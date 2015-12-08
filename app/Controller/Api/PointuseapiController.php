<?php

App::uses('APIController', 'Controller');
App::uses('TagHistory', 'Model');
define('POINT_USE_BASE_CODE', 32000);
class PointUseAPIController extends APIController {
  // ...
  
  public function process(){
    date_default_timezone_set('UTC');
  	
    $this->loadModel('PointAddData');
    $this->loadModel('PointUseData');
      
    // if we get the get information, try to authenticate
    if ($this->request->is('get')) 
    {
      
      $type = $this->request->query('type');
      if( empty($type) ){                           throw new Exception("not found point type", POINT_USE_BASE_CODE+1);   }
      
      $data = $this->request->query('dat');
      if( empty($data) ){                           throw new Exception("not found point data", POINT_USE_BASE_CODE+2);   }
      
      $tag = $this->request->query('tag');
      if( empty($data) ){   $tag = '';  }
      
      if( $type == PointData::TYPE_FUNCTION ){
        $hasData = $this->PointUseData->find( 'count',
                                              array(  'conditions' => array(  'user_id' => $this->session['user_id'],
                                                                              'type'    => PointData::TYPE_FUNCTION,
                                                                              'data'    => $data ) )
                    );
                    
        if( $hasData ){ throw new Exception("already valid function", POINT_USE_BASE_CODE+3); exit; }
    
      }
  
    $point = $this->PointUseData->getUsePoint($type, $data);
    if( $point < 0 ){ throw new Exception("not found point setting data", POINT_USE_BASE_CODE+4); exit; }
    
    $added_point  = $this->PointAddData->getPoint($this->session['user_id']);
    $used_point   = $this->PointUseData->getPoint($this->session['user_id']);
    $nowPoint = $added_point-$used_point;
    
    if( $nowPoint-$point < 0 ){
      throw new Exception("not enough points: $nowPoint: $point", POINT_USE_BASE_CODE+5);
      exit;
    }
    
    if( $type == PointData::TYPE_PERIOD ){
        if( !$tag ){ throw new Exception("not found tag", POINT_USE_BASE_CODE+6); exit; }
        $tag_history = new TagHistory();
        $tag_history->UpdateLimitDateByTagID($tag, $data );
    }
                  
      $this->PointUseData->save( array( "user_id"=>$this->session['user_id'], 
                                        "type"=>$type, 
                                        "data"=>$data, 
                                        "point"=>$point, 
                                        "tag"=>$tag
                                ));
                  
      $used_point   = $this->PointUseData->getPoint($this->session['user_id']);
      
      $this->result['point'] = $added_point-$used_point;
          
    }
        
  }

}

?>