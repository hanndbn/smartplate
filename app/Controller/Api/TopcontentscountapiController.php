<?php

App::uses('APIController', 'Controller');

class TopContentsCountAPIController extends APIController {
  // ...
    
  public function process(){
       
     if ($this->request->is('get')) 
      {
        $bookmark_id = $this->request->query('id');
        $timezone = $this->request->query('tz');
        if( empty($timezone) ){                      $timezone = 'UTC';   }   
        
        $app = $this->request->query['a'];
        
        $this->loadModel('AccessLog');

        $total = $this->AccessLog->getTotalContentsTap($this->session['team_id'], $app, $bookmark_id);
        $daily = $this->AccessLog->getDailyContentsTap($this->session['team_id'], $timezone, $app, $bookmark_id);
        $weekly = $this->AccessLog->getWeeklyContentsTap($this->session['team_id'], $timezone, $app, $bookmark_id);
        $monthly = $this->AccessLog->getMonthlyContentsTap($this->session['team_id'], $timezone, $app, $bookmark_id);
    
    
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

