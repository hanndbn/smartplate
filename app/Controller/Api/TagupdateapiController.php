<?php

App::uses('APIController', 'Controller');

class TagUpdateAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Tag');
    $this->loadModel('Bookmark');
    $this->loadModel('Label');
    $this->loadModel('LabelData');
    
    $tag_id = $this->request->query('id');
    $name = $this->request->query('name');
    $bookmark_id = $this->request->query('bookmark_id');
    $available = $this->request->query('available');
    $labels = $this->request->query('labels');
    $label_ids = $this->request->query('label_ids');
    
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
    if( is_array($label_ids) ){
      $this->Label->deleteLabelDataByTargetID($this->session['team_id'],Label::MODEL_TAG,$tag_id);
      foreach ($label_ids as $key => $label_id) {
        if( $label_id == -1 ){ break; }
        if( $this->Label->hasLabelByID( $this->session['team_id'], Label::MODEL_TAG, $label_id) ){
          $this->LabelData->InsertLabelData($label_id,$tag_id);
        }
      }
    }
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