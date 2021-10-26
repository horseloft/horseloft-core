<?php

namespace Horseloft\Core\Drawer;

use Horseloft\Core\Utils\Horseloft;

class Request
{
    /**
     * 添加请求参数
     *
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value)
    {
        Horseloft::setRequest($name, $value);
    }

    /**
     * 获取指定的请求参数
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = '')
    {
        return Horseloft::getRequest($name, $default);
    }

    /**
     * 获取全部请求参数
     *
     * @return array
     */
    public function all()
    {
        return Horseloft::getCompleteRequest();
    }

    /**
     * 获取header
     *
     * @param string $name
     * @return string
     */
    public function getHeader(string $name): string
    {
        return Horseloft::getHeader($name);
    }

    /**
     * 获取完整header
     *
     * @return array
     */
    public function getCompleteHeader()
    {
        return Horseloft::getCompleteHeader();
    }

    /**
     * 获取cookie
     *
     * @param string $name
     * @return string
     */
    public function getCookie(string $name): string
    {
        return Horseloft::getCookie($name);
    }

    /**
     * 获取全部cookie
     *
     * @return array
     */
    public function getCompleteCookie()
    {
        return Horseloft::getCompleteCookie();
    }

    /**
     * 获取上传的全部文件
     *
     * @return array
     */
    public function getUploadFiles(): array
    {
        return Horseloft::files();
    }

    /**
     * 获取请求的URI
     *
     * @return string
     */
    public function getUri()
    {
        return Horseloft::requestUri();
    }

    /**
     * 获取请求方式
     *
     * @return string
     */
    public function getMethod()
    {
        return Horseloft::requestMethod();
    }

    /**
     * 获取客户端IP
     *
     * @return string
     */
    public function getIP()
    {
        return Horseloft::requestIP();
    }
}
