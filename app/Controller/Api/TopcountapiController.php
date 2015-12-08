<?php

App::uses('APIController', 'Controller');

class TopCountAPIController extends APIController {
  // ...
    
  public function process(){
       
     if ($this->request->is('get')) 
      {
        $_tag = $this->request->query('tag');
        $timezone = $this->request->query('tz');
        if( empty($timezone) ){                      $timezone = 'UTC';   }   
        
        $app = $this->request->query['a'];
        
        $this->loadModel('AccessLog');

        $this->AccessLog->loadActivatePalteList($this->session['team_id']);
        $this->AccessLog->filterAppUserPalteList($this->session['user_id']);
        
        $total = $this->AccessLog->getTotalTap($this->session['team_id'], $app, $_tag);
        $daily = $this->AccessLog->getDailyTap($this->session['team_id'], $timezone, $app, $_tag);
        $weekly = $this->AccessLog->getWeeklyTap($this->session['team_id'], $timezone, $app, $_tag);
        $monthly = $this->AccessLog->getMonthlyTap($this->session['team_id'], $timezone, $app, $_tag);
    
    
        $this->result['analytics'] = array(
            'total'  => $total['count'],
            'total_details'  => $total['details'],
            'daily'  => $daily['count'],
            'daily_details'  => $daily['details'],
            'weekly'  => $weekly['count'],
            'weekly_details'  => $weekly['details'],
            'monthly'  => $monthly['count'],
            'monthly_details'  => $monthly['details'],
        );
      }
        
  }

}

?>

