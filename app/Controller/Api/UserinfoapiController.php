<?php

App::uses('APIController', 'Controller');
App::uses('User', 'model');

class UserInfoAPIController extends APIController {
  // ...
  
  public function process(){
  	
    $this->loadModel('User');
    $this->loadModel('PointAddData');
    $this->loadModel('PointUseData');
      
    // if we get the get information, try to authenticate
    if ($this->request->is('get')) 
    {
      
      $added_point  = $this->PointAddData->getPoint($this->session['user_id']);
      $used_point   = $this->PointUseData->getPoint($this->session['user_id']);

      $nowPoint = $added_point-$used_point;
      
      $functions = $this->PointUseData->getValidFunctions($this->session['user_id']);
      
      $user_data = $this->User->find( 'first', array( 'conditions' => array( 'id' => $this->session['user_id'] ),
                                                      'fields' => array('power','status','name','mail') ) );

      if( empty($user_data['User']) ){
        $this->result['user'] = array();
      } else {
        if( empty($user_data['User']['mail']) ){
          $user_data['User']['mail'] = '';
        }
        $user_data['User']['power'] = intval($user_data['User']['power']);
        $user_data['User']['status'] = intval($user_data['User']['status']);
        $this->result['user'] = $user_data['User'];
      }
      
      $this->result['point'] = $nowPoint;
      $this->result['functions'] = $functions;
      $this->result['converted'] = User::isConvertedUser($this->session['user_id']);
          
    }
        
  }

}

?>