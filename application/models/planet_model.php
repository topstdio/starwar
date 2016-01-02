<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/models/base_model.php';

class Planet_Model extends Base_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    public function get_planet_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->_table('planets'));
        if ($query) {
            return $query->result_array();
        } else {
            return null;
        }
    }

    public function get_planets_by_galaxy_system($galaxy,$system)
    {
        $this->db->where('galaxy', $galaxy);
        $this->db->where('system', $system);
        $query = $this->db->get($this->_table('planets'));
        if ($query) {
            return $query->result_array();
        } else {
            return null;
        }
    }

    public function update_planet_by_id($id, $data)
    {
        if (empty($data)) {
            return null;
        }
        if (intval($id) <= 0) {
            return null;
        }
        $this->db->where('id', $id);
        return $this->db->update($this->_table('planets'), $data);
    }

}
