<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/GameController.php';

class Tech extends GameController
{

    function __construct()
    {
        parent::__construct();
        fs()->includeLang('tech');
    }

    function index_get()
    {
        $this->tree_get();
    }

    function tree_get()
    {
        $query = $this->_tree();
        $query['code'] = $this->g_errcode['ok'];
        $this->_g_response_without_update($query);
    }

    function detail_get()
    {
    }

    private function _tree()
    {
        global $lang, $ge_resource, $ge_requeriments;

        $rtn = array();
        foreach ($lang['tech'] as $Element => $ElementName) {
            $parse = array('tech' => $Element, 'tech_name' => $ElementName);
            if (!isset($ge_resource[$Element])) {
                $parse['Requirements'] = $lang['Requirements'];
            } else {
                if (isset($ge_requeriments[$Element])) {
                    $parse['required_list'] = array(); //$ge_requeriments[$Element];
                    foreach ($ge_requeriments[$Element] as $re => $rlevel) {
                        $tr = array('required' => $re, 'level' => $rlevel, 'required_name' => $lang['tech'][$re]);
                        $parse['required_list'][] = $tr;
                    }
                } else {
                    $parse['required_list'] = "";
                    $parse['tt_detail'] = "";
                }
            }
            $rtn[] = $parse;
        }
        return array('techList'=>$rtn);
    }

    private function  _detail()
    {

    }
}

/* End of file */
