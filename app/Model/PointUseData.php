<?php
App::uses('PointData', 'Model');

/**
 * Model for point_use_data table
 *  
 * @package       app.Model
 * 
 */

define( 'FUNCTION_POINT_FILE','http://smartplate.pro/api/point/functions.json' );
define( 'PERIOD_POINT_FILE','http://smartplate.pro/api/point/terms.json' );

class PointUseData extends PointData {

  public function __construct($id = false, $table = null, $ds = null) {
    $useTable = 'point_use_data';
    parent::__construct($id, $table, $ds);
  }
  
  public function getUsePoint($type,$target)
  {
      if( $type == PointData::TYPE_PERIOD )
      {
        $file_name = PERIOD_POINT_FILE;
      }
     else if( $type == PointData::TYPE_FUNCTION )
    {
        $file_name = FUNCTION_POINT_FILE;
    }else{
      return -1;
    }
    
    $json = file_get_contents($file_name);
    if( empty($json) ){ return -1; }
    
    $data = json_decode($json);
    if( empty($data) ){ return -1; }

    $points = $data->points;
    if( empty($points) ){ return -1; }
    
    foreach ($points as  $value) {
      if( $value->target == $target ) {
        return $value->point;
      }
    }
    return -1;
  }
  
  public function getValidFunctions($user_id) {
    
      $res = $this->find(  'all', 
                            array(  'conditions' => array('user_id' => $user_id, 'type' => PointData::TYPE_FUNCTION),
                                    'fields' => array('data as function') )
                          );
                          
      $functions = array();
      if( ! empty($res) ){
        foreach ($res as $key => $point_use_data) {
          $functions[] = $point_use_data['PointUseData'];
        }
        
      }
      
      return $functions;
  }
}

?>
