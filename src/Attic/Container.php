<?php

namespace Horseloft\Core\Attic;

trait Container
{
    /**
     * @return \Horseloft\Core\Drawer\Horseloft
     */
    public function container()
    {
        return $GLOBALS['_HORSELOFT_CORE_CONTAINER_'];
    }
}
