<?php

namespace Horseloft\Core\Attic;

use Horseloft\Core\Drawer\Spanner;

trait Logger
{
    /**
     * 日志组装
     *
     * @param array $message
     * @param string $level
     * @return string
     */
    private function logToJson(array $message = [], string $level = 'info') {
        $container = $this->container();
        $log = [
            'method' => $container->getRequestMethod(),
            'param' => $container->getParams(),
            'ip' => $container->getRequestIP(),
            'cookie' => $container->getRequestCookie(),
            'file' => $container->getRequestFiles()
        ];
        $date = (new \DateTime)->format('Y-m-d H:i:s:u');
        $json = ltrim(Spanner::encode(array_merge($log, $message)), '{');

        return '{"level":"' . $level . '","datetime":"' . $date . '","uri":"' . $container->getRequestUri() . '",' . $json;
    }
}
