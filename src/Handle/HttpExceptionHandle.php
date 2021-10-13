<?php

namespace Horseloft\Core\Handle;

use Horseloft\Core\Attic\Logger;
use Horseloft\Core\Attic\Container;
use Horseloft\Core\Drawer\Request;
use Horseloft\Core\Drawer\Spanner;
use Horseloft\Core\Exceptions\HorseloftInspectorException;
use Horseloft\Core\Utils\Helper;

class HttpExceptionHandle
{
    use Container,Logger;

    /**
     * @var \Throwable
     */
    private $exception;

    /**
     * HttpExceptionHandle constructor.
     * @param \Throwable $e
     */
    public function __construct(\Throwable $e)
    {
        $this->exception = $e;
    }

    /**
     * 获取异常信息
     *
     * @return array|false|mixed|string
     */
    public function getResponse()
    {
        // 拦截器自定义异常
        $customize = $this->getCustomizeException();
        if (!is_null($customize)) {
            return $customize;
        }

        // 服务自身的异常
        $horseloft = $this->getHorseloftException();
        if (!is_null($horseloft)) {
            return $horseloft;
        }

        return $this->getThrowableException();
    }

    /**
     * 日志记录并输出到命令行
     *
     * @return array
     */
    private function getThrowableException()
    {
        $data = $this->exception->getTrace();
        $data[0]['file'] = $this->exception->getFile();
        $data[0]['line'] = $this->exception->getLine();
        $data[0]['message'] = $this->exception->getMessage();

        //日志记录错误信息
        Helper::logTask($this->logToJson($data, 'error'));

        //输出错误信息到命令行
        Spanner::cliExceptionPrint($this->exception);

        //是否输出错误数据
        if (!$this->container()->isDebug()) {
            $data = [];
        }
        return $data;
    }

    /**
     * horseloft-core的自定义异常解析
     *
     * @return mixed|null
     */
    private function getHorseloftException()
    {
        if ($this->exception instanceof HorseloftInspectorException) {
            return json_decode($this->exception->getMessage(), true);
        }
        return null;
    }

    /**
     * 如果在Exceptions下存在与异常名称相同的类 则使用该类处理异常，该类需要满足以下条件：
     *  1：类中存在handle(Request $request, \Throwable $this->exception)方法
     *  2：handle()方法支持两个参数：
     *   第一个参数：Request $request
     *   第二个参数：\Throwable $e
     *  3：如果类中出现异常 则使用默认处理
     *
     * 返回值作为当前一起请求的响应值
     *
     * 如果没有自定义的异常处理 则使用默认处理
     *
     * @return false|mixed|null
     */
    private function getCustomizeException()
    {
        $reflection = new \ReflectionClass($this->exception);
        $call = [
            $this->container()->getNamespace() . '\Exceptions\\' . $reflection->getName(),
            'handle'
        ];
        if (is_callable($call)) {
            try {
                return call_user_func_array($call, [new Request(), $this->exception]);
            } catch (\Throwable $e){
                //有异常使用默认处理
            }
        }
        return null;
    }
}