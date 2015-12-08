<?php

App::uses('APIController', 'Controller');

class ChangePasswordAPIController extends APIController {
  // ...
  
  public function process(){
  	
    $this->loadModel('User');
      
    // if we get the get information, try to authenticate
    if ($this->request->is('get')) 
    {
      
      $password = $this->request->query('password');
      if( empty($password) ){                           throw new RuntimeException("not found password",        5); }
      $confirm_password = $this->request->query('conf_password');
      if( empty($confirm_password) ){                   throw new RuntimeException("not found confirm password",6); }
      $old_password = $this->request->query('old_password');
      if( empty($old_password) ){                       throw new RuntimeException("not found old password",    7); }
      
      
      $user_data = $this->User->findById($this->session['user_id']); 
      
      //user check
      if(empty($user_data['User'])){                throw new RuntimeException("not regist user",         9); }
      $user = $user_data['User'];
      
      // confirm password check
      if( $password != $confirm_password){          throw new RuntimeException("invalid confirm password", 10);}
      
      //check old password
      if( $user['password'] !=  User::CryptPassword($old_password)){
                                                    throw new RuntimeException("invalid old password",    11);}
    
      $this->User->id = $this->session['user_id'];
      $this->User->set('password', User::CryptPassword($password));
      
      if( ! $this->User->save() ){                  throw new RuntimeException("Change password failed",  12);}
      
    }
    
  }

}

?>
