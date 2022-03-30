<?php

namespace Horseloft\Core\Attic;

use Horseloft\Core\Handle\CrontabHandle;
use Horseloft\Core\Utils\Horseloft;

trait Stater
{
    /**
     * --------------------------------------------------------------------------
     *  毫秒定时器
     * --------------------------------------------------------------------------
     *
     */
    protected function timerStarter()
    {
        if (Horseloft::env('timer') !== true) {
            return;
        }
        $crontabData = Horseloft::config('timer');
        if (empty($crontabData) || !is_array($crontabData)) {
            return;
        }

        foreach ($crontabData as $key => $value) :
            if (!is_array($value) || !is_int($value['timer']) || empty($value['callback']) || !is_callable($value['callback'])) {
                continue;
            }
            $process = new \Swoole\Process(function () use ($key, $value) {
                if ("Darwin" != PHP_OS && is_string($key)) {
                    swoole_set_process_name($key);
                }
                \Swoole\Timer::tick($value['timer'], function() use($value) {
                    if (isset($value['args'])) {
                        call_user_func_array($value['callback'], [$value['args']]);
                    } else {
                        call_user_func_array($value['callback'], []);
                    }
                });
            });
            $this->server->addProcess($process);
        endforeach;
    }

    /**
     * --------------------------------------------------------------------------
     * 定时服务 - crontab
     * --------------------------------------------------------------------------
     *
     */
    protected function crontabStarter()
    {
        if (Horseloft::env('crontab') !== true) {
            return;
        }
        $crontabData = Horseloft::config('crontab');
        if (empty($crontabData) || !is_array($crontabData)) {
            return;
        }
        $crontabHandle = new CrontabHandle();

        foreach ($crontabData as $processName => $crontab) :
            if (empty($crontab['command']) || !is_string($crontab['command']) || !is_callable($crontab['callback'])) {
                continue;
            }
            // 将时间分割为5部分
            $timeCommand = $crontabHandle->commandResolve($crontab['command']);
            if (empty($timeCommand)) {
                continue;
            }

            $args = empty($crontab['args']) ? [] : $crontab['args'];
            $callback = $crontab['callback'];

            $process = new \Swoole\Process(function () use ($processName, $callback, $args, $timeCommand){
                if ("Darwin" != PHP_OS && is_string($processName)) {
                    swoole_set_process_name($processName);
                }
                $isRun = false;
                // 每秒检查一次程序的执行时间  仅在时间的第3秒之前执行一次
                \Swoole\Timer::tick(1000, function() use($callback, $args, $timeCommand, &$isRun) {

                    if (!in_array(date('i'), $timeCommand[0])
                        || !in_array(date('G'), $timeCommand[1])
                        || !in_array(date('j'), $timeCommand[2])
                        || !in_array(date('n'), $timeCommand[3])
                        || !in_array(date('W'), $timeCommand[4])
                    ) {
                        return;
                    }

                    $runTime = date('s');
                    if ($runTime < 3 && $isRun == false) {
                        call_user_func_array($callback, $args);
                        $isRun = true;
                    }
                    if ($runTime > 3) {
                        $isRun = false;
                    }
                });
            });
            $this->server->addProcess($process);
        endforeach;
    }

    /**
     * --------------------------------------------------------------------------
     * 用户自定义进程
     * --------------------------------------------------------------------------
     *
     */
    protected function processStater()
    {
        if (Horseloft::env('process') !== true) {
            return;
        }
        $processData = Horseloft::config('process');
        if (empty($processData) || !is_array($processData)) {
            return;
        }

        foreach ($processData as $processName => $pro) :
            if (empty($pro['callback']) || !is_callable($pro['callback']) ) {
                continue;
            }
            $args = empty($pro['args']) ? [] : $pro['args'];
            $callback = $pro['callback'];

            $process = new \Swoole\Process(function () use ($processName, $callback, $args){
                if ("Darwin" != PHP_OS && is_string($processName)) {
                    swoole_set_process_name($processName);
                }
                while (true) {
                    call_user_func_array($callback, $args);
                }
            });
            $this->server->addProcess($process);
        endforeach;
    }
}
