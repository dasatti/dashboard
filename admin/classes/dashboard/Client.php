<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Client
 *
 * @author danish
 */


require_once dirname(__FILE__).'/DashboardCommon.php';


class Client extends DashboardCommon{
    
    public function __construct() {
        parent::__construct();
    }
    
    public static function get_campaigns(){
        $sql = "SELECT * FROM campaigns WHERE client_id='".  DashboardCommon::get_client_id()."'";
        $r = DashboardCommon::db()->Execute($sql);
        if(!$r) return array();
        $campaigns = array();
        foreach ($r as $campaign){
            $campaigns[] = $campaign;
        }
        return $campaigns;
    }
    
    
    public static function getFirstCampaignStartDate(){
        $sql = "SELECT MIN(start_date) as start_date FROM campaigns
            WHERE client_id='".  DashboardCommon::get_client_id()."' AND start_date!='0000-00-00'";
        $r = DashboardCommon::db()->Execute($sql);
        return $r->fields['start_date'];
    }
}

?>
