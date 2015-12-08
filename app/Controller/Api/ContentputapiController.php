<?php

App::uses('APIController', 'Controller');

class ContentputAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Bookmark');
    $this->loadModel('Tag');
    $this->loadModel('Label');
    $this->loadModel('LabelData');
      
    $bookmark_id = $this->request->query('id');
    $url = $this->request->query('url');
    $name = $this->request->query('name');
    $tags = $this->request->query('tags');
    $labels = $this->request->query('labels');
    $label_ids = $this->request->query('label_ids');
    
    if( empty($tags) ){
      $tags = array();
    }
    
    foreach ($tags as $key => $value) {
      $tag = $this->Tag->TagURLtoTag($value);
      
      $tag_data = $this->Tag->find( 'first', array( 'conditions' => array( 'tag' => $tag ),
                                            'fields' => array('id','team_id')));
        
      if( empty($tag_data['Tag']['id']) ){                              throw new Exception("invalid tag url", 1);  }  
      if( $tag_data['Tag']['team_id'] == 0 ){                           throw new Exception("deactivate plate", 3);   }  
      if( $this->session['team_id'] != $tag_data['Tag']['team_id'] ){   throw new Exception("different team", 2);   }  
      
    }

    if( empty($bookmark_id) ){
        $bookmark_id = $this->Bookmark->InsertBookmark( $this->session['team_id'], $this->session['user_id'], $name, $url );
    }
    
    if( !empty($labels) ){
      foreach ($labels as $key => $value) {
        $this->Label->InsertLabel($this->session['team_id'],$value,Label::MODEL_BOOKMARK,$bookmark_id);
      }
    }
    if( is_array($label_ids) ){
      $this->Label->deleteLabelDataByTargetID($this->session['team_id'],Label::MODEL_BOOKMARK,$bookmark_id);
      foreach ($label_ids as $key => $label_id) {
        if( $label_id == -1 ){ break; }
        if( $this->Label->hasLabelByID( $this->session['team_id'], Label::MODEL_BOOKMARK, $label_id) ){
          $this->LabelData->InsertLabelData($label_id,$bookmark_id);
        }
      }
    }
    
    foreach ($tags as $key => $value) {
      $tag = $this->Tag->TagURLtoTag($value);
      
      $tag_data = $this->Tag->find( 'first', array( 'conditions' => array( 'tag' => $tag ),
                                            'fields' => array('id','team_id')));
      
      $this->Tag->save( array( "id"=>$tag_data['Tag']['id'], "tag"=>$tag, "bookmark_id"=>$bookmark_id ));
    }
                  

  }
}

?>