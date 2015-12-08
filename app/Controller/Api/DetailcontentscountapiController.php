<?php

App::uses('APIController', 'Controller');

class DetailContentsCountAPIController extends APIController {
  // ...
    
  public function process(){
       
     if ($this->request->is('get')) 
      {
        date_default_timezone_set('UTC');

        $this->loadModel('AccessLog');
        $this->loadModel('Bookmark');
       
        $app = $this->request->query('a');
        $start = $this->request->query('start');
        $end = $this->request->query('end');
        $offset = $this->request->query('offset');
        $limit = $this->request->query('limit');
        $direction = $this->request->query('direction');
        
        if ($start)
        {
            $start = date('Y-m-d H:i:s', strtotime($start));
        }
        if ($end)
        {
            $end = date('Y-m-d H:i:s', strtotime($end));
        }

        $contents_data_list = array();
    
        $arg = array('offset'=>$offset,'limit'=>$limit,'direction'=>$direction);
    
        $this->AccessLog->loadActivatePalteList($this->session['team_id']);
        
        
        $total_palte_count = $this->AccessLog->getTotalContentsCountByTeam($this->session['team_id'], $start, $end, $app);
        $all_array = $this->AccessLog->getTotalContentsByTeamAndType($this->session['team_id'], "", $arg, $start, $end, $app);
        $nfc_arry = $this->AccessLog->getTotalContentsByTeamAndType($this->session['team_id'], "N", null, $start, $end, $app);
        $qr_arry = $this->AccessLog->getTotalContentsByTeamAndType($this->session['team_id'], "Q", null, $start, $end, $app);
        //$this->log( $this->AccessLog->getDataSource()->getLog(), LOG_DEBUG);
        $first_access_date = $this->AccessLog->getFirstPlateAccessByTeam($this->session['team_id'], $app);
        
        if( empty($first_access_date) ){
          $first_access_date = "";
        }else{
          $first_access_date = date('r', strtotime($first_access_date));
        }
    
        foreach ($all_array as $index => $row)
        {
              $contents_data = array();
              $contents_data['contents'] = $row['contents'];
              $contents_data['total'] = $row['total'];
              $contents_data['bookmark_id'] = $row['bookmark_id'];
              $contents_data['icon'] = $row['icon'];
              $contents_data['name'] = $row['name'];
              
              if( !empty($nfc_arry[$index] )) {
                $contents_data['nfc'] = $nfc_arry[$index]['N'];
              }else{
                $contents_data['nfc'] = 0;
              }
              if( !empty($qr_arry[$index] )) {
                $contents_data['qr'] = $qr_arry[$index]['Q'];
              }else{
                $contents_data['qr'] = 0;
              }
              if( $contents_data['nfc'] + $contents_data['qr'] != $contents_data['total']) {
                $contents_data['total'] = $contents_data['nfc'] + $contents_data['qr'];
              }
              $contents_data_list[] = $contents_data;
        }
        $this->result['analytics'] = array( 'total' => $total_palte_count,
                                            'first_access' => $first_access_date,
                                            'contents_data' => $contents_data_list);
    }
  }

}

?>

