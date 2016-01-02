<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

abstract class GameController extends REST_Controller
{
    protected $g_config;
    protected $g_errcode;
    protected $g_user;
    protected $g_planet;
    protected $g_galaxy;
    protected $g_dbperfix;

    function __construct()
    {
        parent::__construct();

        $this->load->config('game');        //游戏设置
        $this->load->helper('vars');        //游戏数值系统
        $this->load->helper('formula');     //游戏计算公式

        //游戏3大数据
        $this->load->model('user_model');
        $this->load->model('planet_model');
        $this->load->model('galaxy_model');

        //游戏通用数据操作类
        $this->load->model('common_model');

        //桥接
        $this->load->helper('function_service');

        $this->g_dbperfix = 'game_';
    }

    //执行一些公共查询
    protected function _before_method($args)
    {
        //初始化config
        $this->g_config = $this->config->item('game_configs');
        $this->g_errcode = $this->config->item('game_code');

        //check user
        $username = 'admin';    //TODO : test

        //get user
        $user = $this->user_model->get_user_by_name($username);
        if (count($user) != 1) {
            $this->response('user_error', 200);
        } else {
            $this->g_user = $user[0];
        }


        //fleet

        //planet
        $planet = $this->planet_model->get_planet_by_id($this->g_user['current_planet']);
        if (count($planet) != 1) {
            $this->response('planet_error', 200);
        } else {
            $this->g_planet = $planet[0];
        }

        //galaxy
        $galaxy = $this->galaxy_model->get_galaxy_by_planet_id($this->g_planet['id']);
        if (count($galaxy) != 1) {
            $this->response('galaxy_error', 200);
        } else {
            $this->g_galaxy = $galaxy[0];
        }

    }

    //执行一些公共查询
    protected function _after_method($args=null)
    {
        if (!empty($this->g_user)) {
            if (empty($this->g_planet)) {
                $this->g_planet = fs()->doquery("SELECT * FROM {{table}} WHERE `id` = '" . $this->g_user['current_planet'] . "';", 'planets', true);
            }

            fs()->PlanetResourceUpdate($this->g_user, time());

        }
    }

    protected function _g_response($data = null, $http_code = null, $continue = false){
        $this->_after_method();
        $this->_g_response_without_update($data,$http_code,$continue);
    }
    protected function _g_response_without_update($data = null, $http_code = null, $continue = false){
        header('Access-Control-Allow-Origin:*');    //跨域
        $this->response($data,$http_code,$continue);
    }

    protected function _message($msg, $title = '')
    {
        $msg = array('code' => $this->g_errcode['message'], 'message' => (string)$msg, 'title' => (string)$title);
        $this->response($msg);
    }

    //桥接函数
    public function doquery($query, $table, $fetch = false)
    {
        $sql = str_replace("{{table}}", $this->g_dbperfix . $table, $query);
        return $this->common_model->doquery($sql, $fetch);
    }

    public function game_config()
    {
        return $this->g_config;
    }

    public function &current_user()
    {
        return $this->g_user;
    }

    public function &current_planet()
    {
        return $this->g_planet;
    }

}

/* End of file*/