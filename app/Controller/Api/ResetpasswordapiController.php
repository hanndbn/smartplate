<?php

App::uses('APIController', 'Controller');

class ResetPasswordAPIController extends APIController {
  // ...
  
  public static function who() {
        return __CLASS__;
    }
    
  public function process(){
  	
    $this->loadModel('User');
      
    // if we get the get information, try to authenticate
    if ($this->request->is('get')) 
    {
      
      $uname = $this->request->query('uname');
      if( empty($uname) ){                            throw new RuntimeException("not found user name",       4); }
      $mail = $this->request->query('m');
      if( empty($mail) ){                             throw new RuntimeException("not found mail address",    5); }
      
      if (! preg_match(AppModel::REGULAR_EXPRESSION_MAIL_ADDRESS, $mail)) {
                                                      throw new Exception("invalid mail address",             6); }      
      
      $user_data = $this->User->find( 'first', 
                              array(  'conditions' => array('login_name' => $uname, 'mail' => $mail)
                              ));
      
      //user check
      if(empty($user_data['User'])){                throw new RuntimeException("not found user data",         7); }
      $user = $user_data['User'];
      
      $temp_pass = User::getRandomString();
    
      $this->User->id = $user['id'];
      $this->User->set('password', User::CryptPassword($temp_pass));
      
      if( ! $this->User->save() ){                  throw new RuntimeException("Reset password failed",       12);}
      
      $this->result['tmp_pass'] = $temp_pass;
      
    }
    
  }

}

?>

