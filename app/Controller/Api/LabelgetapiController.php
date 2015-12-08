<?php

App::uses('APIController', 'Controller');

class LabelgetAPIController extends APIController {
  // ...
    
  public function process(){
    $this->loadModel('Label');
    $this->loadModel('LabelData');
      
    $targetr_id = $this->request->query('id');
    $type = $this->request->query('type');
    
    if( empty($targetr_id) ) {
      throw new Exception("Parametar error: target id", 1);
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
    $label_data = $this->Label->getLabelByTargetID( $this->session['team_id'], $type, $targetr_id );
    $this->result['label_data'] = $label_data;
  }
}

?>