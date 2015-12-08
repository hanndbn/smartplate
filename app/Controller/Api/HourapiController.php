<?php

App::uses('APIController', 'Controller');

class HourAPIController extends APIController {
  // ...
    
  public function process(){
       
     if ($this->request->is('get')) 
      {
        date_default_timezone_set('UTC');
        
        $this->loadModel('AccessLog');

        $app = $this->request->query('a');
        $type = $this->request->query('type');
        $_tag = $this->request->query('tag');
        $_cid = $this->request->query('content_id');
        $start = $this->request->query('start');
        $end = $this->request->query('end');

        if ($start) {   $start = date('Y-m-d H:i:s', strtotime($start));
                        $start_hour = date('H', strtotime($start));
         }
        if ($end)   {   $end = date('Y-m-d H:i:s', strtotime($end));
                        $end_hour = date('H', strtotime($end));
         }
        if( $type ){
            $time_list_q = $this->AccessLog->getTotalTapByContentsAndTime($this->session['team_id'], $_cid, "Q", $start, $end, $app);
            $time_list_n = $this->AccessLog->getTotalTapByContentsAndTime($this->session['team_id'], $_cid, "N", $start, $end, $app);
        }else{
            $this->AccessLog->loadActivatePalteList($this->session['team_id']);
            $this->AccessLog->filterAppUserPalteList($this->session['user_id']);
            $time_list_q = $this->AccessLog->getTotalTapByTagAndTime($this->session['team_id'], $_tag, "Q", $start, $end, $app);
            $time_list_n = $this->AccessLog->getTotalTapByTagAndTime($this->session['team_id'], $_tag, "N", $start, $end, $app);
        }
    
        $time_list = array();
        
        for( $i=0; $i<24; $i++ ){
          $time_index = $i+$start_hour;
          if( $time_index > 23)
            $time_index -= 24;
          $time_list["_$time_index"] = array('nfc'=>0,'qr'=>0);
        }
        
        $total_qr = 0;
        $total_nfc = 0;
        
        foreach ($time_list_q as $ind => $tmp)
        {
          $dat = $tmp[0];
          if(!empty($dat['_count'])){
            $time_index = '_'.$dat['_time'];
            $time_list[$time_index]['qr'] = intval($dat['_count']);
            $total_qr += $time_list[$time_index]['qr'];
          }
        }
        foreach ($time_list_n as $ind => $tmp)
        {
          $dat = $tmp[0];
          if(!empty($dat['_count'])){
            $time_index = '_'.$dat['_time'];
            $time_list[$time_index]['nfc'] = intval($dat['_count']);
            $total_nfc += $time_list[$time_index]['nfc'];
          }
        }

        $this->result['total'] = $total_nfc + $total_qr;
        $this->result['total_count_nfc'] = $total_nfc;
        $this->result['total_count_qr']  = $total_qr;
        $this->result['hour_data'] = $time_list;
    }
  }

}

?>

