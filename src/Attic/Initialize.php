<?php

namespace Horseloft\Core\Attic;

use Horseloft\Core\Handle\RouteHandle;

trait Initialize
{
    /**
     * --------------------------------------------------------------------------
     * 设置服务的配置信息
     * --------------------------------------------------------------------------
     */
    private function setEnvironment(string $applicationPath)
    {
        if (!is_file($applicationPath . '/env.ini')) {
            exit('env.ini文件不存在');
        }
        $application = parse_ini_file($applicationPath . '/env.ini', true, INI_SCANNER_RAW);

        if (empty($application['host'])) {
            exit('host错误');
        }
        if (empty($application['port'])) {
            exit('port号错误');
        }
        $this->container()->setHost($application['host']);
        $this->container()->setPort($application['port']);

        // debug
        if ($application['debug'] === false) {
            $this->container()->setDebug(false);
        } else {
            $this->container()->setDebug(true);
        }

        // default route
        if ($application['default_route'] === false) {
            $this->container()->setDefaultRoute(false);
        } else {
            $this->container()->setDefaultRoute(true);
        }

        // 日志目录、日志文件
        if (is_dir($application['log_path'])) {
            $this->container()->setLogPath($application['log_path']);
        } else {
            $thisLogPath = $this->container()->getApplicationPath() . '/Log';
            if (!is_dir($thisLogPath)) {
                exit('log目录缺失');
            }
            $this->container()->setLogPath($thisLogPath);
        }
        $this->container()->setLogFilename('horseloft.log');

        // 服务启动项
        $swooleConfig = [
            'log_file' => $this->container()->getLogPath() . '/' . strtolower($this->container()->getLogFilename())
        ];
        if (is_array($application['swoole'])) {
            $swooleConfig = array_merge($application['swoole'], $swooleConfig);
        }
        $this->container()->setSwooleConfig($swooleConfig);

        // env.ini文件内容以数组格式保留
        $this->container()->setConfigure('_horseloft_configure_env_ini_', $application);
    }

    /**
     * --------------------------------------------------------------------------
     *  读取路由目录下的路由配置文件 并加入容器
     * --------------------------------------------------------------------------
     */
    private function readSetRoute()
    {
        $routePath = $this->container()->getApplicationPath() . '/Route';
        $routeList = $this->readApplicationFile($routePath);

        $routeHandle = new RouteHandle($this->container()->getNamespace() . '\Controller\\');

        foreach ($routeList as $list) :
            $route = $routeHandle->getRequestRoute($list);
            foreach ($route as $value) {
                $this->container()->setRouteConfig($value['uri'], $value['request']);
            }
        endforeach;
    }

    /**
     * --------------------------------------------------------------------------
     *  自动读取 Interceptor 目录下的类文件 并作为拦截器使用
     * --------------------------------------------------------------------------
     *
     * 1. 以小驼峰格式的文件名称作为拦截器名称
     *
     * 2. 类中必须有handle方法
     *
     * 3. handle方法必须有一个Request类型的参数
     *
     * 4. Request类全路径：Horseloft\Core\Drawer\Request
     */
    private function readSetInterceptor()
    {
        $dir = $configPath = $this->container()->getApplicationPath() . '/Interceptor';
        if (!is_dir($dir)) {
            return;
        }

        $interceptor = [];
        $namespace = $this->container()->getNamespace() . '\Interceptor\\';
        $handle = opendir($configPath);
        while (false !== $file = readdir($handle)) {
            if ($file == '.' || $file == '..') {
                continue;
            } else {
                try {
                    $suffix = substr($file, -4);
                    if ($suffix == false || $suffix != '.php') {
                        continue;
                    }
                    $interceptorName = ucfirst(substr($file, 0, -4));
                    $interceptorClass = $namespace . $interceptorName;
                    $cls = new \ReflectionClass($interceptorClass);
                    $method = $cls->getMethod('handle');
                    $methodNumber = $method->getNumberOfParameters();
                    if ($methodNumber == 0) {
                        exit('路由拦截器[' . $interceptorName . '->handle]缺少Request参数');
                    }
                    if ($methodNumber > 1) {
                        exit('路由拦截器[' . $interceptorName . '->handle]仅允许一个Request类型的参数');
                    }
                    $params = $method->getParameters();

                    $paramClass = $params[0]->getClass();
                    if (is_null($paramClass)) {
                        exit('路由拦截器[' . $interceptorName . '->handle]第一个参数应是Request类型');
                    }

                    $paramClassName = $paramClass->getName();
                    if ($paramClassName != 'Horseloft\Core\Drawer\Request') {
                        exit('路由拦截器[' . $interceptorName . '->handle]第一个参数必须是Request类型');
                    }

                    $interceptor[$interceptorName] = [$interceptorClass, 'handle'];

                } catch (\Exception $e){
                    exit($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }
            }
        }
        if (!empty($interceptor)) {
            $this->container()->setConfigure('_horseloft_configure_interceptor_', $interceptor);
        }
        closedir($handle);
    }

    /**
     * --------------------------------------------------------------------------
     *  设置全局配置信息
     * --------------------------------------------------------------------------
     */
    private function readSetConfig()
    {
        $configPath = $this->container()->getConfigDir();
        if (!is_dir($configPath)) {
            exit('Config目录缺失');
        }

        $handle = opendir($configPath);
        while (false !== $file = readdir($handle)) {
            if ($file == '.' || $file == '..') {
                continue;
            } else {
                try {
                    $suffix = substr($file, -4);
                    if ($suffix == false || $suffix != '.php') {
                        continue;
                    }
                    $configure = require_once $configPath . '/' . $file;
                    if (!is_array($configure)) {
                        continue;
                    }
                    $this->container()->setConfigure(substr($file, 0, -4), $configure);

                } catch (\Exception $e){
                    continue;
                }
            }
        }
        closedir($handle);
    }

    /**
     * ------------------------------------------------------------
     * 读取目录下的.php文件 并返回文件内容
     * ------------------------------------------------------------
     *
     * 1. 仅读取当前目录内的文件，不读取目录下的目录
     *
     * 2. 仅获取文件返回的数组数据
     *
     * @param string $path
     * @return array
     */
    private function readApplicationFile(string $path)
    {
        if (!is_dir($path)) {
            return [];
        }

        $fileInfo = [];
        $handle = opendir($path);
        while (false !== $file = readdir($handle)) {
            if ($file == '.' || $file == '..' || !is_file($path . '/' . $file)) {
                continue;
            } else {
                try {
                    $suffix = substr($file, -4);
                    if ($suffix == false || $suffix != '.php') {
                        continue;
                    }
                    $configure = require_once $path . '/' . $file;
                    if (is_array($configure)) {
                        $fileInfo[] = $configure;
                    }
                } catch (\Exception $e){
                    continue;
                }
            }
        }
        closedir($handle);

        return $fileInfo;
    }
}
