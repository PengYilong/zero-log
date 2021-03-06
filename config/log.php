<?php
return [
    // 日志记录方式
    'type' => 'File',
    // 允许记录的日志级别   
    'level' => [],
    // 日志保存目录
    'path' => '',
    // 日志输出格式
    'time_format' => 'c',
    // 单文件日志写入
    'single' => false,
    // 日志文件大小限制
    'file_size' => 2097152,
    // 独立日志级别
    'apart_level' => [],
    // 最大日志文件数量（超过自动清理）
    'max_files' => 0,
    'json' => false,
    'json_options' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    // 允许记录的日志级别   
    'format' => '[%s][%s] %s',
    'allow_key' => '',
];