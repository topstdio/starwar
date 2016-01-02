<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . '/libraries/GameController.php';


class Building extends GameController
{

    function __construct()
    {
        parent::__construct();
        fs()->includeLang('buildings');
    }

    public function example_get()
    {
//        $this->load->helper('function_service');
//        $t=array('h'=>2,'m'=>3);
//        $m = 'hh';
//        fs()->c('test_function',array(&$t,$m),array(&$m));
//        print_r($t);
//        $ts = &fs()->current_user();
//        $ts['username']='i am polly';
//        print_r($ts);
//        print_r($this->g_user);
    }

    function index_get()
    {
        $this->basic_get();
    }

    function basic_get()
    {
        $cmd = $this->input->get('cmd', TRUE);
        $building = $this->input->get('building', TRUE);
        $listid = $this->input->get('listid', TRUE);

        $query = $this->_building($cmd, $building, $listid);
        $query['code'] = $this->g_errcode['ok'];
        $this->_g_response($query);
    }

    function research_get()
    {
        $cmd = $this->input->get('cmd', TRUE);
        $techid = $this->input->get('techid', TRUE);

        $query = $this->_research($cmd, $techid);
        $query['code'] = $this->g_errcode['ok'];
        $this->_g_response($query);
    }

    function fleet_get()
    {
        $builds = $this->input->get();

        $query = $this->_fleet($builds);
        $query['code'] = $this->g_errcode['ok'];
        $this->_g_response($query);
    }

    function defense_get()
    {
        $builds = $this->input->get();

        $query = $this->_defense($builds);
        $query['code'] = $this->g_errcode['ok'];
        $this->_g_response($query);
    }


    //组织建筑相关数据
    private function _building($TheCommand = null, $Element = null, $ListID = null)
    {
        global $lang, $ge_resource;

        fs()->UpdatePlanetBatimentQueueList();
        $IsWorking = fs()->HandleTechnologieBuild();
        fs()->CheckPlanetUsedFields( /*$CurrentPlanet*/);

        $Allowed['1'] = array(1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 44);
        $Allowed['3'] = array(12, 14, 21, 22, 23, 24, 34, 41, 42, 43);

        //建造
        if (isset($_GET['cmd'])) {
            //check_urlaubmodus(); //休假模式
            $bDoItNow = false;
            if (isset ($Element) && null != $Element) {
                if (!strchr($Element, " ")) {
                    if (in_array(trim($Element), $Allowed[$this->g_planet['planet_type']])) {
                        $bDoItNow = true;
                    }
                }
            } elseif (isset ($ListID)) {
                $bDoItNow = true;
            }
            if ($bDoItNow == true) {
                switch ($TheCommand) {
                    case 'cancel':
                        fs()->CancelBuildingFromQueue();
                        break;
                    case 'remove':
                        fs()->RemoveBuildingFromQueue($ListID);
                        break;
                    case 'insert':
                        fs()->AddBuildingToQueue($this->g_user, $Element, true);
                        break;
                    case 'destroy':
                        fs()->AddBuildingToQueue($this->g_user, $Element, false);
                        break;
                    default:
                        break;
                } // switch
            }
        }

        fs()->SetNextQueueElementOnTop($this->g_user);
        fs()->BuildingSavePlanetRecord($this->g_planet);
        fs()->BuildingSaveUserRecord($this->g_user);

        $building = $this->_building_query($this->g_planet);
        //print_r($building);

        if ($building['lenght'] < MAX_BUILDING_QUEUE_SIZE) {
            $CanBuildElement = true;
        } else {
            $CanBuildElement = false;
        }
        $CurrentMaxFields = fs()->CalculateMaxPlanetFields($this->g_planet);
        if ($this->g_planet["field_current"] < ($CurrentMaxFields - $building['lenght'])) {
            $RoomIsOk = true;
        } else {
            $RoomIsOk = false;
        }

        $tobuild = array();
        foreach ($lang['tech'] as $Element => $ElementName) {
            if (in_array($Element, $Allowed[$this->g_planet['planet_type']])) {
                $tp = array('element' => $Element, 'element_title' => $ElementName);
                $tp['room_is_ok'] = (int)$RoomIsOk;

                if (fs()->IsTechnologieAccessible($this->g_user, $this->g_planet, $Element)) {
                    $HaveResources = fs()->IsElementBuyable($this->g_user, $this->g_planet, $Element, true, false);
                    $tp['have_resource'] = (int)$HaveResources;
                    $tp['building_level'] = (int)$this->g_planet[$ge_resource[$Element]];
                    $tp['build_time'] = (int)fs()->GetBuildingTime($this->g_user, $this->g_planet, $Element);
                    $tp['price'] = fs()->GetElementPrice($this->g_user, $this->g_planet, $Element);
                }

                $tobuild[] = $tp;
            }
        }

        $rtn = array('building' => $building, 'basic' => $tobuild, 'can_build' => (int)$CanBuildElement);
        return $rtn;
    }

