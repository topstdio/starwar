<?php

if (!function_exists('test_function')) {
    function test_function($a, $b)
    {
        global $ge_ProdGrid, $ge_resource, $ge_reslist, $game_config;

        print_r($ge_ProdGrid);

        echo 'test_function:s:' . $a . $b;
        $rtn = function_service::single()->doquery("select * from {{table}} where id='1' ", 'users', true);
        print_r($rtn);
        echo 'test:e';
    }
}


//end of file