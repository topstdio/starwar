<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/models/base_model.php';

class Common_Model extends Base_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }

    public function doquery($sql, $fetch = false)
    {
        $query = $this->db->query($sql);
        log_message('debug', 'sql:'.$sql);

        if ($fetch) {
            $arr = $query->result_array();
            if (!empty($arr)) {
                return $arr[0];
            } else {
                return null;
            }

        } else {
            return $query;
        }
    }

}