    private function _research($TheCommand, $Techno)
    {
        global $lang, $ge_resource, $ge_reslist;
        $CurrentPlanet = &fs()->current_planet();
        $CurrentUser = $this->g_user;

        fs()->UpdatePlanetBatimentQueueList();
        $IsWorking = fs()->HandleTechnologieBuild();
        $InResearch = $IsWorking['OnWork'];
        $ThePlanet = $IsWorking['WorkOn'];

        if ($CurrentPlanet[$ge_resource[31]] == 0) {    //没有研究所
            $this->_message($lang['no_laboratory'], $lang['Research']);
        }

        if (!empty($TheCommand)) {
            if (is_numeric($Techno) && in_array($Techno, $ge_reslist['tech'])) {
                if (is_array($ThePlanet)) {
                    $WorkingPlanet = $ThePlanet;
                } else {
                    $WorkingPlanet = $CurrentPlanet;
                }

                $UpdateData = false;
                switch ($TheCommand) {
                    case 'cancel':
                        if ($ThePlanet['b_tech_id'] == $Techno) {
                            $costs = fs()->GetBuildingPrice($CurrentUser, $WorkingPlanet, $Techno);
                            $WorkingPlanet['metal'] += $costs['metal'];
                            $WorkingPlanet['crystal'] += $costs['crystal'];
                            $WorkingPlanet['deuterium'] += $costs['deuterium'];
                            $WorkingPlanet['b_tech_id'] = 0;
                            $WorkingPlanet["b_tech"] = 0;
                            $CurrentUser['b_tech_planet'] = 0;
                            $UpdateData = true;
                            $InResearch = false;
                        }
                        break;
                    case 'search':
                        if (fs()->IsTechnologieAccessible($CurrentUser, $WorkingPlanet, $Techno) && fs()->IsElementBuyable($CurrentUser, $WorkingPlanet, $Techno)) {
                            $costs = fs()->GetBuildingPrice($CurrentUser, $WorkingPlanet, $Techno);
                            $WorkingPlanet['metal'] -= $costs['metal'];
                            $WorkingPlanet['crystal'] -= $costs['crystal'];
                            $WorkingPlanet['deuterium'] -= $costs['deuterium'];
                            $WorkingPlanet["b_tech_id"] = $Techno;
                            $WorkingPlanet["b_tech"] = time() + fs()->GetBuildingTime($CurrentUser, $WorkingPlanet, $Techno);
                            $CurrentUser["b_tech_planet"] = $WorkingPlanet["id"];
                            $UpdateData = true;
                            $InResearch = true;
                        }
                        break;
                }

                if ($UpdateData == true) {
                    $pdata = array(
                        'b_tech_id' => $WorkingPlanet['b_tech_id'],
                        'b_tech' => $WorkingPlanet['b_tech'],
                        'metal' => $WorkingPlanet['metal'],
                        'crystal' => $WorkingPlanet['crystal'],
                        'deuterium' => $WorkingPlanet['deuterium'],
                    );
                    $this->planet_model->update_planet_by_id($WorkingPlanet['id'], $pdata);

                    $udata = array(
                        'b_tech_planet' => $CurrentUser['b_tech_planet'],
                    );
                    $this->user_model->update_user_by_id($CurrentUser['id'], $udata);
                }

                if (is_array($ThePlanet)) {
                    $ThePlanet = $WorkingPlanet;
                } else {
                    $CurrentPlanet = $WorkingPlanet;
                    if ($TheCommand == 'search') {
                        $ThePlanet = $CurrentPlanet;
                    }
                }
            }


        }

        $researching = array();
        if ($InResearch) {
            $btp = array(
                'tech_time' => $ThePlanet["b_tech"] - time(),
                'planet_name' => $ThePlanet["name"],
                'planet_id' => $ThePlanet["id"],
                'tech' => $ThePlanet["b_tech_id"],
            );
            $researching[] = $btp;
        }

        $lab_settings_inquery = fs()->CheckLabSettingsInQueue($CurrentPlanet);
        $toresearch = array();
        foreach ($lang['tech'] as $Tech => $TechName) {
            if ($Tech > 105 && $Tech <= 199) {
                if (fs()->IsTechnologieAccessible($this->g_user, $CurrentPlanet, $Tech)) {
                    $tp = array('tech' => $Tech, 'tech_name' => $TechName);

                    $building_level = $this->g_user[$ge_resource[$Tech]];
                    $tp['tech_level'] = ($building_level == 0) ? "" : "( " . $lang['level'] . " " . $building_level . " )";
                    $tp['tech_desc'] = $lang['res']['descriptions'][$Tech];
                    $tp['tech_price'] = fs()->GetElementPrice($CurrentUser, $CurrentPlanet, $Tech);
                    $SearchTime = fs()->GetBuildingTime($CurrentUser, $CurrentPlanet, $Tech);
                    $tp['tech_time'] = $SearchTime;
                    $CanBeDone = fs()->IsElementBuyable($CurrentUser, $CurrentPlanet, $Tech);
                    $tp['tech_can'] = (int)$CanBeDone;
                    if ($InResearch && $ThePlanet["b_tech_id"] == $Tech) {
                        $tp['is_researching'] = 1;
                    } else {
                        $tp['is_researching'] = 0;
                    }
                    $toresearch[] = $tp;
                }
            }
        }

        $rtn = array(
            'lab_status' => (int)$lab_settings_inquery,
            'researching' => $researching,
            'basic' => $toresearch,
        );

        return $rtn;
    }

