<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/GameController.php';

class Test extends GameController
{

    function __construct()
    {
        parent::__construct();
    }

    function index_get()
    {
        echo $this->config->item('game_test');
        $this->load->helper('url');
        $this->load->view('welcome_message');
    }

    function test1_get()
    {
        print_r($this->g_config);

        //$this->load->model('user_model');
        //$this->user_model->test();
    }
}

/* End of file */
