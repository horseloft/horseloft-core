<?php

namespace Horseloft\Core\Drawer;

use Horseloft\Core\Utils\Horseloft;

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
        return Horseloft::getRequest($name);
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
}
