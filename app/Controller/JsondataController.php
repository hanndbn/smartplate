<?php

App::uses('AppController', 'Controller');

class JsonDataController extends AppController {
  // ...
    
  public function functions(){
      $this->autoRender = false;
    
    $file_name = TMP . 'data/smart_plate/functions.json';

    echo mb_convert_encoding(file_get_contents($file_name), "utf8", "auto");
  }
  
  public function terms(){
      $this->autoRender = false;
    
    $file_name = TMP . 'data/smart_plate/terms.json';

    echo file_get_contents($file_name);
  }
}

?>