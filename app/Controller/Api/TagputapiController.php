<?php

App::uses('APIController', 'Controller');

class TagPutAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Tag');
    $this->loadModel('Bookmark');
    
    $tag_id = $this->request->query('id');
    $tag = $this->request->query('tag');
    $name = $this->request->query('name');
    $bookmark_id = $this->request->query('bookmark_id');
    $available = $this->request->query('available');
    $labels = $this->request->query('labels');
    
    $datum = $this->request->query('datum');
    $latitude = $this->request->query('latitude');
    $longtitude = $this->request->query('longtitude');
    
    $offset = $this->request->query('offset');
    $limit = $this->request->query('limit');
    $order = $this->request->query('order');
    $direction = $this->request->query('direction');
    
    $arg = array('offset'=>$offset,'limit'=>$limit,'order'=>$order,'direction'=>$direction);
    
    
    $tag_data = $this->Tag->find( 'first', array( 'conditions'  => array( 'id' => $tag_id ),
                                                  'fields'      => array( 'id','team_id' )
                                ));
      
    if( empty($tag_data['Tag']['id']) ){                              throw new Exception("invalid tag id",  1);  }  
    if( $this->session['team_id'] != $tag_data['Tag']['team_id'] ){   throw new Exception("different team (plate)",   2);  }  
    
    if( !empty($bookmark_id) ) {
      $bookmark_data = $this->Bookmark->find( 'first', array( 'conditions'  => array( 'id' => $bookmark_id ),
                                                          'fields'      => array( 'id','team_id' )
                                  ));
      if( empty($bookmark_data['Bookmark']['id']) ){                              throw new Exception("invalid bookmark id",  3);  }  
      if( $this->session['team_id'] != $bookmark_data['Bookmark']['team_id'] ){   throw new Exception("different team (content)",   4);  }  
    }
    /*
    if( !empty($labels) ){
      foreach ($labels as $key => $value) {
        $this->Label->InsertLabel($this->session['team_id'],$value,Label::MODEL_TAG,$tag_id);
      }
    }
    */
   
    $data = array( "id"=>$tag_id );
    if( isset($name) ){
      $data['name'] = $name;
    }
    if( isset($bookmark_id) ){
      $data['bookmark_id'] = $bookmark_id;
    }
    if( isset($available) ){
      $data['available'] = $available;
    }
    $this->Tag->save( $data );
    
  }
}

?>