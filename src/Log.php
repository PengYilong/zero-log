<?php
declare(strict_types = 1);

namespace zero;

use Exception;

class Log
{

    /**
     * 配置参数
     *
     * @var array
     */
    protected $config = [];

    /**
     * 日志写入驱动
     *
     * @var array
     */
    protected $driver = [];

    /**
     * the message of the log
     *
     * @var array
     */
    protected $messages = [];

    protected $key = [];

    /**
     * init
     *
     * @param array $config
     */
    public function init(array $config)
    {
        $this->config = $config;
        $type = $this->config['type'] ?? 'File';
        $class = false !== strpos($type, '\\') ? $type : '\\zero\\log\\driver\\' . ucwords($type);

        if( class_exists($class) ) {
            $this->driver = new $class($config);
        } else {
            throw new Exception('The class donesn\'t exists:' . $class);
        }
    }

    /**
     * System is unusable.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        return $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        return $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        return $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        return $this->log(__FUNCTION__, $message, $context);   
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        return $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        return $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        return $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        return $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param mixed $message
     * @param array $context
     *
     * @return $this
     *
     */
    public function log($level, $message, array $context = array())
    {
        return $this->record($message, $level, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param [type] $message
     * @param [type] $level
     * @param array $context
     * @return void
     */
    public function record($message, $level = 'info', array $context = array())
    {
        if( is_string($message) ) {
            $replace = [];

            foreach($context as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }

            $messages = strtr($message, $replace);
        }

        $this->messages[$level][] = $messages;

        return $this;
    }

    /**
     * 保存信息到驱动
     *
     * @return void
     */
    public function save(): bool
    {
        if( empty($this->messages) ) {
            return true;
        } 

        // 检查是否有key限制
        if(!$this->checkKey($this->config)) {
            return false;
        }

        // 只记录允许的level

        // 使用驱动写入
        return $this->driver->save($this->messages);
    }

    /**
     * 获取日志信息
     *
     * @param string $type
     * @return void
     */
    public function getLog(string $type = ''): array
    {
        return $this->messages[$type] ?? $this->messages;
    }

    /**
     * 检查日志写入权限，是否设置key
     *
     * @param array $config
     * @return boolean
     */
    public function checkKey(array $config): bool
    {
        if( $this->key && !empty($config['allow_key']) && !in_array($this->key, $config['allow_key']) ) {
            return false;
        }
        return true;
    }   

    /**
     * 当前日志记录的授权key
     *
     * @param array $key
     * @return $this
     */
    public function key(array $key)
    {
        $this->key = $key;

        return $this;
    }

}