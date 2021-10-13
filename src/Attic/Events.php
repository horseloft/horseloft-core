<?php

namespace Horseloft\Core\Attic;

trait Events
{
    /**
     * --------------------------------------------------------------------------
     * worker启动事件
     * --------------------------------------------------------------------------
     *
     */
    private function onWorkerStart()
    {
        $this->container()->getServer()->on('workerStart', function(\Swoole\Server $server, int $workerId) {
            if(isset($server->setting['worker_num']) && $workerId >= $server->setting['worker_num']) {
                if ("Darwin" != PHP_OS) {
                    swoole_set_process_name('php: task_' . ($workerId - $server->setting['worker_num']));
                }
            } else {
                if ("Darwin" != PHP_OS) {
                    swoole_set_process_name('php: worker_' . $workerId);
                }
            }
        });
    }

    /**
     * --------------------------------------------------------------------------
     * task事件
     * --------------------------------------------------------------------------
     *
     * task进程被调用时触发
     *
     * 调用task时，传递的参数:
     * [
     *    'function' => [class, functionName], //完整命名空间的类方法，方法名称
     *    'params' => [] //调用的方法的参数
     * ]
     *
     */
    private function onTask()
    {
        $this->container()->getServer()->on('task', function (\Swoole\Server $server, int $task_id, int $src_worker_id, $data) {
            $handle = '';
            if (isset($data['function']) && is_callable($data['function'])) {
                if (isset($data['params'])) {
                    $handle = call_user_func_array($data['function'], $data['params']);
                } else {
                    $handle = call_user_func($data['function']);
                }
            }
            $server->finish($handle);
        });
    }

    /**
     * * --------------------------------------------------------------------------
     * task进程主动调用finish()方法的回调
     * --------------------------------------------------------------------------
     *
     * task 进程的 onTask 事件中没有调用 finish 方法或者 return 结果，worker 进程不会触发 onFinish
     *
     */
    private function onFinish()
    {
        $this->container()->getServer()->on('finish', function (\Swoole\Server $server, int $task_id, $data) {

        });
    }
}
