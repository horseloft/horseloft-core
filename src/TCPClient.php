<?php

namespace Horseloft\Core;

class TCPClient
{
    //超时时间s
    protected $connectTimeout = 30;
    protected $receiveTimeout = 30;

    //swoole 服务
    protected $client = null;

    //请求TCPService中的类名称
    protected $serviceName = null;

    //接收的数据的结尾标识符
    protected $eof = '#FX#FX#';

    //最大接收数据1M【1 * 1024 * 1024】
    protected $maxPackage = 1048576;

    //通用异常
    protected $exception = '请求失败';

    /**
     * TCPClient constructor.
     * @param string $host
     * @param int $port
     * @param string $serviceName
     */
    public function __construct(string $host, int $port, string $serviceName)
    {
        if (empty($host)) {
            throw new \RuntimeException($this->exception, 1001);
        }
        if ($port < 1 || $port > 65535) {
            throw new \RuntimeException($this->exception, 1002);
        }
        if (preg_match("/^[a-zA-Z]+$/", $serviceName) == false) {
            throw new \RuntimeException($this->exception, 1003);
        }
        $this->connectTcpServer($host, $port, $serviceName);
    }

    /**
     *
     * @param $callFunction
     * @param $callParam
     * @return string
     */
    public function __call($callFunction, $callParam)
    {
        if (preg_match("/^[a-zA-Z]+$/", $callFunction) == false) {
            throw new \RuntimeException($this->exception, 1004);
        }
        return $this->sendAndReceive($callFunction, $callParam);
    }

    /**
     * 连接swoole服务
     * SWOOLE_SOCK_TCP|SWOOLE_KEEP
     * 结束符检测 最大接收长度 指定接收的数据结尾标识符
     *
     * @param string $host
     * @param int $port
     * @param string $serviceName
     */
    private function connectTcpServer(string $host, int $port, string $serviceName)
    {
        $this->serviceName = $serviceName;
        $this->client = new \Swoole\Client(SWOOLE_SOCK_TCP);
        $this->client->set([
            'open_eof_check' => true,
            'package_eof' => $this->eof,
            'package_max_length' => $this->maxPackage
        ]);
        if ($this->client->connect($host, $port, $this->connectTimeout) == false) {
            throw new \RuntimeException($this->exception, 1005);
        }
    }

    /**
     * 发送数据接收返回的数据
     *
     * @param string $function
     * @param array $params
     * @return string
     */
    private function sendAndReceive(string $function, array $params = [])
    {
        try {
            $send = [
                'controller' => $this->serviceName,
                'function' => $function,
                'params' => $params
            ];
            if ($this->client->send(json_encode($send, JSON_UNESCAPED_UNICODE)) === false) {
                throw new \RuntimeException($this->exception, 1006);
            }
            $receive = $this->client->recv($this->receiveTimeout);
            $this->client->close();

            if ($receive === false || $receive == '') {
                throw new \RuntimeException($this->exception, 1007);
            }
            return rtrim($receive, $this->eof);
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
}
