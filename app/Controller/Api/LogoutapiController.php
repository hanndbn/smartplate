<?php

App::uses('APIController', 'Controller');

class LogoutAPIController extends APIController {
  // ...
    
  public function process(){
	
    if( ! $this->Session->deleteAll(array('token' => $this->session['token']), false) ){
      throw new Exception("Logout failed", 10000);
    }
    
  }
}

?>