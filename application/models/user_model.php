<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/models/base_model.php';

class User_Model extends Base_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    public function get_user_by_name($username)
    {
        $this->db->where('username', $username);
        $query = $this->db->get($this->_table('users'));
        if ($query) {
            return $query->result_array();
        } else {
            return null;
        }
    }

    public function update_user_by_id($id,$data){
        if(empty($data)){
            return null;
        }
        if(intval($id)<=0){
            return null;
        }
        $this->db->where('id', $id);
        return $this->db->update($this->_table('users'), $data);
    }

}
