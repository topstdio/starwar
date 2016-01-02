<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/models/base_model.php';

class Luna_Model extends Base_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
    }


}
