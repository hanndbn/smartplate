<?php

App::uses('APIController', 'Controller');

class StoreMailAPIController extends APIController {
  // ...
  
  public function process(){
  	
    $this->loadModel('User');
      
    // if we get the get information, try to authenticate
    if ($this->request->is('get')) 
    {
      
      $mail = $this->request->query('m');
      if( empty($mail) ){                           throw new Exception("not found mail address", 5);   }
      
      
      $user_data = $this->User->findById($this->session['user_id']); 
      
      //user check
      if(empty($user_data['User'])){                throw new Exception("not regist user", 9);          }
      
      $this->User->id = $this->session['user_id'];
      $this->User->set('mail', $mail);
      
      if( ! $this->User->save() ){
        throw new Exception("invalid mail address", 10);
      }
      
    }
    
  }

}

?>