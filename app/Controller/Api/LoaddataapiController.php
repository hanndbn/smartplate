<?php

App::uses('APIController', 'Controller');

class LoadDataAPIController extends APIController {
  // ...

  public function process(){
    
    $data_dir = TMP . 'data/smart_plate/save_datas/';
    
    $uid = $this->session['user_id'];
    if( $this->session['team_id'] == 1 ){
      $file_name = $data_dir."77.dat";
    }else{
      $file_name = "$data_dir$uid.dat";
    }

    if( !file_exists($file_name)){
      $file_name = $data_dir."sample.dat";
      if( !file_exists($file_name)){
        throw new Exception("not found data.", 6);
      }
    }
    $json_str = file_get_contents($file_name);
   
    $this->result['no_encode'] = $json_str;
  }
}

?>