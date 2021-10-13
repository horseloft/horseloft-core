<?php

namespace Horseloft\Core;

use Horseloft\Core\Attic\Events;
use Horseloft\Core\Attic\HttpEvents;
use Horseloft\Core\Drawer\Server;

class HTTPServer extends Server
{
    use Events,HttpEvents;

    /**
     * HTTPServer constructor.
     * @param string $applicationPath
     * @throws \Exception
     */
    public function __construct(string $applicationPath)
    {
        parent::__construct($applicationPath);

        $this->create();

        $this->onConnect();

        $this->onRequest();

        $this->onWorkerStart();

        $this->onClose();

        $this->onTask();

        $this->onFinish();
    }

    /**
     * --------------------------------------------------------------------------
     * 创建服务
     * --------------------------------------------------------------------------
     *
     */
    private function create()
    {
        try {
            $this->container()->setServer(
                new \Swoole\Http\Server($this->container()->getHost(), $this->container()->getPort())
            );
        } catch (\Exception $e) {
            exit('HTTP服务启动失败 ' . $e->getMessage());
        }
    }
}
