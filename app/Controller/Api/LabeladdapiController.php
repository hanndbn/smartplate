<?php

App::uses('APIController', 'Controller');

class LabeladdAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Label');
      
    $parent_id = $this->request->query('pid');
    $label_text = $this->request->query('label');
    $type = $this->request->query('type');
    
    if( empty($parent_id) ) {
      $parent_id = 0;
    }
    if( empty($label_text) ) {
      throw new Exception("Parametar error: label", 1);
    }
    if( empty($type) ) {
      throw new Exception("Parametar error: type", 2);
    }
    
    switch ($type) {
      case 1:
        $type = Label::MODEL_TAG;
        break;
      case 2:
        $type = Label::MODEL_BOOKMARK;
        break;
      default:
        throw new Exception("Parametar error: invalid type", 3);
        break;
    }
    $has_label = $this->Label->hasLabel( $this->session['team_id'], $parent_id, $type, $label_text );
    if( ! $has_label ){
      $new_id = $this->Label->InsertLabel($this->session['team_id'],$label_text,$type,0,$parent_id);
    }else{
        throw new Exception("registed label", 4);
    }
        
  }
}

?>