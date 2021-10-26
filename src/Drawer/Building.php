<?php

namespace Horseloft\Core\Drawer;

class Building
{
    /**
     * @var \Swoole\Http\Server
     */
    private $server;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * 控制器的命名空间
     *
     * @var string
     */
    private $namespace = '';

    /**
     * 日志路径
     *
     * @var string
     */
    private $logPath = '';

    /**
     * 日志文件名称
     *
     * @var string
     */
    private $logFilename = '';

    /**
     * crontab数据
     *
     * @var array
     */
    private $crontabConfig = [];

    /**
     * swoole服务端配置项
     *
     * @var array
     */
    private $swooleConfig = [];

    /**
     * header
     *
     * @var array
     */
    private $requestHeader = [];

    /**
     * ip
     *
     * @var string
     */
    private $requestIP = '';

    /**
     * cookie
     *
     * @var array
     */
    private $requestCookie = [];

    /**
     * 上传的文件
     *
     * @var array
     */
    private $requestFiles = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * 响应
     *
     * @var \Swoole\Http\Response
     */
    private $response;

    /**
     * 配置信息
     *
     * @var array
     */
    private $configure = [];

    /**
     * 路由
     *
     * @var array
     */
    private $routeConfig = [];

    /**
     * 配置文件目录
     *
     * @var string
     */
    private $configDir = '';

    /**
     * 项目路径
     * @var string
     */
    private $applicationPath = '';

    /**
     * 当前路由的拦截器
     *
     * @var callable
     */
    private $routeInterceptor = [];

    /**
     * 当前路由指向的控制器方法
     *
     * @var callable
     */
    private $routeCallback;

    /**
     * 当前请求的请求方法
     *
     * @var string
     */
    private $requestMethod;

    /**
     * 当前请求的URI
     * @var string
     */
    private $requestUri;

    /**
     * 是否开启debug模式
     *
     * @var bool
     */
    private $debug = false;

    /**
     * 是否使用默认路由
     *
     * @var bool
     */
    private $defaultRoute = true;

    /**
     * 是否记录错误日志
     *
     * @var bool
     */
    private $errorLog = false;

    /**
     * @return bool
     */
    public function isErrorLog(): bool
    {
        return $this->errorLog;
    }

    /**
     * @param bool $errorLog
     */
    public function setErrorLog(bool $errorLog): void
    {
        $this->errorLog = $errorLog;
    }

    /**
     * @return bool
     */
    public function isDefaultRoute(): bool
    {
        return $this->defaultRoute;
    }

    /**
     * @param bool $defaultRoute
     */
    public function setDefaultRoute(bool $defaultRoute): void
    {
        $this->defaultRoute = $defaultRoute;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @return string
     */
    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    /**
     * @param string $requestUri
     */
    public function setRequestUri(string $requestUri): void
    {
        $this->requestUri = $requestUri;
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * @param mixed $requestMethod
     */
    public function setRequestMethod($requestMethod): void
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * @return callable
     */
    public function getRouteInterceptor()
    {
        return $this->routeInterceptor;
    }

    /**
     * @param callable|array $routeInterceptor
     */
    public function setRouteInterceptor($routeInterceptor): void
    {
        $this->routeInterceptor = $routeInterceptor;
    }

    /**
     * @return callable
     */
    public function getRouteCallback(): callable
    {
        return $this->routeCallback;
    }

    /**
     * @param callable $routeCallback
     */
    public function setRouteCallback(callable $routeCallback): void
    {
        $this->routeCallback = $routeCallback;
    }

    /**
     * @return string
     */
    public function getApplicationPath(): string
    {
        return $this->applicationPath;
    }

    /**
     * @param string $applicationPath
     */
    public function setApplicationPath(string $applicationPath): void
    {
        $this->applicationPath = $applicationPath;
    }

    /**
     * @return string
     */
    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    /**
     * @param string $configDir
     */
    public function setConfigDir(string $configDir): void
    {
        $this->configDir = $configDir;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * 向请求参数中 添加新的参数作为请求参数
     *
     * @param string $name
     * @param $value
     */
    public function addParam(string $name, $value): void
    {
        $this->params = array_merge($this->params, [$name => $value]);
    }

    /**
     * @param \Swoole\Http\Server $server
     */
    public function setServer(\Swoole\Http\Server $server): void
    {
        $this->server = $server;
    }

    /**
     * @return \Swoole\Http\Server
     */
    public function getServer(): \Swoole\Http\Server
    {
        return $this->server;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getLogPath(): string
    {
        return $this->logPath;
    }

    /**
     * @param string $logPath
     */
    public function setLogPath(string $logPath): void
    {
        $this->logPath = $logPath;
    }

    /**
     * @param string $logFilename
     */
    public function setLogFilename(string $logFilename): void
    {
        $this->logFilename = $logFilename;
    }

    /**
     * @return string
     */
    public function getLogFilename(): string
    {
        return $this->logFilename;
    }

    /**
     * @return array
     */
    public function getCrontabConfig(): array
    {
        return $this->crontabConfig;
    }

    /**
     * @param array $crontabConfig
     */
    public function setCrontabConfig(array $crontabConfig): void
    {
        array_push($this->crontabConfig, $crontabConfig);
    }

    /**
     * @param array $swooleConfig
     */
    public function setSwooleConfig(array $swooleConfig): void
    {
        $this->swooleConfig = $swooleConfig;
    }

    /**
     * @return array
     */
    public function getSwooleConfig(): array
    {
        return $this->swooleConfig;
    }

    /**
     * @return array
     */
    public function getRequestHeader(): array
    {
        return $this->requestHeader;
    }

    /**
     * @param array $requestHeader
     */
    public function setRequestHeader(array $requestHeader): void
    {
        $this->requestHeader = $requestHeader;
    }

    /**
     * @param string $requestIP
     */
    public function setRequestIP(string $requestIP): void
    {
        $this->requestIP = $requestIP;
    }

    /**
     * @return string
     */
    public function getRequestIP(): string
    {
        return $this->requestIP;
    }

    /**
     * @return array
     */
    public function getRequestCookie(): array
    {
        return $this->requestCookie;
    }

    /**
     * @param array $requestCookie
     */
    public function setRequestCookie(array $requestCookie): void
    {
        $this->requestCookie = $requestCookie;
    }

    /**
     * @param array $requestFiles
     */
    public function setRequestFiles(array $requestFiles): void
    {
        $this->requestFiles = $requestFiles;
    }

    /**
     * @return array
     */
    public function getRequestFiles(): array
    {
        return $this->requestFiles;
    }

    /**
     * @return \Swoole\Http\Response
     */
    public function getResponse(): \Swoole\Http\Response
    {
        return $this->response;
    }

    /**
     * @param \Swoole\Http\Response $response
     */
    public function setResponse(\Swoole\Http\Response $response): void
    {
        $this->response = $response;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setConfigure($key, $value): void
    {
        $this->configure[$key] = $value;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getConfigure(string $name = ''): array
    {
        if (empty($name)) {
            return $this->configure;
        }
        return $this->configure[$name] ?? [];
    }

    /**
     *
     * @param string $key
     * @param $value
     */
    public function setRouteConfig(string $key, $value): void
    {
        $this->routeConfig[$key] = $value;
    }

    /**
     * @return array
     */
    public function getRouteConfig(): array
    {
        return $this->routeConfig;
    }
}
