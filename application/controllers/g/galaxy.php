<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/GameController.php';

class Galaxy extends GameController
{

    function __construct()
    {
        parent::__construct();
    }

    function index_get()
    {
        $galaxy = $this->input->get('galaxy', TRUE);
        $system = $this->input->get('system', TRUE);
        if(intval($galaxy)<=0 || intval($system)<=0){
            $query['code'] = $this->g_errcode['err'];
            $this->_g_response_without_update($query);
        }
        $query = $this->_galaxy_list($galaxy,$system);
        if($query){
            $query['code'] = $this->g_errcode['ok'];
            $this->_g_response_without_update($query);
        }

        $query['code'] = $this->g_errcode['err'];
        $this->_g_response_without_update($query);
    }

    private function _galaxy_list($galaxy,$system)
    {
        $planets = $this->planet_model->get_planets_by_galaxy_system($galaxy,$system);
        return array('planets'=>$planets);
    }

    private function  _galaxy_info()
    {

    }
}

/* End of file */