    private function _fleet($fmenge = null)
    {
        global $lang, $ge_resource;
        $CurrentPlanet = &fs()->current_planet();
        $CurrentUser = $this->g_user;

        fs()->UpdatePlanetBatimentQueueList();
        $IsWorking = fs()->HandleTechnologieBuild();

        if ($CurrentPlanet[$ge_resource[21]] == 0) {
            $this->_message($lang['need_hangar'], $lang['tech'][21]);
        }

        if (!empty($fmenge)) {
            //check_urlaubmodus(); //休假模式

            foreach ($fmenge as $Element => $Count) {
                $Element = intval($Element);
                $Count = intval($Count);
                if ($Count > MAX_FLEET_OR_DEFS_PER_ROW) {
                    $Count = MAX_FLEET_OR_DEFS_PER_ROW;
                }
                if ($Count != 0) {
                    if (fs()->IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element)) {
                        $MaxElements = fs()->GetMaxConstructibleElements($Element, $CurrentPlanet);
                        if ($Count > $MaxElements) {
                            $Count = $MaxElements;
                        }
                        $Ressource = fs()->GetElementRessources($Element, $Count);
                        //$BuildTime = fs()->GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);

                        if ($Count >= 1) {
                            $CurrentPlanet['metal'] -= $Ressource['metal'];
                            $CurrentPlanet['crystal'] -= $Ressource['crystal'];
                            $CurrentPlanet['deuterium'] -= $Ressource['deuterium'];
                            $CurrentPlanet['b_hangar_id'] = $CurrentPlanet['b_hangar_id'] . $Element . "," . $Count . ";";
                        }
                    }
                }
            }

        }

