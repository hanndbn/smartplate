<?php

App::uses('APIController', 'Controller');

class ActivateDateAPIController extends APIController {
  // ...
    
  public function process(){
    
    $data_dir = TMP . 'data/smart_plate/save_datas/';
    
    if( empty($this->request->query['tag']) ){
      throw new Exception("not found tag", 6);
    }
    $tag = $this->request->query('tag');
    
    $uuid = $this->request->query['i'];
    $local = $this->request->query['l'];
    $app = $this->request->query['a'];
    
    $this->loadModel('Tag');
    $this->loadModel('TagHistory');
    
    $tag_data = $this->Tag->find( 'first',
                                  array(  'conditions' => array( 'tag' => $tag ),
                                          'fields'     => array( 'id' )) );
    if( empty($tag_data) ){
      throw new Exception("invalid tag", 7);
    }
    $tag_history = $this->TagHistory->GetFirstActivateDateByTagID($tag_data['Tag']['id']);

    if( !empty($tag_history['cdate']) ){
      $activate_date = $tag_history['cdate'];
      $timezone = new DateTimeZone('Asia/Tokyo');
      $date = new DateTime($activate_date,$timezone);
      $date->setTimeZone(new DateTimeZone('UTC'));
      $date_str = $date->format("r");
    }else{
      $date_str = "";
    }
    
    if( !empty($tag_history['limit_date']) ){
      $limit_date = $tag_history['limit_date'];
      $timezone = new DateTimeZone('UTC');
      $date = new DateTime($limit_date,$timezone);
      $limit_str = $date->format("r");
    }else{
      $limit_str = "";
    }
    
    $this->result['data'] = array('activate_date' => $date_str,'limit_date' => $limit_str);
  }
}

?>