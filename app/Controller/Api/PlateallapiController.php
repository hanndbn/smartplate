<?php

App::uses('APIController', 'Controller');

class PlateAllAPIController extends APIController {
  // ...
    
  public function process(){
       
     if ($this->request->is('get')) 
      {
        date_default_timezone_set('UTC');
        
        $this->loadModel('AccessLog');
        $this->loadModel('Tag');

        $app = $this->request->query('a');
        $time_zone = $this->request->query('tz');
        $start = $this->request->query('start');
        $end = $this->request->query('end');


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

        if( empty($time_zone) ){ $time_zone = 'UTC'; }
        $this->AccessLog->target_time_zone = $time_zone;
        $start_timezone = $this->AccessLog->convertUTC2TimeZone($time_zone, $start);
        $end_timezone = $this->AccessLog->convertUTC2TimeZone($time_zone, $end);
        

            $this->AccessLog->loadActivatePalteList($this->session['team_id']);
            $this->AccessLog->filterAppUserPalteList($this->session['user_id']);
            
            $n_num     = $this->AccessLog->getTotalTapByTagAndType($this->session['team_id'], null, 'N', $start, $end, $app);
            $q_num     = $this->AccessLog->getTotalTapByTagAndType($this->session['team_id'], null, 'Q', $start, $end, $app);
            $date_list_tmp = $this->AccessLog->getTotalTapByTagAndDate($this->session['team_id'], null, $start, $end, $app);
            
            $date_list = array();

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

            $tag_data = array();
            $tag_data['total_count_nfc'] = $n_num;
            $tag_data['total_count_qr']  = $q_num;
            $tag_data['dates']           = $date_data;
            $tag_data_list[] = $tag_data;


        $this->result['analytics'] = array('tag_data' => $tag_data_list,'tag_data' => $tag_data_list);
    }
  }

}

?>

