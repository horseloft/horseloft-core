<?php

namespace Horseloft\Core\Drawer;

class Log
{
    //日志文件大小 默认为6M | 格式为字节数 6291456
    const LOG_FILE_MAX_SIZE = 6291456;

    /**
     * --------------------------------------------------------------------------
     * 写日志
     * --------------------------------------------------------------------------
     *
     * $file 是带有完整路径的文件 例：/var/log/horseloft/horseloft.log
     *
     * @param string $file
     * @param $message
     * @return bool
     */
    public static function write(string $file, $message)
    {
        $path = dirname($file);
        $filename = basename($file);

        //递归创建目录
        if (!is_dir($path)) {
            $old = umask(0);
            mkdir($path, 0777, true);
            umask($old);
        }

        //资源类型不记录
        if (is_resource($message)) {
            return false;
        }

        if (is_array($message) || is_object($message)) {
            $message = Spanner::encode($message);
        }

        file_put_contents(self::logFileHandle($path, $filename), $message . PHP_EOL, FILE_APPEND);

        return true;
    }

    /**
     * --------------------------------------------------------------------------
     * 读取日志目录 并返回一个最新的日志文件名称
     * --------------------------------------------------------------------------
     *
     * 只读取一层目录
     *
     * 例：日志路径为/web/abc.log;
     * 日志文件格式为：abc2020-01-02-1.log 并以此累加
     *
     * @param string $path
     * @param string $filename
     * @return string
     */
    private static function logFileHandle(string $path, string $filename)
    {
        $filename = rtrim($filename, '.log');
        $logFile = $path . '/' . $filename . '.log';

        /*
         * --------------------------------------------------------------------------
         * 因为上一步已经处理了目录问题，这里不再处理；
         * 如果文件不存在 则返回当前日志文件
         * 如果当前日志文件小于最大值 则返回当前日志文件
         * --------------------------------------------------------------------------
         */
        if (!is_file($logFile) || filesize($logFile) < self::LOG_FILE_MAX_SIZE) {
            return $logFile;
        }

        /*
         * --------------------------------------------------------------------------
         * 默认为日志目录下的文件都是小写字母的 以当前项目名称开始 日期数字结尾 的.log文件
         * --------------------------------------------------------------------------
         * 注意：累加的日志文件格式为 abc年月日-n.log 格式；所以文件名称中不提倡使用短斜线'-'加数字格式
         *
         */
        $dateFile = $path . '/' . $filename . date('Y-m-d') . '.log';
        $fileList = glob($path . '/'. $filename . date('Y-m-d') . '-[0-9]*.log');
        $saveFile = trim($dateFile, '.log') . '-' . (count($fileList) + 1) . '.log';

        rename($logFile, $saveFile);

        return $logFile;
    }
}