<?php

App::uses('APIController', 'Controller');

class SaveDataAPIController extends APIController {
  // ...
    
  public function process(){
    
    $data_dir = TMP . 'data/smart_plate/save_datas/';
    $uid = $this->session['user_id'];


    $json_str = $this->request->data('datas');
    if( empty($json_str) ){       throw new Exception("not found datas", 1);  }
    
    if( !is_string($json_str) ){  throw new Exception("invalid datas", 3);    }
    
    $datas = json_decode($json_str);
    
    if( empty($datas) ){          throw new Exception("invalid datas", 4);    }
    
    $file_name = "$data_dir$uid.dat";
    
    $result = file_put_contents($file_name, $json_str);
    if( empty($result) ){         throw new Exception("faild write datas.", 6);}
    
  }
  
}

?>