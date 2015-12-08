<?php

App::uses('APIController', 'Controller');

class UniqueUserAPIController extends APIController {
  // ...
    
  public function process(){
       
     if ($this->request->is('get')) 
      {
        date_default_timezone_set('UTC');
        
        $this->loadModel('AccessLog');
 
        $app = $this->request->query('a');
        $type = $this->request->query('type');
        $time_zone = $this->request->query('tz');
        $start = $this->request->query('start');
        $end = $this->request->query('end');
        $target = $this->request->query('tag');
        if( empty($target) ){
          $target = $this->request->query('content_id');
        }
        
        if( empty($type) ){ $type = 0; }
        
        if( $time_zone ){ $time_zone = 'UTC'; }
        if ($start) {
             $start = date('Y-m-d H:i:s', strtotime($start));  
        }else{
             $start = $this->AccessLog->getFirstPlateAccessByTeam($this->session['team_id'], $app);
        }
        if ($end)   {
            $end = date('Y-m-d H:i:s', strtotime($end));      
        } else{
            $end = date('Y-m-d H:i:s');
        }
        
        $team_ids = array( $this->session['team_id'] );
        $this->AccessLog->loadActivatePalteList($this->session['team_id']);
        $this->AccessLog->filterAppUserPalteList($this->session['user_id']);
            
        $unique_user  = $this->AccessLog->countUniqueUser($team_ids, $type, $target, $time_zone, $start, $end, $app );
        $repeat_user  = $this->AccessLog->countRepeatUser($team_ids, $type, $target, $time_zone, $start, $end, $app );
        
        if( empty($unique_user)){ $unique_user = 0; }
        if( empty($repeat_user)){ $repeat_user = 0; }
        $new_user = $unique_user - $repeat_user;
        
        $this->result['analytics'] = array('unique_user' => $unique_user, 'new_user' => $new_user, 'repeat_user' => $repeat_user);
    }
  }

}

?>

