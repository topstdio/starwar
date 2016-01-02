<?php

/**
 * Created by PhpStorm.
 * User: duansz
 * Date: 7/11/15
 * Time: 9:08 上午
 */
class base_service
{
    function __construct(){
        
    }

    protected function &g(){
        return get_instance();
    }
} 