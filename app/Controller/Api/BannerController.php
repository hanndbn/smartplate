<?php

App::uses('AppController', 'Controller');

class BannerController extends AppController {
  // ...
    
  public function index(){
      $this->autoRender = false;
    
    $file_name = TMP . 'data/smart_plate/banner.json';

    echo file_get_contents($file_name);
  }
}

?>