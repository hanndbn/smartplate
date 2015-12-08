<?php

App::uses('AppController', 'Controller');

  define('TAILS_CODE', 16000);
class TailsContentsAPIController extends AppController {
  // ...
  public function beforeFilter() {
     parent::beforeFilter();
        $this->Auth->allow(array('controller' => 'tailscontentsapi', 'action' => 'index'));
  }   
  public function index(){

      $this->autoRender = false;
      
      try {
    $tag = $this->request->query('tag');
    if( empty($tag) ){
      throw new Exception("not found tag", 1);
    }
    
    
    $this->loadModel('Bookmark');
    $this->loadModel('Link');
    $this->loadModel('Tag');
    
    $tag_data = $this->Tag->find('first', array( 'conditions'=> array('tag' => $tag)) );
    if( empty($tag_data) ){
      throw new Exception("tag error", 2);
    }
    $tag_data = $tag_data['Tag'];
    if( $tag_data['bookmark_id'] == 0){
      $links = $this->Link->find('all', array( 'conditions'=> array('tag_id' => $tag_data['id'])) );
    }else{
      $links = $this->Link->find('all', array( 'conditions'=> array('bookmark_id' => $tag_data['bookmark_id'])) );
    }
    
    $datas = array();
    
    if( !empty($links) ){
      
      $is_left = TRUE;
      foreach ($links as $key => $link) {
        $link = $link['Link'];
        if( $is_left ){
          $row_data = array();
        }
        $row_data[] = array('url'=>$link['url'],'link_text'=>$link['link_text'],'icon'=>'http://smartplate.pro/icon/'.$link['icon'].'.png');
        
        if( $is_left ){
          $is_left = FALSE;
        }else{
          $datas[] = $row_data;
          $is_left = TRUE;
        }
      }
      if( !$is_left ){
        $datas[] = $row_data;
      }
      
    }
    $image = "http://smartplate.pro/icon/logo-smartplate.png";
    $data_json = json_encode($datas);
    
    print '{"status":{"code":0},"image":"'.$image.'","team":'.$tag_data['team_id'].',"tails":'.$data_json.'}';
    }catch(exception $e){
      $err_message = $e->getMessage();
      $err_code = $e->getCode();
      print '{"status":{"code":"'.$err_code.'","message":"'.$err_message.'"}}';
    }
  }
}

?>