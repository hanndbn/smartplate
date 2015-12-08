<?php

App::uses('APIController', 'Controller');

define('DYNAPICK_TEAM_ID', 100);
define('DYNAPICK_USER_ID', 3);

class PutbydynapickAPIController extends APIController {
  // ...
    
  public function beforeFilter() {
     parent::beforeFilter();
        $this->Auth->allow(array('controller' => 'putbydynapickapi', 'action' => 'index'));
  }   
  public function index(){

    $this->autoRender = false;
  
    try {
      $url = $this->request->query('url');
      $tag_url = $this->request->query('tagurl');
        
      if( empty($url) ) {
        throw new Exception("Parametar error", 4);
      }
      if( empty($tag_url) ) {
        throw new Exception("Parametar error", 5);
      }
      
      $this->loadModel('Tag');   
      $this->loadModel('Link');   
      
      $tag_id = $this->Tag->TagURLtoTag($tag_url);
      
      $tag_data = $this->Tag->find('first', array( 'conditions' => array( 'tag' => $tag_id ) ) );
      if( empty($tag_data) ){
        throw new Exception("tag error", 6);
      }
      $tag_data = $tag_data['Tag'];
      
      if( !empty($tag_data['team_id']) && $tag_data['team_id'] != DYNAPICK_TEAM_ID ){
        throw new Exception("other team tag", 7);
      }
      
      if( isset( $tag_data['team_id']) ) {
        $team_id = $tag_data['team_id'];
      }
      
      $this->Link->DeleteByTagID($tag_data['id']);
      
      $links_datas = array(   'tag_id'=>$tag_data['id'],
                  'url'=>$url, 
                  'bookmark_id'=>0, 
                  'type'=>0, 
                  'sub_type'=>0, 
                  'user_id'=>DYNAPICK_USER_ID,
                  'udate'=>date("Y-m-d H:i:s"),
                  'cdate'=>date("Y-m-d H:i:s") );
                  
      $this->Link->save( $links_datas );
  
      $tag_data['team_id'] = DYNAPICK_TEAM_ID;
      $tag_data['bookmark_id'] = 0;
      
      $this->Tag->save($tag_data,false);
      
      print '{"status":{"code":0}}';
    }catch(exception $e){
          
        $err_message = $e->getMessage();
        $err_code = $e->getCode();
        print '{"status":{"code":"'.$err_code.'","message":"'.$err_message.'"}}';
    }
  }
}

?>