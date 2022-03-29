<?php

namespace Horseloft\Core\Utils;

use Horseloft\Core\Attic\BuildingReading;
use Horseloft\Core\Attic\BuildingSetting;
use Horseloft\Core\Drawer\Log;

class Horseloft
{
    use BuildingSetting,BuildingReading;

    /**
     * horseloft service object
     * @return \Horseloft\Core\Drawer\Building
     */
    private static function horseloft()
    {
        if (!isset($GLOBALS['_HORSELOFT_CORE_CONTAINER_'])) {
            throw new \RuntimeException('missing container');
        }
        return $GLOBALS['_HORSELOFT_CORE_CONTAINER_'];
    }

    /**
     * 服务的响应
     * @return \Swoole\Http\Response
     */
    private static function response()
    {
        $response = self::horseloft()->getResponse();
        if (!($response instanceof \Swoole\Http\Response)) {
            throw new \RuntimeException('container response is null');
        }

        return $response;
    }

    /**
     * 获取env.ini配置项的值
     *
     * @param string $name
     * @param mixed $default
     * @return false|mixed
     */
    public static function env(string $name = 'env', $default = false)
    {
        return self::config('_horseloft_configure_env_ini_.' . $name, $default);
    }

    /**
     * --------------------------------------------------------------------------
     *  获取Config中配置的数据信息
     * --------------------------------------------------------------------------
     *
     * 如果未能读取到$name的配置信息，返回$default
     *
     * @param string $name
     * @param mixed $default
     * @return false|mixed
     */
    public static function config(string $name, $default = false)
    {
        if (strlen($name) == 0) {
            return $default;
        }
        $list = explode('.', $name);

        $var = self::horseloft()->getConfigure();
        foreach ($list as $value) {

            if (!isset($var[$value])) {
                return $default;
            }
            $var = $var[$value];
        }
        return $var;
    }

    /**
     * --------------------------------------------------------------------------
     * task
     * --------------------------------------------------------------------------
     *
     * 调用task执行一个异步任务
     *
     * $call 是完整命名空间的类名称及方法名称 例：[Library\Utils\HorseLoftUtil::class, 'encode']
     * $args 回调方法的参数 一个或者多个
     *
     * 返回值 false|task_id; task_id为0-n的int值
     *
     * @param callable $call
     * @param mixed ...$args
     * @return false|int
     */
    public static function task(callable $call, ...$args)
    {
        return self::horseloft()->getServer()->task(
            [
                'function' => $call,
                'params' => $args
            ]
        );
    }

    /**
     * --------------------------------------------------------------------------
     * 使用task写日志【异步】
     * --------------------------------------------------------------------------
     *
     * $filename为空 则使用默认日志文件
     *
     * @param $message
     * @param string $filename
     * @return false|int
     */
    public static function taskLog($message, string $filename = '')
    {
        $horseloft = self::horseloft();

        if (empty($filename)) {
            $filename = $horseloft->getLogFilename();
        }

        return $horseloft->getServer()->task(
            [
                'function' => [\Horseloft\Core\Drawer\Log::class, 'write'],
                'params' => [
                    $horseloft->getLogPath() . '/' . $filename,
                    $message
                ]
            ]
        );
    }

    /**
     * --------------------------------------------------------------------------
     * 日志写入【同步】
     * --------------------------------------------------------------------------
     *
     * $filename为空 则使用默认日志文件
     *
     * @param $message
     * @param string $filename
     * @return bool
     */
    public static function log($message, string $filename = '')
    {
        $horseloft = self::horseloft();

        if (empty($filename)) {
            $filename = $horseloft->getLogFilename();
        }
        return Log::write($horseloft->getLogPath() . '/' . $filename, $message);
    }

    /**
     *  获取服务的日志存储路径
     *
     * @return string
     */
    public static function logPath()
    {
        return self::horseloft()->getLogPath();
    }

    /**
     * 获取定时任务配置信息
     *
     * @return array
     */
    public static function crontab()
    {
        return self::horseloft()->getCrontabConfig();
    }

    /**
     * 获取swoole的启动配置项
     *
     * @return array
     */
    public static function swooleConfig()
    {
        return self::horseloft()->getSwooleConfig();
    }

    /**
     * 获取自定义路由配置信息
     *
     * @return array
     */
    public static function route()
    {
        return self::horseloft()->getRouteConfig();
    }

    /**
     * 项目路径
     *
     * @return string
     */
    public static function root()
    {
        return self::horseloft()->getApplicationPath();
    }
}
