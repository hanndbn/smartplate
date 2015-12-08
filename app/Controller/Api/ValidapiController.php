<?php

App::uses('APIController', 'Controller');

class ValidAPIController extends APIController {
  // ...
    
  public function process(){
    if( empty($this->request->query['valid']) ){
      throw new Exception("not found tag url", 5);
    }
    if( empty($this->request->query['tagurl']) ){
      throw new Exception("not found valid", 6);
    }
    
    $uuid = $this->request->query['i'];
    $local = $this->request->query['l'];
    $app = $this->request->query['a'];
    $tag_url  = $this->request->query['tagurl'];
    $valid   = $this->request->query['valid'];
    
    $tag_url_data = explode('/', $tag_url);
    
    $tag_id_base = $tag_url_data[count($tag_url_data)-1];
    
    $tag_hrad = substr($tag_id_base, 0,2);
    $tag_lot = substr($tag_id_base, 2,5);
    $tag_num = substr($tag_id_base, 8);
    
    $tag_id = "$tag_hrad.$tag_lot.$tag_num";
    
    $this->loadModel('Tag');
        
    $res = $this->Tag->find( 'first', array(  'conditions' => array( 'tag' => $tag_id )) );
    
    // Check Registed tag
    if( empty($res) ){
      throw new Exception("not found tag", 7);
    }
    $tag_data = $res['Tag'];
    
    // Check Team ID
    if(  !empty( $tag_data['team_id']) && $tag_data['team_id'] != $this->session['team_id']) {
      //print "$tag_data->team_id != $session_data->team_id";
      throw new Exception("different team", 9);
    }
    $tag_data['available'] = $valid;
    
    $this->Tag->save($tag_data,false);


  }
}

?>