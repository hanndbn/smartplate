<?php

App::uses('APIController', 'Controller');
App::uses('Bookmark', 'Model');
App::uses('Label', 'Model');

class ContentListAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Bookmark');
    $this->loadModel('Label');
    
    $label = $this->request->query('label');
    $keyword = $this->request->query('keyword');
    $offset = $this->request->query('offset');
    $limit = $this->request->query('limit');
    $order = $this->request->query('order');
    $direction = $this->request->query('direction');
    
    // offset != 0の場合Labelは返さない
    
    $arg = array('offset'=>$offset,'limit'=>$limit,'order'=>$order,'direction'=>$direction);

    if( isset($label) && isset($keyword) ){
      $data = $this->Bookmark->SelectByTeams($this->session['team_id'],$arg,$total);
    }else{
      if ( empty($keyword) ) {
        if( empty($label) ) $label=0;
        $data = $this->Bookmark->SelectByTeamWithLabel( $this->session['team_id'], $label,$arg,$total);
        
        if( empty($offset) ){
          $labels = $this->Label->getLabelsArrayByTeam($this->session['team_id'], $label, Label::MODEL_BOOKMARK );
          $this->result['labels'] = $labels;
        }
      }else{
        $data = $this->Bookmark->SelectByTeamWithKeyword( $this->session['team_id'],$keyword,$arg,$total);
      }
    }
    
    if( empty($total) ) { $total = 0; }
    
    $this->result['contents'] = $data;
    $this->result['total_number'] = $total;
    
    

  }
}

?>