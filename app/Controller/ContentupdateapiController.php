<?php

App::uses('APIController', 'Controller');

class ContentUpdateAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Bookmark');
    $this->loadModel('Link');
    $this->loadModel('Label');
    $this->loadModel('LabelData');
      
    $bookmark_id = $this->request->query('id');
    $name = $this->request->query('name');
    $labels = $this->request->query('labels');
    $type = $this->request->query('type');

      if( empty($bookmark_id) ){                                        throw new Exception("not fount contents id", 1);  }  
      if( !isset($type) ){                                        throw new Exception("not fount type", 2);  }  
      
    $urls = $this->request->query('url');
    if( empty($urls) ){
      throw new Exception("not found url", 3);
    }else if( !is_array($urls) && count($urls) <= 0) {
      throw new Exception("invalid url datas", 4);
    }
    
    // check for tailes data
    if( $type == 2 ){
      $link_texts = $this->request->query('txt');
      if( empty($link_texts) ){
        throw new Exception("not found link text datas", 5);
      }else if( !is_array($link_texts) ) {
        throw new Exception("invalid link text datas", 6);
      }
      
      $icons = $this->request->query('icon');
      if( empty($icons) ){
        throw new Exception("not found icon datas", 7);
      }else if( !is_array($icons) && count($icons) <= 0) {
        throw new Exception("invalid icon datas", 8);
      }
      
      if( count($urls) != count($link_texts) ||  count($urls) != count($icons) ){
        throw new Exception("The number of data of URL and ICON and LinkText is different.", 9);
      }
    }

      
      $bookmark_data = $this->Bookmark->find( 'first', array( 'conditions' => array( 'id' => $bookmark_id ),
                                              'fields' => array('id','team_id')));
                                            
      if( empty($bookmark_data)){   throw new Exception("not regist contents", 10);   }  
      if( $this->session['team_id'] != $bookmark_data['Bookmark']['team_id'] ){   throw new Exception("different team", 11);   }  
      

      $bookmark_id = $this->Bookmark->UpdateBookmark( $bookmark_id,$this->session['team_id'], $this->session['user_id'], $name, $urls, $type );
    
    if( !empty($labels) ){
      foreach ($labels as $key => $value) {
        $this->Label->InsertLabel($this->session['team_id'],$value,Label::MODEL_BOOKMARK,$bookmark_id);
      }
    }             

  }
}

?>