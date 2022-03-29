<?php

namespace Horseloft\Core\Handle;

use Horseloft\Core\Attic\Logger;
use Horseloft\Core\Attic\Container;
use Horseloft\Core\Drawer\Request;
use Horseloft\Core\Drawer\Spanner;
use Horseloft\Core\Exceptions\HorseloftInspectorException;
use Horseloft\Core\Exceptions\HorseloftRequestException;
use Horseloft\Core\Utils\Convert;
use Horseloft\Core\Utils\Horseloft;

class HttpRequestHandle
{
    use Container,Logger;

    /**
     * HttpRequestHandle constructor.
     * @param \Swoole\Http\Request $request
     */
    public function __construct(\Swoole\Http\Request $request)
    {
        $container = $this->container();

        // 客户端header
        $container->setRequestHeader($request->header);

        // 客户端cookie
        $container->setRequestCookie(empty($request->cookie) ? [] : $request->cookie);

        // 客户端上传文件
        $container->setRequestFiles(empty($request->files) ? [] : $request->files);

        // 请求方法
        $container->setRequestMethod($request->server['request_method']);

        // 请求URI
        $container->setRequestUri($request->server['request_uri']);

        // 客户端IP
        $this->remoteAddressHandle($request);

        // 客户端请求参数整合 请求参数合并
        $this->requestParamHandle($request);

        // 客户端请求数据输出到终端 + 写日志
        $this->requestDataHandle();

        // 路由拦截器处理
        $this->requestRouteHandle();

        // 执行拦截器
        $this->requestInterceptorHandle();
    }

    /**
     * 过滤有效的请求参数
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getRequestArgs()
    {
        $method = new \ReflectionMethod($this->container()->getRouteCallback());

        //调用的方法必须是静态方法
        if (!$method->isStatic()) {
            throw new HorseloftRequestException('Request Not Found');
        }

        //调用的方法没有参数
        if (count($method->getParameters()) == 0) {
            return [];
        }
        $params = $this->container()->getParams();

        $requestCount = count($params);
        $paramterNumber = $method->getNumberOfParameters();
        $paramterRequireNumber = $method->getNumberOfRequiredParameters();

        /*
         * --------------------------------------------------------------------------
         * 验证接受的参数类型
         * --------------------------------------------------------------------------
         *
         * 请求参数数量 >= 方法的参数数量，并且格式类型匹配，则直接请求
         * 或者
         * 如果请求参数数量 >= 必填参数数量，并且格式类型匹配，则直接请求
         *
         */
        $callArgs = $this->callArgs($method, $params);
        if (($requestCount >= $paramterNumber && !empty($callArgs)) || ($requestCount >= $paramterRequireNumber && !empty($callArgs))) {
            return $callArgs;
        }

        $firstParamType = $method->getParameters()[0]->getType();

        /*
         * --------------------------------------------------------------------------
         * 如果方法的参数数量为1 版本参数类型为Request
         * --------------------------------------------------------------------------
         * 
         */
        if ($paramterNumber == 1
            && !is_null($firstParamType)
            && $firstParamType->getName() == 'Horseloft\Core\Drawer\Request'
        ) {
            return [new Request()];
        }

