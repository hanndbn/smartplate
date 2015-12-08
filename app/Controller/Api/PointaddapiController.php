<?php

App::uses('APIController', 'Controller');

define('POINT_ADD_BASE_CODE', 31000);
class PointAddAPIController extends APIController {
  // ...
  
  public function process(){
  	
    $this->loadModel('PointAddData');
    $this->loadModel('PointUseData');
      
    // if we get the get information, try to authenticate
      
      $order_id = $this->request->data('order_id');
      if( empty($order_id) ){                           throw new Exception("not found order id", POINT_ADD_BASE_CODE+1);   }
      
      $product_id = $this->request->data('product_id');
      if( empty($product_id) ){                           throw new Exception("not found product id", POINT_ADD_BASE_CODE+2);   }
      
      $purchase_time = $this->request->data('purchase_time');
      if( empty($purchase_time) ){                           throw new Exception("not found purchase time", POINT_ADD_BASE_CODE+2);   }
      
      $point = $this->request->data('point');
      if( empty($point) ){                           throw new Exception("not found point", POINT_ADD_BASE_CODE+3);   }

      $this->PointAddData->save( array( "user_id"=>$this->session['user_id'], 
                                        "order_id"=>$order_id, 
                                        "product_id"=>$product_id, 
                                        "purchase_time"=>$purchase_time, 
                                        "point"=>$point, 
                                        "uuid"=>$this->session['uuid']
                                ));
                  
      $added_point  = $this->PointAddData->getPoint($this->session['user_id']);
      $used_point   = $this->PointUseData->getPoint($this->session['user_id']);
      
      $this->result['point'] = $added_point-$used_point;
        
  }

}

?>