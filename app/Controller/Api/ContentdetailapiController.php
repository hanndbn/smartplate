<?php

App::uses('APIController', 'Controller');
App::uses('Bookmark', 'Model');

class ContentDetailAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Bookmark');
    $this->loadModel('Link');
    $this->loadModel('Tag');
    
    $bookmark_id = $this->request->query('id');
    
    // base bookmark data
    $bookmark = $this->Bookmark->find( 'first', array( 'conditions' => array( 'Bookmark.id' => $bookmark_id ),
                                            'fields' => array('id','team_id','name','kind','code','image','cdate')));
    if ( empty($bookmark) ){
      throw new Exception("not regist contents", 1);
    }                                 
    // links data
    $Links_res = $this->Link->find( 'all', array( 'conditions' => array( 'bookmark_id' => $bookmark_id ),
                                            'fields' => array('type','sub_type','url','icon','link_text')));
     
    $bookmark['Bookmark']['cdate'] = $this->Bookmark->convertDateTokyo2UTC($bookmark['Bookmark']['cdate']);
                 
    
    $type = 0;
    $Links = array();                                   
    foreach ($Links_res as $key => $value) {
      $type = $value['Link']['type'];
      $icon = $value['Link']['icon'];
      if( !empty($icon) ){
        $value['Link']['icon'] = Router::fullbaseUrl() . DS . 'img/icon/' . $icon . '.png';
      }
      unset($value['Link']['type']);
      $Links[] = $value['Link'];
    }       
    $bookmark['Bookmark']['type'] = $type; 
 /*   
    if( empty($bookmark['Bookmark']['image'] ) ){
      $icon_dir = 'http://'.$_SERVER["HTTP_HOST"].'/img/icon/';
      switch ($type) {
        case Bookmark::TYPE_OS :
          $bookmark['Bookmark']['image'] = $icon_dir.Bookmark::ICON_OS;
          break;
        case Bookmark::TYPE_TAILS :
          $bookmark['Bookmark']['image'] = $icon_dir.Bookmark::ICON_TAILS;
          break;
        case Bookmark::TYPE_RANDOM :
          $bookmark['Bookmark']['image'] = $icon_dir.Bookmark::ICON_RANDOM;
          break;
        case Bookmark::TYPE_ROTATE :
          $bookmark['Bookmark']['image'] = $icon_dir.Bookmark::ICON_ROTATE;
          break;
      }
    }
     */
    // tag data                                     
    $Tags_res = $this->Tag->find( 'all', array( 'conditions' => array( 'bookmark_id' => $bookmark_id ),
                                            'fields' => array('id') ));
    
    
    $Tags = array();                                   
    foreach ($Tags_res as $key => $value) {
      $Tags[] = $value['Tag']['id'];
    }       

    if( !empty($bookmark['Bookmark']['image'])  ){
      //$bookmark['Bookmark']['image'] = Router::fullbaseUrl() . DS . 'upload' . DS . 'bookmark' . DS . $bookmark['Bookmark']['image'];
      $bookmark['Bookmark']['image']  = Bookmark::imageURL($bookmark['Bookmark']['image']);
    }
    
    $this->result['content'] = $bookmark['Bookmark'];
    $this->result['content']['links'] = $Links;
    $this->result['content']['tag_ids'] = $Tags;
    
    

  }
}

?>