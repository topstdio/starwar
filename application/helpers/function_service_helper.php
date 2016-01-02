<?php

/**
 * Created by PhpStorm.
 * User: duansz
 * Date: 7/11/15
 * Time: 9:08 上午
 */

require_once APPPATH . '/helpers/base_service.php';

class function_service extends base_service
{
    private static $single;

    function __construct()
    {
        parent::__construct();

        $this->lang = array();
        $this->includeLang('system');
        $this->includeLang('tech');
    }

    public static function single()
    {
        if (!self::$single) {
            self::$single = new function_service();
        }
        return self::$single;
    }

    function __call($name, $arguments)
    {
        require_once APPPATH . '/helpers/functions/' . $name . '.php';
        return call_user_func_array($name, $arguments);
    }

    function doquery($query, $table, $fetch = false)
    {
        return $this->g()->doquery($query, $table, $fetch);
    }

    function game_config()
    {
        return $this->g()->game_config();
    }
    function &current_user()
    {
        $u = &$this->g()->current_user();
        return $u;
    }
    function &current_planet()
    {
        $p = &$this->g()->current_planet();
        return $p;
    }

    function pretty_number($n, $floor = true) {
        if ($floor) {
            $n = floor($n);
        }
        return number_format($n, 0, ",", ".");
    }

    function includeLang ($filename, $ext = '.php', $type = 'cn') {
        global $lang;
        include_once( APPPATH . 'helpers/lang/' . $type .'/'. $filename . '.php');
    }
}

function fs()
{
    return function_service::single();
}