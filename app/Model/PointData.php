<?php
App::uses('AppModel', 'Model');

/**
 * Model for point_add_data and point_use_data table
 *  
 * @package       app.Model
 * 
 */
class PointData extends AppModel {

  const TYPE_PERIOD     = 1;
  const TYPE_FUNCTION   = 2;
  
  const PERIOD_TYPE_HARF_YEAR  = 1;
  const PERIOD_TYPE_ONE_YEAR   = 2;

  public $useTable;
    
  public function getPoint( $user_id )
  {
    if( empty($useTable) )
    {
      
      $res = $this->find(  'first', 
                            array(  'conditions' => array('user_id' => $user_id),
                                    'fields' => array('SUM(point) as pt') )
                          );
                          
      if( empty($res[0]['pt']) ){
        return 0;
      }else {
        return $res[0]['pt'];
      }
    }
  }
  
}
?>
