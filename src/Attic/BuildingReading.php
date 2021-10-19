<?php

namespace Horseloft\Core\Attic;

trait BuildingReading
{
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
     * 获取上传的文件 | 返回值：二维数组
     *
     * @return array
     */
    public static function files()
    {
        return self::horseloft()->getRequestFiles();
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
     * 获取请求uri
     *
     * @return string
     */
    public static function requestUri()
    {
        return self::horseloft()->getRequestUri();
    }

    /**
     * 获取请求方式
     *
     * @return string
     */
    public static function requestMethod()
    {
        return self::horseloft()->getRequestMethod();
    }
}
