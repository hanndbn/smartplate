<?php
App::uses('AppModel', 'Model');
App::uses('Tag', 'Model');
/**
 * Model for table links
 *
 * @package       app.Model
 */
class TagHistory extends AppModel {
  
    public $useTable = 'tag_history';
    
  const ACTIVE        = 1;
  const DEACTIVE      = 2;
  const UPDATE_LIMIT_DATE   = 3;
  
  const PERIOD_HARF_YEAR  = 183;
  const PERIOD_ONE_YEAR   = 365;
  
  public function GetFirstActivateDateByTagID( $tag_id ) {
    
    $first = array();
    $res = $this->find(  'first', 
                          array(  'conditions'  => array( 'tag_id'  => $tag_id,
                                                          'type'  => self::ACTIVE   ),
                                  'fields'      => array( 'cdate' ),
                                  'order'       => array( 'cdate' ),
                                  'limit'       => 1
                          ));
    
    if( !empty($res['TagHistory']) )
    {
      $first = $res['TagHistory'];
    }else{
      $first['cdate'] = "";
    }
    
    $limit = $this->GetLastLimitDateByTagID($tag_id);
    
    $first['limit_date'] = $limit['limit_date'];
    
    return $first;
  }
  
  public function GetLastLimitDateByTagID( $tag_id ) {
    
    $res = $this->find(  'first', 
                          array(  'conditions'  => array( 'tag_id'  => $tag_id, 
                                                          'NOT'     => array('limit_date' => NULL)),
                                  'fields'      => array( 'limit_date' ),
                                  'order'       => array( 'cdate DESC' ),
                                  'limit'       => 1
                          ));
    if( empty($res['TagHistory']) )
    {
      return array('limit_date'=>"");
    }
      return $res['TagHistory'];
  }
  
  public function UpdateLimitDateByTagID( $tag,$period_type ) {
    
    $limit_date = date('Y-m-d H:i:s');
    
    $TagModel = new Tag();
    
    $res = $TagModel->find(  'first', 
                              array(  'conditions'  => array( 'tag'  => $tag ),
                                      'fields'      => array( 'id' )
                                  ));
                                  
    $tag_data = $res['Tag'];                 
    if( empty($tag_data['id']) ){ throw new RuntimeException("not fount tag data", 1); }
    
    $tag_id = $tag_data['id'];
    
    $last_limit_data = $this->GetLastLimitDateByTagID($tag_id);
    
    
    if( !empty($last_limit_data['limit_date']) ){
      if( $last_limit_data['limit_date'] > date('Y-m-d H:i:s') ){
        $limit_date = $last_limit_data['limit_date'];
      }
    }
 
     if( $period_type == PointData::PERIOD_TYPE_HARF_YEAR ){
        $period = self::PERIOD_HARF_YEAR;
        $new_limit_date = date('Y-m-d H:i:s',strtotime("$limit_date +$period day"));
     }else{
        $new_limit_date = date('Y-m-d H:i:s',strtotime("$limit_date next year"));
     }
    
    $data = array();
    $this->create();
    $data['TagHistory'] = array(  "tag_id"=>$tag_id, 
                                  "type"=>self::UPDATE_LIMIT_DATE, 
                                  "limit_date"=>$new_limit_date );

    try{
      $this->save($data);
    }catch( exception $e ){
      var_dump($data);
    }
  }
}
?>
