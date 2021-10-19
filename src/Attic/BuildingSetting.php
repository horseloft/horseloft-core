<?php

namespace Horseloft\Core\Attic;

trait BuildingSetting
{
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
}
