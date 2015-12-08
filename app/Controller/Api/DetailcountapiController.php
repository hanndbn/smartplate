<?php

App::uses('APIController', 'Controller');

class DetailCountAPIController extends APIController {
  // ...
    
  public function process(){
       
     if ($this->request->is('get')) 
      {
        date_default_timezone_set('UTC');
        
        $this->loadModel('AccessLog');

        $app = $this->request->query('a');
        $_tag = $this->request->query('tag');
        $start = $this->request->query('start');
        $end = $this->request->query('end');
        $offset = $this->request->query('offset');
        $limit = $this->request->query('limit');
        $direction = $this->request->query('direction');

        if ($start) {   $start = date('Y-m-d H:i:s', strtotime($start));  }
        if ($end)   {   $end = date('Y-m-d H:i:s', strtotime($end));      }

    
        $arg = array('offset'=>$offset,'limit'=>$limit,'direction'=>$direction);
        
        $this->AccessLog->loadActivatePalteList($this->session['team_id']);
        $this->AccessLog->filterAppUserPalteList($this->session['user_id']);
        
        $total_palte_count = $this->AccessLog->getTotalPlateCountByTeam($this->session['team_id'], $start, $end, $app);
        $all_array = $this->AccessLog->getTotalTapByTeamAndType($this->session['team_id'], "", $arg, $start, $end, $app);
        $nfc_arry = $this->AccessLog->getTotalTapByTeamAndType($this->session['team_id'], "N", null, $start, $end, $app);
        $qr_arry = $this->AccessLog->getTotalTapByTeamAndType($this->session['team_id'], "Q", null, $start, $end, $app);
        
        $first_access_date = $this->AccessLog->getFirstPlateAccessByTeam($this->session['team_id'], $app);
        
        if( empty($first_access_date) ){
          $first_access_date = "";
        }else{
          $first_access_date = date('r', strtotime($first_access_date));
        }
    
        $tag_data_list = array();
        foreach ($all_array as $tag => $row)
        {
            $tag_data = array();
            $tag_data['plate'] = $tag;
            $tag_data['total'] = $row['total'];
            $tag_data['icon'] = $row['icon'];
            $tag_data['name'] = $row['name'];
            if( !empty($nfc_arry[$tag] )) {
              $tag_data['nfc'] = $nfc_arry[$tag]['N'];
            }else{
              $tag_data['nfc'] = 0;
            }
            if( !empty($qr_arry[$tag] )) {
              $tag_data['qr'] = $qr_arry[$tag]['Q'];
            }else{
              $tag_data['qr'] = 0;
            }
            $tag_data_list[] = $tag_data;
        }
        $this->result['analytics'] = array( 'total' => $total_palte_count,
                                            'first_access' => $first_access_date,
                                            'tag_data' => $tag_data_list);
    }
  }

}

?>

