<?php

App::uses('APIController', 'Controller');
App::uses('Tag', 'Model');
App::uses('User', 'Model');

class TagListAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Tag');
    $this->loadModel('Label');
    
    $label = $this->request->query('label');
    $keyword = $this->request->query('keyword');
    $offset = $this->request->query('offset');
    $limit = $this->request->query('limit');
    $order = $this->request->query('order');
    $direction = $this->request->query('direction');
    
    // offset != 0の場合Labelは返さない
    
    $arg = array('offset'=>$offset,'limit'=>$limit,'order'=>$order,'direction'=>$direction);
    
    $userModel = new User();
    $value = $userModel->find( 'first', array( 'conditions'  => array( 'id'  => $this->session['user_id'] ),
                                          'fields'      => array( 'power' )
                            ));
    if( !empty($value['User']['power'] ) && $value['User']['power'] >= 100 ){
        $this->Tag->app_user_id = $this->session['user_id'];
    }
    
    if( isset($label) && isset($keyword) ){
      $tag_data = $this->Tag->SelectByTeams($this->session['team_id'],$arg,$total);
    }else{
      if ( empty($keyword) ) {
        if( empty($label) ) $label=0;
        $tag_data = $this->Tag->SelectByTeamWithLabel( $this->session['team_id'], $label,$arg,$total);
      
        if( empty($offset) ){
          $labels = $this->Label->getLabelsArrayByTeam($this->session['team_id'], $label, Label::MODEL_TAG );
          $this->result['labels'] = $labels;
        }
      }else{
        $tag_data = $this->Tag->SelectByTeamWithKeyword( $this->session['team_id'],$keyword,$arg,$total);
      }
    }
    
    if( $this->request->query['i'] == 'webapi' ){
        if( !empty($tag_data) ){
            $tmp = array();
            foreach ($tag_data as $key => $value) {
                $tmp[] = array( 'plate_id'=>$value['tag'],'name'=>$value['name'],'contents'=>$value['bookmark_id']);
            }
            $tag_data = $tmp;
        }
    }
    $this->result['tags'] = $tag_data;
    $this->result['total'] = $total;
    
    

  }
}

?>