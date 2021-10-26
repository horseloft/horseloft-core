<?php

namespace Horseloft\Core\Drawer;

class Spanner
{
    /**
     * 非字符串以json格式返回
     *
     * @param $data
     * @return string
     */
    public static function response($data)
    {
        if (is_string($data)) {
            return $data;
        }
        return self::encode($data);
    }

    /**
     * json
     * @param $data
     * @return string
     */
    public static function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 命令行输出
     * @param $data
     */
    public static function cliPrint($data)
    {
        if (is_string($data)) {
            echo $data . PHP_EOL;
        } else {
            print_r($data);
        }
    }

    /**
     * 命令行输出异常信息
     *
     * @param \Throwable $e
     */
    public static function cliExceptionPrint(\Throwable $e)
    {
        $message = '=========================  SERVICE ERROR  =======================' . PHP_EOL;

        $message .= 'message: ' . $e->getMessage() . PHP_EOL;

        $message .= 'file: ' . $e->getFile() . ' (line:' . $e->getLine() . ')' . PHP_EOL;

        self::cliPrint($message . $e->getTraceAsString());
    }
}
