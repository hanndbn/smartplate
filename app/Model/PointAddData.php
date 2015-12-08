<?php
App::uses('PointData', 'Model');

/**
 * Model for point_add_data table
 *  
 * @package       app.Model
 * 
 */
class PointAddData extends PointData {
  
  public function __construct($id = false, $table = null, $ds = null) {
    $useTable = 'point_add_data';
    parent::__construct($id, $table, $ds);
  }
    
  public function addWelcomePoint($user_id, $uuid) {
    
    $orderid = "wp_".$uuid;
    
    $res = $this->find( 'count', array(  'conditions' => array('order_id' => $orderid) ) );
                        
    if( empty($res) ){
      $data = array();
      $this->create();
      $data['PointAddData'] = array( "user_id"=>$user_id, 
                  "order_id"=>$orderid, 
                  "product_id"=>"welcome_point", 
                  "purchase_time"=>time(), 
                  "point"=>500, 
                  "uuid"=>$uuid
      );
      
      try{
        $this->save($data);
      }catch( exception $e ){
        var_dump($data);
      }
    }
  }
  
}
?>
