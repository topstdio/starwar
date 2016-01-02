<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/models/base_model.php';

class Galaxy_Model extends Base_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    public function get_galaxy_by_planet_id($planet_id)
    {
        $this->db->where('id_planet', $planet_id);
        $query = $this->db->get($this->_table('galaxy'));
        if ($query) {
            return $query->result_array();
        } else {
            return null;
        }
    }

    public function get_galaxy_system($galaxy, $system)
    {
        $this->db->where('galaxy', $galaxy);
        $this->db->where('system', $system);
        $query = $this->db->get($this->_table('galaxy'));
        if ($query) {
            return $query->result_array();
        } else {
            return null;
        }
    }

}