        //building
        $building = $this->_fleeting_query($this->current_planet());

        //basic
        $basic = array();
        foreach ($lang['tech'] as $Element => $ElementName) {
            if ($Element > 201 && $Element <= 399) {
                if (fs()->IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element)) {
                    $tp = array('fleet' => $Element, 'fleet_name' => $ElementName);
                    $tp['fleet_can_one'] = fs()->IsElementBuyable($CurrentUser, $CurrentPlanet, $Element, false);
                    $tp['fleet_time_one'] = fs()->GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);
                    $tp['count'] = $CurrentPlanet[$ge_resource[$Element]];
                    $tp['fleet_desc'] = $lang['res']['descriptions'][$Element];
                    $tp['fleet_price'] = fs()->GetElementPrice($CurrentUser, $CurrentPlanet, $Element, false);
                    $basic[] = $tp;
                }
            }
        }

        $rtn = array('building' => $building, 'basic' => $basic);
        return $rtn;
    }

    private function _defense($fmenge = null)
    {
        global $lang, $ge_resource;
        $CurrentPlanet = &fs()->current_planet();
        $CurrentUser = $this->g_user;

        fs()->UpdatePlanetBatimentQueueList();
        $IsWorking = fs()->HandleTechnologieBuild();

        if ($CurrentPlanet[$ge_resource[21]] == 0) {
            $this->_message($lang['need_hangar'], $lang['tech'][21]);
        }

        if (!empty($fmenge)) {
            //check_urlaubmodus();

            $Missiles = array();
            $Missiles[502] = $CurrentPlanet[$ge_resource[502]];
            $Missiles[503] = $CurrentPlanet[$ge_resource[503]];
            $SiloSize = $CurrentPlanet[$ge_resource[44]];
            $MaxMissiles = $SiloSize * 10;

            $BuildQueue = $CurrentPlanet['b_hangar_id'];
            $BuildArray = explode(";", $BuildQueue);
            for ($QElement = 0; $QElement < count($BuildArray); $QElement++) {
                $ElmentArray = explode(",", $BuildArray[$QElement]);
                if (isset($ElmentArray[502]) && $ElmentArray[502] != 0) {
                    $Missiles[502] += $ElmentArray[502];
                } elseif (isset($ElmentArray[503]) && $ElmentArray[503] != 0) {
                    $Missiles[503] += $ElmentArray[503];
                }
            }
            foreach ($fmenge as $Element => $Count) {
                $Element = intval($Element);
                $Count = intval($Count);
                if ($Count > MAX_FLEET_OR_DEFS_PER_ROW) {
                    $Count = MAX_FLEET_OR_DEFS_PER_ROW;
                }


                if ($Count != 0) {
                    $InQueue = strpos($CurrentPlanet['b_hangar_id'], $Element . ",");
                    $IsBuild = ($CurrentPlanet[$ge_resource[407]] >= 1) ? true : false;
                    if ($Element == 407 || $Element == 408) {
                        if ($InQueue === false && !$IsBuild) {
                            $Count = 1;
                        }
                    }

                    if (fs()->IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element)) {
                        $MaxElements = fs()->GetMaxConstructibleElements($Element, $CurrentPlanet);

                        if ($Element == 502 || $Element == 503) {
                            $ActuMissiles = $Missiles[502] + (2 * $Missiles[503]);
                            $MissilesSpace = $MaxMissiles - $ActuMissiles;
                            if ($Element == 502) {
                                if ($Count > $MissilesSpace) {
                                    $Count = $MissilesSpace;
                                }
                            } else {
                                if ($Count > floor($MissilesSpace / 2)) {
                                    $Count = floor($MissilesSpace / 2);
                                }
                            }
                            if ($Count > $MaxElements) {
                                $Count = $MaxElements;
                            }
                            $Missiles[$Element] += $Count;
                        } else {
                            if ($Count > $MaxElements) {
                                $Count = $MaxElements;
                            }
                        }

                        $Ressource = fs()->GetElementRessources($Element, $Count);
                        if ($Count >= 1) {
                            $CurrentPlanet['metal'] -= $Ressource['metal'];
                            $CurrentPlanet['crystal'] -= $Ressource['crystal'];
                            $CurrentPlanet['deuterium'] -= $Ressource['deuterium'];
                            $CurrentPlanet['b_hangar_id'] .= "" . $Element . "," . $Count . ";";
                        }
                    }
                }
            }
        }

        //building
        $building = $this->_fleeting_query($this->current_planet());

        //basic
        $basic = array();
        foreach ($lang['tech'] as $Element => $ElementName) {
            if ($Element > 400 && $Element <= 599) {
                if (fs()->IsTechnologieAccessible($CurrentUser, $CurrentPlanet, $Element)) {
                    $tp = array('fleet' => $Element, 'fleet_name' => $ElementName);
                    $tp['defense_can_one'] = fs()->IsElementBuyable($CurrentUser, $CurrentPlanet, $Element, false);
                    $tp['defense_time_one'] = fs()->GetBuildingTime($CurrentUser, $CurrentPlanet, $Element);
                    $tp['count'] = $CurrentPlanet[$ge_resource[$Element]];
                    $tp['defense_desc'] = $lang['res']['descriptions'][$Element];
                    $tp['defense_price'] = fs()->GetElementPrice($CurrentUser, $CurrentPlanet, $Element, false);
                    $basic[] = $tp;
                }
            }
        }

        $rtn = array('building' => $building, 'basic' => $basic);
        return $rtn;
    }

    private function _building_query($CurrentPlanet)
    {
        global $lang;

        $CurrentQueue = $CurrentPlanet['b_building_id'];
        if ($CurrentQueue != 0) {
            $QueueArray = explode(";", $CurrentQueue);
            $ActualCount = count($QueueArray);
        } else {
            $QueueArray = "0";
            $ActualCount = 0;
        }

        $ListIDRow = "";
        if ($ActualCount != 0) {
            $PlanetID = $CurrentPlanet['id'];
            $CurrentTime = floor(time());
            for ($QueueID = 0; $QueueID < $ActualCount; $QueueID++) {
                $BuildArray = explode(",", $QueueArray[$QueueID]);
                $BuildEndTime = floor($BuildArray[3]);
                if ($BuildEndTime >= $CurrentTime) {
                    $ListID = $QueueID + 1;
                    $Element = $BuildArray[0];
                    $BuildLevel = $BuildArray[1];
                    $BuildMode = $BuildArray[4];
                    $BuildTime = $BuildEndTime - time();
                    $ElementTitle = $lang['tech'][$Element];

                    $listItem = array(
                        'list_id' => $ListID,
                        'element' => $Element,
                        'build_level' => $BuildLevel,
                        'build_time' => $BuildTime,
                        'build_model' => $BuildMode,
                        'element_title' => $ElementTitle,
                    );
                    $ListIDRow[] = $listItem;
                }
            }
        }

        $RetValue['lenght'] = $ActualCount;
        $RetValue['buildlist'] = $ListIDRow;

        return $RetValue;
    }

    private function _fleeting_query($CurrentPlanet)
    {
        global $lang;

        $fleeting = array();
        $ElementQueue = explode(';', $CurrentPlanet['b_hangar_id']);
        foreach ($ElementQueue as $ElementLine => $Element) {
            if ($Element != '') {
                $Element = explode(',', $Element);
                $tp = array('fleet' => $Element[0], 'fleet_name' => $lang['tech'][$Element[0]]);
                $time_one = fs()->GetBuildingTime($this->g_user, $CurrentPlanet, $Element[0]);
                $num = $Element[1];
                $tp['fleet_time_one'] = $time_one;
                $tp['fleet_num'] = $num;
                $fleeting[] = $tp;
            }
        }

        return $fleeting;
    }

}

/* End of file */
