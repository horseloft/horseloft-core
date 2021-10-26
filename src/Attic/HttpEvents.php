<?php

namespace Horseloft\Core\Attic;

use Horseloft\Core\Drawer\Spanner;
use Horseloft\Core\Handle\HttpExceptionHandle;
use Horseloft\Core\Handle\HttpRequestHandle;

trait HttpEvents
{
    /**
     * --------------------------------------------------------------------------
     * 建立连接事件
     * --------------------------------------------------------------------------
     *
     * onConnect/onClose 这 2 个回调发生在 Worker 进程内，而不是主进程
     * UDP 协议下只有 onReceive 事件，没有 onConnect/onClose 事件
     *
     */
    private function onConnect()
    {
        $this->container()->getServer()->on('connect', function () {
            $this->connectTime = microtime(true);
        });
    }

    /**
     * --------------------------------------------------------------------------
     * 监听连接关闭事件
     * --------------------------------------------------------------------------
     *
     * onConnect/onClose 这 2 个回调发生在 Worker 进程内，而不是主进程
     * UDP 协议下只有 onReceive 事件，没有 onConnect/onClose 事件
     */
    private function onClose()
    {
        $this->container()->getServer()->on('close', function () {
            $costTime = (microtime(true) - $this->connectTime) * 1000;
            $runTime = round($costTime, 1);
            if ($runTime < 100) {
                $header = "\e[36m";
            } else if ($runTime > 250) {
                $header = "\e[31m";
            } else {
                $header = "\e[33m";
            }
            Spanner::cliPrint($header ."[" . $runTime . 'ms]' . "\e[0m");
        });
    }

    /**
     * --------------------------------------------------------------------------
     * 监听HTTP服务的数据接收
     * --------------------------------------------------------------------------
     *
     */
    private function onRequest()
    {
        $this->container()->getServer()->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            try {
                //设置请求的开始时间
                $this->connectTime = microtime(true);

                //ico请求
                if ($request->server['request_uri'] == '/favicon.ico') {
                    $response->end();
                    $this->container()->getServer()->close($response->fd);
                    return;
                }
                //设置默认的输出类型为json；header为NGINX
                $response->header('Content-Type', 'application/json');
                $response->header('Server', 'Nginx');

                // 当前一次response加入容器
                $this->container()->setResponse($response);

                // 处理$request相关
                $requestHandle = new HttpRequestHandle($request);

                $returnData = call_user_func_array($this->container()->getRouteCallback(), $requestHandle->getRequestArgs());

                //数据返回至客户端
                $response->end(Spanner::response($returnData));

            } catch (\Throwable $e) {
                $exceptionHandle = new HttpExceptionHandle($e);
                //数据返回至客户端
                $response->end(Spanner::response($exceptionHandle->getResponse()));
            }
            //主动关闭本次连接
            $this->container()->getServer()->close($response->fd);
        });
    }
}
