<?php

App::uses('APIController', 'Controller');

class TagGetAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Tag');
    
    $tags = $this->request->query('tags');
    $ids = $this->request->query('ids');
    $labels = $this->request->query('labels');
    $offset = $this->request->query('offset');
    $limit = $this->request->query('limit');
    $order = $this->request->query('order');
    $direction = $this->request->query('direction');
    
    $arg = array('offset'=>$offset,'limit'=>$limit,'order'=>$order,'direction'=>$direction);
    
    if ( !empty($ids) ) {
        $tag_data = $this->Tag->SelectByIDs( $this->session['team_id'],$ids,$arg,$total);
    }else if ( !empty($tags) ) {
      $tag_data =  $this->Tag->SelectByPartialMatchTags($tags,$this->session['team_id'],$arg,$total);
    }
    else {
      if ( !empty($labels) ) {
        $tag_data = $this->Tag->SelectByTeamWithLabels( $this->session['team_id'], $labels,$arg,$total);
      }else{
        $tag_data = $this->Tag->SelectByTeams( $this->session['team_id'],$arg,$total);
      }
    }
    $this->result['tags'] = $tag_data;
    $this->result['total_number'] = $total;
    
    

  }
}

?>