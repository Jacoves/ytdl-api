<?php
namespace f2face;

class YouTubeDL {

    private $ytdl_file = 'youtube-dl';

    function __construct($youtubedl_file = null) {
        if (!empty($youtubedl_file))
            $this->ytdl_file = $youtubedl_file;
        
        if (!$this->checkYtdl())
            throw new \Exception('YouTube-DL not found');
    }

    /**
     *  Get media data
     *
     *  @param string $url
     *  @return object
     */
    public function run($url) {
        $result = $this->runYtdl(sprintf('--skip-download --print-json "%s"', $url));
        $obj = json_decode($result);
        
        if (!$obj)
            throw new \Exception('Not found or not supported');
        
        if (empty($obj->fulltitle))
            throw new \Exception('Not found');
        
        $data = array(
            'id' => $obj->id,
            'full_title' => $obj->fulltitle,
            'description' => !empty($obj->description) ? $obj->description : null,
            'duration' => !empty($obj->duration) ? $obj->duration : null,
            'uploader' => !empty($obj->uploader) ? $obj->uploader : null,
            'upload_date' => !empty($obj->upload_date) ? $obj->upload_date : null,
            'thumbnail' => !empty($obj->thumbnail) ? $obj->thumbnail : null,
            'page_url' => !empty($obj->webpage_url) ? $obj->webpage_url : null,
            'extractor' => !empty($obj->extractor_key) ? $obj->extractor_key : null,
            'formats' => !empty($obj->formats) ? $obj->formats : null,
        );
        
        return json_decode(json_encode($data));
    }
    
    /**
     *  Run YouTube-DL.
     *
     *  @param string $args
     *  @return string|null $data
     */
    private function runYtdl($args) {
        $data = shell_exec(sprintf('"%s" %s', $this->ytdl_file, $args));
        return $data;
    }

    /**
     *  Check if YouTube-DL exists or is executable.
     *
     *  @return boolean
     */
    private function checkYtdl() {
        if ($this->command_exists($this->ytdl_file))
            return true;
        
        return false;
    }

    /**
     *  Check if a command exists or is executable.
     *
     *  @param string $command
     *  @return boolean
     */
    private function command_exists($command) {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';
        
        $process = proc_open(sprintf('%s "%s"', $whereIsCommand, $command), array(
                0 => array("pipe", "r"), //STDIN
                1 => array("pipe", "w"), //STDOUT
                2 => array("pipe", "w"), //STDERR
            ),
            $pipes
        );
        
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return ($stdout != '' || file_exists($this->ytdl_file));
        }

        return false;
    }
}