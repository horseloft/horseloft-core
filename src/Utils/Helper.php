<?php

namespace Horseloft\Core\Utils;

use Horseloft\Core\Drawer\Log;

class Helper
{
    /**
     * horseloft service object
     * @return \Horseloft\Core\Drawer\Horseloft
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
     * 读取配置信息
     * @param string $name
     * @param bool $isEnv
     * @param mixed $default
     * @return array|false|mixed
     */
    private static function getConfig(string $name, bool $isEnv, $default = false)
    {
        if (strlen($name) == 0) {
            return $default;
        }
        $list = explode('.', $name);

        if ($isEnv) {
            array_splice($list, 1, 0, self::horseloft()->getEnv());
        }

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
        return self::getConfig($name, false, $default);
    }

    /**
     * --------------------------------------------------------------------------
     *  获取Config中配置的数据信息
     * --------------------------------------------------------------------------
     *
     * 自动读取当前环境变量 并获取配置信息
     *
     * 如果未能读取到$name的配置信息，返回$default
     *
     * 例：存在配置redis.dev.demo，并且当前环境变量:APP_ENV=dev
     * 那么：$name = redis.demo
     * 即可获取redis.dev.demo的配置信息
     *
     * @param string $name
     * @param mixed $default
     * @return array|false|mixed
     */
    public static function envConfig(string $name, $default = false)
    {
        return self::getConfig($name, true, $default);
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
     * 使用task异步写日志
     * --------------------------------------------------------------------------
     *
     * $filename为空 则使用默认日志文件
     *
     * @param $message
     * @param string $filename
     * @return false|int
     */
    public static function logTask($message, string $filename = '')
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
     * 日志写入
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
     * --------------------------------------------------------------------------
     * 设置header
     * --------------------------------------------------------------------------
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public static function setHeader(string $name, string $value)
    {
        if (strlen(trim($value)) == 0) {
            return false;
        }

        if (self::response()->header($name, $value) === false) {
            return false;
        }
        return true;
    }

    /**
     * --------------------------------------------------------------------------
     * 设置header
     * --------------------------------------------------------------------------
     *
     * $header: 以header名为key，header值为value的一维数组
     *
     * @param array $header
     * @return bool
     */
    public static function setHeaders(array $header)
    {
        if (empty($header)) {
            return false;
        }

        $response = self::response();

        foreach ($header as $key => $value) {
            if ($response->header($key, $value) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * --------------------------------------------------------------------------
     * 设置cookie; 会自动会对 cookie 进行 urlencode 编码
     * --------------------------------------------------------------------------
     *
     * Swoole 会自动会对 $value 进行 urlencode 编码
     *
     * 可使用 rawCookie() 方法关闭对 $value 的编码处理
     *
     * Swoole 允许设置多个相同 $name 的 COOKIE
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @return bool
     */
    public static function setCookie(string $name, string $value = '', int $expire = 0, string $path = '/', string $domain = '')
    {
        if (strlen(trim($name)) == 0) {
            return false;
        }
        if (self::response()->cookie($name, $value, $expire, $path, $domain) === false) {
            return false;
        }
        return true;
    }

    /**
     * --------------------------------------------------------------------------
     * 设置cookie
     * --------------------------------------------------------------------------
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @return bool
     */
    public static function setRawCookie(string $name, string $value = '', int $expire = 0, string $path = '/', string $domain = '')
    {
        if (strlen(trim($name)) == 0) {
            return false;
        }
        if (self::response()->rawCookie($name, $value, $expire, $path, $domain) === false) {
            return false;
        }
        return true;
    }

    /**
     * --------------------------------------------------------------------------
     * 获取header值
     * --------------------------------------------------------------------------
     *
     * @param string $name
     * @return string
     */
    public static function getHeader(string $name)
    {
        if (strlen(trim($name)) == 0) {
            return '';
        }
        return isset(self::getCompleteHeader()[$name]) ? self::getCompleteHeader()[$name] : '';
    }

    /**
     * --------------------------------------------------------------------------
     * 获取全部header
     * --------------------------------------------------------------------------
     *
     * @return array
     */
    public static function getCompleteHeader()
    {
        return self::horseloft()->getRequestHeader();
    }

    /**
     * 获取cookie
     *
     * @param string $name
     * @return string
     */
    public static function getCookie(string $name)
    {
        if (strlen(trim($name)) == 0) {
            return '';
        }
        return isset(self::getCompleteCookie()[$name]) ? self::getCompleteCookie()[$name] : '';
    }

    /**
     * 获取全部cookie
     *
     * @return array
     */
    public static function getCompleteCookie()
    {
        return self::horseloft()->getRequestCookie();
    }

    /**
     * 向请求参数中添加新的参数作为请求参数的一部分
     *
     * 注：
     *  1. 在拦截器使用该方法，则添加的参数将作为请求参数的一部分传递给路由方法
     *  2. 在拦截器之外（Controller,Service等）使用该方法，则只能使用Helper::getRequest()或Helper::getCompleteRequest()获取
     *  3. 使用该方法添加的参数，将替换请求参数中已有的同名参数的值
     *
     * @param string $name
     * @param $value
     */
    public static function setRequest(string $name, $value)
    {
        self::horseloft()->addParam($name, $value);
    }

    /**
     * 获取指定的请求参数值
     *
     * @param string $name
     * @return mixed|string
     */
    public static function getRequest(string $name)
    {
        if (strlen(trim($name)) == 0) {
            return '';
        }
        $params = self::horseloft()->getParams();
        if (isset($params[$name])) {
            return  $params[$name];
        }
        return '';
    }

    /**
     * 获取全部请求参数和值
     *
     * @return array
     */
    public static function getCompleteRequest()
    {
        return self::horseloft()->getParams();
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
     * 获取请求IP
     *
     * @return string
     */
    public static function requestIP()
    {
        return self::horseloft()->getRequestIP();
    }

    /**
     * 获取上传的文件 | 返回值：二维数组
     *
     * @return array
     */
    public static function files()
    {
        return self::horseloft()->getRequestFiles();
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
}
