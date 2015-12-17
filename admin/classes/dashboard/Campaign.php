<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Campaigns
 *
 * @author danish
 */


require_once dirname(__FILE__).'/DashboardCommon.php';


class Campaign extends DashboardCommon{
    //put your code here
    
    private $table = 'campaigns';
    
    private $campaign_id;
    
    private $campaign_name;
    
    private $gsm_number;
    
    private $unbounce_id;
    
    private $client_id;
    
    private $ga_view_id;
    
    private $start_date;
    
    private $end_date;
    
    private $status;
    
    
    private $error = array();
    
    public function __construct() {
        parent::__construct();
    }
    
    public function add($campaign_name,$gsm_number,$unbouce_id,$client_id,$ga_view_id,$start_date,$end_date){
        
        $this->campaign_name = $campaign_name;
        $this->gsm_number = $gsm_number;
        $this->unbounce_id = $unbouce_id;
        $this->client_id= (int)$client_id;
        $this->ga_view_id = $ga_view_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        
        if($this->validate()){
            $sql = "INSERT INTO $this->table (campaign_name,gsm_number,unbounce_id,client_id,ga_view_id,
                start_date,end_date)
                VALUES('$this->campaign_name','$this->gsm_number','$this->unbounce_id','$this->client_id','$this->ga_view_id','$this->start_date','$this->end_date')";
            $r = DashboardCommon::db()->Execute($sql);
            if($r) return true;
            else{
                return false;
            }
        }
        return false;
    }
    
    public function edit($campaign_id,$campaign_name,$gsm_number,$unbouce_id,$client_id,$ga_view_id,
            $start_date,$end_date){
        
        $this->campaign_id = (int)$campaign_id;
        $this->campaign_name = $campaign_name;
        $this->gsm_number = $gsm_number;
        $this->unbounce_id = $unbouce_id;
        $this->client_id= (int)$client_id;
        $this->ga_view_id = $ga_view_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        
        if($this->validate()){
            $sql = "UPDATE $this->table SET campaign_name='$this->campaign_name', gsm_number = '$this->gsm_number',
                unbounce_id='$this->unbounce_id',client_id='$this->client_id',ga_view_id='$this->ga_view_id',
                start_date='$this->start_date',end_date='$this->end_date'
                WHERE id=$campaign_id";
            $r = DashboardCommon::db()->Execute($sql);
            if($r) return true;
            else{
                return false;
            }
        }
        return false;
    }
    
    public function validate(){
        $valid = true;
        if(trim($this->campaign_name)==''){
            $this->error[] = 'Campaign name not provided';$valid = false;
        }
        
        if(trim($this->gsm_number)==''){
            $this->error[] = 'GSM numeber is required';$valid = false;
        } 
        if(trim($this->unbounce_id)==''){
            $this->error[] = 'Unbounce ID is required';$valid = false;
        } 
        if(!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',
                trim($this->unbounce_id))){
            $this->error[] = 'Invalid unbounce id (correct format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)';
            $valid = false;
        }
        
        if(trim($this->client_id)!=''){
            if(!is_numeric($this->client_id)){
                $this->error[] = 'Invalid client ID';$valid = false;
            }
        }
        
        return $valid;
    }
    
    
    public static function get_clients_list(){
        $cl = DashboardCommon::get_clients();
        //print_r($cl);
        $clients_list = array();
        
        foreach ($cl as $c){
            $clients_list[] = array('id'=>$c['id'],'name'=>$c['name']);
        }
        
        return $clients_list;
    }
    
    public static function get_gsm_numbers(){
        $sql = "SELECT * FROM phone_numbers";
        $r = DashboardCommon::db()->Execute($sql);
        if(!$r) return array();
        $gsm_numbers = array();
        foreach ($r as $gsm){
            $gsm_numbers[] = $gsm;
        }
        return $gsm_numbers;
    }


    public function get_campaigns(){
        $sql = "SELECT campaigns.*,tbl_admin.name FROM campaigns LEFT JOIN tbl_admin
            ON campaigns.client_id=tbl_admin.id";
        $r = DashboardCommon::db()->Execute($sql);
        if(!$r) return array();
        $campaigns = array();
        foreach($r as $c){
            $campaigns[] = $c;
        }
        
        return $campaigns;
    }
    
    public function get_campaign($id){
        $id = (int)$id;
        $sql = "SELECT campaigns.*,tbl_admin.name FROM campaigns LEFT JOIN tbl_admin
            ON campaigns.client_id=tbl_admin.id WHERE campaigns.id=$id";
        $r = DashboardCommon::db()->getRow($sql);
        return $r;
        if(!$r) return array();
        $campaigns = array();
        foreach($r as $c){
            $campaigns[] = $c;
        }
        
        return $campaigns;
    }


    public function delete($id){
        $id = (int)$id;
        $sql = "DELETE FROM campaigns WHERE id=$id";
        return DashboardCommon::db()->Execute($sql);
    }

    public function get_error(){
        return $this->error;
    }
}

?>
