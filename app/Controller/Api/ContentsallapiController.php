<?php

App::uses('APIController', 'Controller');

class ContentsAllAPIController extends APIController {
  // ...
    
  public function process(){
       
     if ($this->request->is('get')) 
      {
        date_default_timezone_set('UTC');
        
        $this->loadModel('AccessLog');
        $this->loadModel('Bookmark');

        $app = $this->request->query('a');
        $time_zone = $this->request->query('tz');
        $start = $this->request->query('start');
        $end = $this->request->query('end');
        

        if ($start) {
             $start = date('Y-m-d H:i:s', strtotime($start));  
        }else{
             $start = $this->AccessLog->getFirstContentsAccessByTeam($this->session['team_id'], $app);
        }
        if ($end)   {
            $end = date('Y-m-d H:i:s', strtotime($end));      
        } else{
            $end = date('Y-m-d H:i:s');
        }
        

        if( empty($time_zone) ){ $time_zone = 'UTC'; }
        $this->AccessLog->target_time_zone = $time_zone;
        $start_timezone = $this->AccessLog->convertUTC2TimeZone($time_zone, $start);
        $end_timezone = $this->AccessLog->convertUTC2TimeZone($time_zone, $end);
        
        

        $total_num     = $this->AccessLog->getContentsTap($this->session['team_id'], $time_zone, $start, $end, $app, null);
        $date_list_tmp = $this->AccessLog->getTotalTapByContentAndDate($this->session['team_id'], null, $start, $end, $app);
        $date_list = array();
        
        
        if( !empty($total_num['details'])){
 
          foreach ($total_num['details'] as $tmp)
          {
             if( $tmp['type'] == 'N' ){
               $n_num = $tmp['count'];
             }else if( $tmp['type'] == 'Q' ){
               $q_num = $tmp['count'];
             }
          }
        }else{
          $n_num = 0;
          $q_num = 0;
        }

        foreach ($date_list_tmp as $tmp)
        {
          $dat = $tmp[0];
          if(!empty($dat['_count']))
            $date_list[$dat['_date']] = $dat['_count'];
        }
        $i = 0;
        $date_data = array();
        while (true)
        {
            $date = date('Y-m-d', strtotime("+". $i ." days", strtotime($start_timezone)));

            if (strtotime($date) > strtotime($end_timezone))
            {
                break;
            }
            $date_data[] = array(
                'date'  => date('r', strtotime($date)),
                'count' => isset($date_list[$date]) ? $date_list[$date] : 0,
            );
            $i++;
        }

        $data = array();
        $data['total_count_nfc'] = intval($n_num);
        $data['total_count_qr']  = intval($q_num);
        $data['dates']           = $date_data;
        $data_list[] = $data;


        $this->result['analytics'] = array('content_data' => $data_list);
    }
  }

}

?>

