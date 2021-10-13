<?php

namespace Horseloft\Core\Utils;

class Convert
{
    /**
     * 驼峰字符串转为下划线分割的字符串
     * @param string $string
     * @return string
     */
    public static function humpToUnderline(string $string)
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string));
    }

    /**
     * 下划线分割的字符串转为驼峰字符串
     * @param string $string
     * @return string|string[]|null
     */
    public static function strToHump(string $string)
    {
        return preg_replace_callback('/_+([a-z])/',function($matches){
            return strtoupper($matches[1]);
        }, $string);
    }
}