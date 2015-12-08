<?php

App::uses('APIController', 'Controller');
class TestAPIController extends APIController {
  // ...
  
  public function test(){
  	
    $this->autoRender = false;
    $this->loadModel('Tag');
    $this->loadModel('Label');
    $this->loadModel('LabelData');
    
    $res = $this->Tag->SelectByTeamWithLabels(1007,array('関東'));
    var_dump($res);
    
  }

}

?>