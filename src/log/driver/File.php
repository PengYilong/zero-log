<?php
declare(strict_types = 1);

namespace zero\log\driver;

use Exception;

class File
{
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 日志写入
     *
     * @return void
     */
    public function save(array $messages = []): bool
    {   
        $destination = $this->getMasterLogFile();

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        $info = [];

        // 日志信息封装
        $time = date($this->config['time_format']);
        
        foreach($messages as $type => $val) {
            $message = [];
            foreach($val as $msg) {
                if(!is_string($msg)) {
                    $msg = var_export($msg, true);
                }

                $message[] = $this->config['json'] ?
                json_encode(['time' => $time, 'type' => $type, 'msg' => $msg],  $this->config['json_options']) :
                sprintf($this->config['format'], $time, $type, $msg);

                if(true === $this->config['apart_level'] || in_array($type, $this->config['apart_level']) ) {
                    // 独立记录的日志级别
                    $filename = $this->getApartLevelFile($path, $type);
                    $this->write($message, $filename);
                    continue;
                }
            }

            $info[$type] = $message;
        }

        if($info) {
            return $this->write($info, $destination);
        }

        return true;
        
    }

    public function getMasterLogFile(): string
    {
        if(substr($this->config['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->config['path'] .= DIRECTORY_SEPARATOR;
        }

        // 最大日志文件数，超过自动清理
        if( $this->config['max_files'] ) {
            $files = glob($this->config['path'] . '*.log' );

            try {
                if( count($files) > $this->config['max_files'] ) {
                    unlink($files[0]);
                }
            } catch (Exception $e) {

            }
        }   

        if( $this->config['single'] ) {
            $name = is_string($single) ? $single : 'single';
            $destination = $this->config['path'] . $name . '.log';
        } else {
            if( $this->config['max_files'] ) {
                $filename = date('Ymd') . '.log';
            } else {
                $filename = date('Ym') . DIRECTORY_SEPARATOR . date('d') . '.log';
            }

            $destination = $this->config['path'] . $filename;
        }

        return $destination;
    }

    /**
     * 日志写入
     *
     * @param array $message
     * @param string $destination
     * @return boolean
     */
    public function write(array $message, string $destination): bool
    {
        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        $this->checkLogSize($destination);

        $info = [];
        foreach($message as $type => $msg) {
            $info[$type] = is_array($msg) ? implode(PHP_EOL, $msg) : $msg;
        }

        $message = implode(PHP_EOL, $info) . PHP_EOL;
        
        return error_log($message, 3, $destination);
    }

    /**
     * 检查文件是否超过大小限制
     *
     * @param string $destination
     * @return void
     */
    public function checkLogSize(string $destination): void
    {
        if( is_file($destination) && floor($this->config['file_size']) <= filesize($destination) ) {
            try {
                rename($destination, dirname($destination) . DIRECTORY_SEPARATOR . time() . '-' . basename($destination));
            } catch(Exception $e) {

            }
        }
    }

    /**
     * 获取独立日志文件名
     *
     * @param string $path
     * @param string $type
     * @return string
     */
    protected function getApartLevelFile(string $path, string $type): string
    {
        if($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';
            $name .= '_' . $type;
        } elseif ($this->config['max_files']) {
            $name = date('Ymd') .  '_' . $type;
        } else {
            $name = date('d') . '_' . $type;
        }

        return $path . DIRECTORY_SEPARATOR . $name . '.log';
    }
}