<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');


abstract class Base_Model extends CI_Model
{
    protected $db_perfix;

    public function __construct()
    {
        parent::__construct();

        $this->db_perfix='game_';
    }

    public function __call($name, $arguments)
    {
        echo "[Model method [ " . get_class($this) . " $name ] called]";
    }

    protected function _table($table){
        return $this->db_perfix.$table;
    }
}
