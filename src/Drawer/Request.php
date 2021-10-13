<?php

namespace Horseloft\Core\Drawer;

use Horseloft\Core\Utils\Helper;

class Request
{
    private $files = [];

    /**
     * 获取header
     *
     * @param string $name
     * @return string
     */
    public function getHeader(string $name): string
    {
        return Helper::getHeader($name);
    }

    /**
     * 获取完整header
     *
     * @return array
     */
    public function getCompleteHeader()
    {
        return Helper::getCompleteHeader();
    }

    /**
     * 获取cookie
     *
     * @param string $name
     * @return string
     */
    public function getCookie(string $name): string
    {
        return Helper::getCookie($name);
    }

    /**
     * 获取全部cookie
     *
     * @return array
     */
    public function getCompleteCookie()
    {
        return Helper::getCompleteCookie();
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * 获取指定的请求参数
     *
     * @param string $name
     * @return mixed|string
     */
    public function get(string $name)
    {
        return Helper::getRequest($name);
    }

    /**
     * 获取全部请求参数
     *
     * @return array
     */
    public function all()
    {
        return Helper::getCompleteRequest();
    }
}