        /*
         * --------------------------------------------------------------------------
         * 如果方法的参数数量为1，并且是数组格式或者无格式，或者混合类型格式，则把请求参数作为一个数组传递
         * --------------------------------------------------------------------------
         *
         */
        if (($paramterNumber == 1 || $paramterRequireNumber == 1)
            && is_null($firstParamType) || $firstParamType->getName() == 'array') {
            return [$params];
        }
        throw new HorseloftRequestException('Bad Request Param');
    }

    /**
     * --------------------------------------------------------------------------
     * 验证请求参数的类型，请求参数类型应与方法定义的参数类型一致
     * --------------------------------------------------------------------------
     *
     * @param \ReflectionMethod $method
     * @param array $params
     * @return array
     */
    private function callArgs(\ReflectionMethod $method, array $params)
    {
        $result = [];
        $args = $method->getParameters();

        foreach ($args as $value) {

            $argsName = $value->getName();
            $argsType = $value->getType();
            $paramValue = $this->getRequestParamValue($params, $argsName);

            if ($value->isDefaultValueAvailable()) { //如果有默认值
                if ($paramValue == 'horse_loft_null_value') {
                    $enableParam = $value->getDefaultValue();
                } else {
                    //如果方法参数：有类型、不是混合类型、参数类型不符合要求
                    if ($argsType != null
                        && $argsType->getName() != 'mixed'
                        && !$this->isVarType($argsType->getName(), $paramValue)
                    ) {
                        return [];
                    }
                    $enableParam = $paramValue;
                }
            } else { //如果没有默认值
                //请求参数存在、方法参数没有类型、方法参数是混合类型、请求参数格式=方法参数格式
                if ($paramValue !== 'horse_loft_null_value' &&
                    ($argsType == null
                        || $argsType->getName() == 'mixed'
                        || $this->isVarType($argsType->getName(), $paramValue)
                    )
                ) {
                    $enableParam = $paramValue;
                } else {
                    return [];
                }
            }
            $result[$value->getPosition()] = $enableParam;
        }
        return $result;
    }

    /**
     * --------------------------------------------------------------------------
     * 获取请求参数对应的方法的参数名称
     * --------------------------------------------------------------------------
     *
     * 如果请求参数是下划线分割的字符串，允许方法参数是对应的驼峰格式；
     * 例：user_name -> userName
     *
     * 如果不存在 返回一个标识符
     * 标识符 = 'horse_loft_null_value'
     *
     * @param array $params
     * @param string $argsName
     * @return false|mixed
     */
    private function getRequestParamValue(array $params, string $argsName)
    {
        if (isset($params[$argsName])) {
            return $params[$argsName];
        }

        //如果请求参数是下划线分割的字符串，允许方法参数是对应的驼峰格式；例子：user_name -> userName
        $string = Convert::humpToUnderline($argsName);
        if (isset($params[$string])) {
            return $params[$string];
        }

        //如果不存在 返回一个标识符
        return 'horse_loft_null_value';
    }

    /**
     * 验证$var的数据类型是否=$type
     * @param string $type
     * @param $var
     * @return bool
     */
    private function isVarType(string $type, $var)
    {
        switch ($type) {
            case 'string':
                //允许null
                if (in_array(gettype($var), ['unknown type', 'array', 'object', 'resource'])) {
                    return false;
                }
                break;
            case 'array':
                if (gettype($var) != 'array') {
                    return false;
                }
                break;
            case 'int':
                if (is_numeric($var) == false) {
                    return false;
                }
                break;
            case 'bool':
                if (gettype($var) == 'boolean'
                    || $var === 1
                    || $var === '1'
                    || $var === '0'
                    || $var === 0
                    || strtolower($var) === 'true'
                    || strtolower($var) === 'false'
                ) {
                    return true;
                } else {
                    return false;
                }
            case 'float':
            case 'double':
                if (!is_numeric($var)) {
                    return false;
                }
                break;
            default:
                if ($type != gettype($var)) {
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * 拦截器 | 校验
     */
    private function requestInterceptorHandle()
    {
        $interceptor = $this->container()->getRouteInterceptor();
        if (empty($interceptor)) {
            return;
        }
        // 拦截器回调方法不存在
        $interceptorCall = Horseloft::config('_horseloft_configure_interceptor_.' . $interceptor);
        if (empty($interceptorCall)) {
            throw new HorseloftRequestException('Request Not Allowed');
        }

        // 拦截器返回值 === true 时，允许通过
        $callback = call_user_func($interceptorCall, new Request());
        if ($callback === true) {
            return;
        }
        throw new HorseloftInspectorException(json_encode($callback));
    }

    /**
     * --------------------------------------------------------
     * 拦截器 | 路由
     * --------------------------------------------------------
     *
     * 1. 优先使用自定义路由，如果没有自定义当前路由；如果启用了默认路由，则尝试使用默认路由
     *
     * 2. 默认路由为pathInfo路由并且至少有两级，路由最后一级为控制器方法，路由倒数第二级为控制器，其他为命名空间
     *      例1：http://localhost/admin/user/student/index ==> Controller\Admin\User\StudentController::index
     *      例2：http://localhost/student/index ==> Controller\StudentController::index
     */
    private function requestRouteHandle()
    {
        $uri = trim($this->container()->getRequestUri(), '/');
        if (empty($uri)) {
            throw new HorseloftRequestException('Bad Request Uri');
        }

        $routeUri = $this->container()->getRequestMethod() . '_' . $uri;
        $routeConfig = $this->container()->getRouteConfig();
        $currentRoute = $routeConfig[$routeUri] ?? $routeConfig[$uri] ?? [];

        if (empty($currentRoute)) {
            // 启默认路由 | 检测是使用默认路由
            if ($this->container()->isDefaultRoute() == false) {
                throw new HorseloftRequestException('Request Not Found');
            }
            $uriList = explode('/', $uri);
            if ($uriList < 2) {
                throw new HorseloftRequestException('Request Not Found');
            }
            $uriList = array_map('ucfirst', $uriList);

            $namespace = $this->container()->getNamespace() . '\Controller\\';
            $function = lcfirst(array_pop($uriList));
            $controller = array_pop($uriList) . 'Controller';

            if (!empty($uriList)) {
                $namespace .= implode('\\', $uriList) . '\\';
            } else {
                $namespace .= '';
            }
            $callback = $namespace . $controller . '::' . $function;
        } else {
            // 用户路由
            if (!empty($currentRoute['interceptor'])) {
                // 拦截器名称
                $this->container()->setRouteInterceptor($currentRoute['interceptor']);
            }
            $callback = $currentRoute['callback'];
        }

        if (!is_callable($callback)) {
            throw new HorseloftRequestException('Request Not Found');
        }
        $this->container()->setRouteCallback($callback);
    }

    /**
     * 一次request整理
     *
     * @param array $message
     */
    private function requestDataHandle(array $message = [])
    {
        $log = $this->logToJson($message);

        Horseloft::taskLog($log);

        Spanner::cliPrint($log);
    }

    /**
     * 请求参数
     *
     * @param \Swoole\Http\Request $request
     */
    private function requestParamHandle(\Swoole\Http\Request $request)
    {
        $responseParams = [];
        $rawRequest = @$request->rawContent();
        if (!empty($request->get)) {
            $responseParams = $request->get;
        }
        if (!empty($request->post)) {
            $responseParams = array_merge($responseParams, $request->post);
        }
        if (empty($responseParams) && !empty($rawRequest)) {
            $jsonData = json_decode($rawRequest, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $responseParams = $jsonData;
            } else {
                parse_str($rawRequest, $responseParams);
            }
        }
        $params = empty($responseParams) ? [] : $responseParams;

        //请求参数置于容器
        $this->container()->setParams($params);
    }

    /**
     * 远程IP
     *
     * @param \Swoole\Http\Request $request
     */
    private function remoteAddressHandle(\Swoole\Http\Request $request)
    {
        $requestIP = $request->header['x-forwarded-for'] ?? $request->header['x-real-ip'] ?? $request->server['remote_addr'];
        //如果是代理转发，IP为逗号分隔的字符串
        if (strpos($requestIP, ',')) {
            $address = explode(',', $requestIP);
            $requestIP = end($address);
        }
        $this->container()->setRequestIP($requestIP);
    }
}
