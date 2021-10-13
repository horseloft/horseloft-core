<?php

namespace Horseloft\Core\Handle;

class RouteHandle
{
    /**
     * 控制器层的命名空间
     *
     * @var string
     */
    private $controllerNamespace;

    /**
     * 路由前缀
     *
     * @var string
     */
    private $prefix = '';

    /**
     * 路由命名空间
     *
     * @var string
     */
    private $namespace = '';

    /**
     * 路由拦截器
     *
     * @var string
     */
    private $interceptor = '';

    /**
     * POST请求的路由
     *
     * @var array
     */
    private $post = [];

    /**
     * GET请求的路由
     *
     * @var array
     */
    private $get = [];

    /**
     * 既支持POST请求也支持GET请求的路由
     *
     * @var array
     */
    private $any= [];

    public function __construct(string $namespace)
    {
        $this->controllerNamespace = $namespace;
    }

    /**
     * 路由配置文件转为对一个的类方法
     *
     * @param array $route
     * @return array
     */
    public function getRequestRoute(array $route)
    {
        if (empty($route)) {
            return [];
        }
        $this->routeParse($route);
        // POST 路由
        $postRoute = $this->routeResolve($this->post, 'POST_');
        // GET 路由
        $getRoute = $this->routeResolve($this->get, 'GET_');
        // any路由
        $anyRoute = $this->routeResolve($this->any);

        return array_merge($postRoute, $getRoute, $anyRoute);
    }

    /**
     * 路由解析
     *
     * @param array $route
     * @param string $method
     * @return array
     */
    private function routeResolve(array $route, string $method = '')
    {
        $list = [];
        foreach ($route as $uri => $action) {
            if (!is_string($uri) || empty($action) || !is_string($action) || strpos($action, '::') == false) {
                continue;
            }
            $routeCallback = $this->namespace . $action;
            if (!is_callable($routeCallback)) {
                continue;
            }

            $list[] = [
                'uri' => $method . $this->prefix . trim($uri, '/'),
                'request' => [
                    'callback' => $routeCallback,
                    'interceptor' => $this->interceptor
                ]
            ];
        }
        return $list;
    }

    /**
     * 初始化数据
     *
     * @param array $route
     */
    private function routeParse(array $route)
    {
        if (empty($route['prefix']) || !is_string($route['prefix'])) {
            $this->prefix = '';
        } else {
            $this->prefix = trim($route['prefix'], '/') . '/';
        }

        if (empty($route['namespace']) || !is_string($route['namespace'])) {
            $this->namespace = $this->controllerNamespace;
        } else {
            $this->namespace = $this->controllerNamespace . trim($route['namespace'], '\\') . '\\';
        }

        if (empty($route['interceptor']) || !is_string($route['interceptor'])) {
            $this->interceptor = '';
        } else {
            $this->interceptor = ucfirst($route['interceptor']);
        }

        if (empty($route['post']) || !is_array($route['post'])) {
            $this->post = [];
        } else {
            $this->post = $route['post'];
        }

        if (empty($route['get']) || !is_array($route['get'])) {
            $this->get = [];
        } else {
            $this->get = $route['get'];
        }

        if (empty($route['any']) || !is_array($route['any'])) {
            $this->any = [];
        } else {
            $this->any = $route['any'];
        }
    }
}
