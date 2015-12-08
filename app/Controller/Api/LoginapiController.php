<?php

App::uses('APIController', 'Controller');
App::uses('User', 'model');

class LoginapiController extends APIController {
  // ...
  
  protected $user;
  
  public static function who() {
        return __CLASS__;
    }
    
  protected function loadUser(){
 
      $this->loadModel('User');
    
      $login_id = $this->request->query('uname');
      $password = $this->request->query('password');
      
      if( strlen($password) == 40 ){
        $pass_sha = $password;
      }else{
        $pass_sha = User::CryptPassword($password);
      }
      
      $this->User->unbindModel(
        array('hasMany' => array('Session','Device'))
      );
      
      $output = array();
      $account = $this->User->find( 'first', 
                              array(  'conditions' => array('login_name' => $login_id, 'password' => $pass_sha),
                                      'fields' => array('team_id','id','status','power','application')
                              ));
                              
      if (empty($account['User'])){
        throw new RuntimeException('Login Failed', 1);
      }
      $this->user = $account['User'];
  }
  
  
  public function process(){
  	
    //$this->loadModel('Session');
	
    // if we get the get information, try to authenticate
    if ($this->request->is('get')) 
    {
      $this->loadUser();
      
      $uuid = $this->request->query('i');
      $app = $this->request->query('a');
      
      if( $app == 'sp' && $this->user['application'] != 1 ){
            throw new RuntimeException('different application type', 2);
      }
      if( $app == 'cf' && $this->user['application'] == 1 ){
            throw new RuntimeException('different application type', 3);
      }
            
      $token = $this->Session->GenerateToken();
      $this->Session->create();
      $this->request->data['Session'] = array(
          'token' => $token,
          'team_id' => $this->user['team_id'],
          'user_id' => $this->user['id'],
          'uuid' => $uuid
      );
      
      if ($this->Session->save($this->request->data)) {
        $this->result['token'] = $token;
        $this->session = $this->request->data['Session'];
        $this->result['converted'] = User::isConvertedUser($this->user['id']);
      }else{
        throw new RuntimeException('Login Failed', 1);
      }
      
    }
    
  }

}

?>