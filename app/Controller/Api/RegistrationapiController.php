<?php

App::uses('LoginapiController', 'Controller');
App::uses('User', 'model');

class RegistrationAPIController extends LoginapiController {
  // ...
  
  public static function who() {
        return __CLASS__;
    }
  
  public function process(){
  
    // if we get the get information, try to authenticate
    if ($this->request->is('get')) 
    {
      $this->loadModel('User');
      $this->loadModel('Team');   
      $this->loadModel('PointAddData');   
    
      $login_id = $this->request->query('uname');
      $password = $this->request->query('password');
      $uuid = $this->request->query('i');
      $type = $this->request->query('tp');
      
      $this->User->unbindModel(
        array('hasMany' => array('Session','Device'))
      );
      
      $output = array();
      
      // check duplicate user data
      $user_count = $this->User->find(   'count', 
                                          array(  'conditions' => array('login_name' => $login_id),
                                          'fields' => array('id') ));
                                          
      if ( $user_count ){
        if ($type !== "sns") {
          throw new Exception("alrady registed user id", 6);
        }
      }else{
      
        // create project data
        $this->Team->create();
        $this->request->data['Team'] = array(
            'management_id'  => 0,
            'name'  => 'SmartPlate',
            'valid'  => 1
        );
        
        if (! $this->Team->save($this->request->data)) {
          throw new RuntimeException('Register Project Failed', 1);
        }
        
        // create user data
        if( strlen($password) == 40 ){
          $pass_sha = $password;
        }else{
          $pass_sha = User::CryptPassword($password);
        }
        
        $this->User->create();
        $this->request->data['User'] = array(
            'login_name'  => $login_id,
            'password'    => $pass_sha,
            'team_id'     => $this->Team->id,
            'status'      => 1,
            'application' => 1
        );
        
        if (! $this->User->save($this->request->data)) {
          throw new RuntimeException('Register User Failed', 1);
        }
  
        // create welcome point data
        $this->PointAddData->addWelcomePoint($this->User->id,$uuid);
        
        if (! $this->Team->save($this->request->data)) {
          throw new RuntimeException('Register Project Failed', 1);
        }
        
          $converted = false;
          if( ! empty($this->request->query['ver'] ) ){
                $ver_array = explode('.', $this->request->query['ver']);
                if( !empty($ver_array[0]) && intval($ver_array[0]) > 1){
                    $converted = true;
                }elseif( !empty($ver_array[1]) && intval($ver_array[1]) > 0){
                    $converted = true;
                }elseif( !empty($ver_array[2]) && intval($ver_array[2]) > 15){
                    $converted = true;
                }
          }
          if( $converted ){ User::converted($this->User->id); }
      }
      
      
      // login
      parent::process();
    }
    
  }

}

?>