<?php

App::uses('APIController', 'Controller');

class LinksAPIController extends APIController {
  // ...
    
  public function process(){
	
    $tag_url = $this->request->query('tag');
    if( empty($tag_url) ){
      throw new Exception("not found tag", 6);
    }
    $type = $this->request->query('type');
    if( empty($type) ){
      $type = 0;
    }
      // check for url without initiraize(type != 9)
    if( $type != 9 ){
      $urls = $this->request->query('url');
      if( empty($urls) ){
        throw new Exception("not found url", 6);
      }else if( !is_array($urls) && count($urls) <= 0) {
        throw new Exception("invalid url datas", 7);
      }
    }
    
    // check for tailes data
    if( $type == 2 ){
      $link_texts = $this->request->query('txt');
      if( empty($link_texts) ){
        throw new Exception("not found link text datas", 12);
      }else if( !is_array($link_texts) ) {
        throw new Exception("invalid link text datas", 13);
      }
      
      $icons = $this->request->query('icon');
      if( empty($icons) ){
        throw new Exception("not found icon datas", 14);
      }else if( !is_array($icons) && count($icons) <= 0) {
        throw new Exception("invalid icon datas", 15);
      }
      
      if( count($urls) != count($link_texts) ||  count($urls) != count($icons) ){
        throw new Exception("The number of data of URL and ICON and LinkText is different.", 16);
      }
    }
    
    $this->loadModel('Tag');   
    $this->loadModel('Link');   
      
    $tag_id = $this->Tag->TagURLtoTag($tag_url);
    
    $tag_data = $this->Tag->find('first', array( 'conditions' => array( 'tag' => $tag_id ) ) );
    if( empty($tag_data) ){
      throw new Exception("tag error", 8);
    }
    $tag_data = $tag_data['Tag'];
    
    // Check Team ID
    if(  empty( $tag_data['team_id']) ) {
      throw new Exception("not activation", 10);
    }
    // Check Team ID
    if(  $tag_data['team_id'] != $this->session['team_id']) {
      throw new Exception("other team plate", 11);
    }
    
    $this->Link->DeleteByTagID($tag_data['id']);
    
    if( !empty($tag_data['bookmark_id']) ){
      $tag_data['bookmark_id'] = 0;
      $this->Tag->save($tag_data,false);
    }
    
    //date_default_timezone_set('UTC');
    
    if( $type != 9 ){
      foreach ($urls as $sub_type => $url) {
        
        $links_datas = array(   'tag_id'=>$tag_data['id'],
                    'url'=>$url, 
                    'bookmark_id'=>0, 
                    'type'=>$type, 
                    'sub_type'=>$sub_type, 
                    'user_id'=>$this->session['user_id'],
                    'udate'=>date("Y-m-d H:i:s"),
                    'cdate'=>date("Y-m-d H:i:s") );
                    
        if( $type == 2 ){
          $links_datas['icon'] = $icons[$sub_type];
          $links_datas['link_text'] = $link_texts[$sub_type];
        }   
        $this->Link->save( $links_datas );
      }
    }
  }
}

?>