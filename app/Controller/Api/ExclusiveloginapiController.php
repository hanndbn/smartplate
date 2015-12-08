<?php

App::uses('LoginapiController', 'Controller');

class ExclusiveLoginAPIController extends LoginapiController {
  // ...
  
  public function process(){

    $this->loadUser();
    
    $mode = $this->request->query('mode');
    
    if( empty($mode) ){   // before check other login user
      
      $logined = $this->Session->find( 'all', 
                              array(  'conditions' => array('user_id' => $this->user['id'], 'uuid !=' => 'PC'),
                                      'fields' => array('id','uuid')
                              ));
                              
      if( count($logined) > 0 ){
        throw new RuntimeException('Is logged in', 5);
      }
    }else{              // before delete other login user
      $this->Session->deleteAll(array('user_id' => $this->user['id'],'uuid !=' => 'PC'), false);
    }
    
    parent::process();
    
  }

}

?>