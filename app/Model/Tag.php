<?php
App::uses('AppModel', 'Model');

/**
 * Model for table tag
 *
 * @package       app.Model
 */
class Tag extends AppModel {

    public $useTable = 'tag';
    public $app_user_id = 0;

    public $recursive = -1;
    public $validate = array(
        'name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Name is required',
                'allowEmpty' => false
            )
        ),
    );
    public function TagURLtoTag($tag_url) {

      $tag_data = substr($tag_url, strlen($tag_url)-15);        
    
      $index_char = substr($tag_data, 0,2);  
      $lot_number = substr($tag_data, 2,5);  
      $index_number = substr($tag_data, 8,7);  
      
      return "$index_char.$lot_number.$index_number";
    }
    
    public function SelectByPartialMatchTags($tags, $team_id, $arg = null, &$total = null) {

      $conditions_or = array();
      if ($tags) {
        foreach ($tags as $t) {
          $conditions_or[] = array('Tag.tag LIKE' => "%$t%");
        }
      }
      
      $options = array( 'conditions'  => array('team_id' => $team_id,'OR' => $conditions_or),
                           );
      if( $this->app_user_id > 0){
          $options['conditions']['activation_user'] = $this->app_user_id;
      }
      
      $total = $this->find( 'count', $options );
      
      if( !empty( $arg['order'] ) )
        $options['order'] = array( $arg['order'] => $arg['direction'] );
      if( !empty( $arg['limit'] ) )
        $options['limit'] = $arg['limit'];
      if( !empty( $arg['offset'] ) )
        $options['offset'] = $arg['offset'];
                                          
      $res = $this->find( 'all', $options );
        
      $datas = array();
      foreach ($res as $key => $value) {
        $value['Tag']['cdate'] = $this->convertDateTokyo2UTC($value['Tag']['cdate']);
        if( empty($value['Tag']['icon']) ){
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        }else{
          //$value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/" . DS . "th" . basename($value['Tag']['icon']);
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/". $value['Tag']['icon'];
        }
        $datas[] = $value['Tag'];
      }                   
       return $datas;
      
    }
    
   public function SelectByTeamWithKeyword($team_id, $keyword, $arg = null, &$total = null) {
      $team_id = intval($team_id);

      $options =  array(
                'fields' => array( 'Tag.*' ),
            );
            
      $keyword = "%$keyword%";
      $options['conditions'] = array(  'Tag.team_id' => $team_id );
      $options['conditions']['OR'] = array( 'Tag.name LIKE' => $keyword,'Tag.tag LIKE' => $keyword );
      
      if( $this->app_user_id > 0){
          $options['conditions']['activation_user'] = $this->app_user_id;
      }
      
      $total = $this->find( 'count', $options );
      
      if( !empty( $arg['order'] ) )
        $options['order'] = array( $arg['order'] => $arg['direction'] );
      if( !empty( $arg['limit'] ) )
        $options['limit'] = $arg['limit'];
      if( !empty( $arg['offset'] ) )
        $options['offset'] = $arg['offset'];
                                          
      $res = $this->find( 'all', $options );
      
       
      $datas = array();
      foreach ($res as $key => $value) {
        $value['Tag']['cdate'] = $this->convertDateTokyo2UTC($value['Tag']['cdate']);
        if( empty($value['Tag']['icon']) ){
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        }else{
          //$value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/" . DS . "th" . basename($value['Tag']['icon']);
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/". $value['Tag']['icon'];
        }
        $datas[] = $value['Tag'];
      }                   
       return $datas;              
    }

    public function SelectByTeamWithLabel($team_id, $label_id, $arg = null, &$total = null) {
    
      $team_id = $team_id;
      
      $options =  array( 'fields' => array( 'Tag.*') );
      

      if( $label_id){
        $sql = "select LabelData.target_id from label_datas AS LabelData"
                    ." LEFT JOIN label AS Label ON Label.id = LabelData.label_id"
                    ." where Label.team_id = $team_id and Label.type = 'TagModel' and Label.id = $label_id";
                    
      }else{
        $sql = "select `LabelData`.target_id from `smartplate`.`label_datas` AS `LabelData` LEFT JOIN `smartplate`.`label` AS `Label` ON `Label`.`id` = `LabelData`.`label_id` where `Label`.`team_id` = $team_id";
      }
      
      $res = $this->query($sql);
      $target_ids = array();
        foreach ($res as $key => $value) {
            $target_ids[] = $value['LabelData']['target_id'];
        }
      
      if( $label_id){
        $options['conditions'] = array(  'Tag.team_id' => $team_id, 'Tag.id' => $target_ids );
      }else{
        $options['conditions'] = array(  'Tag.team_id' => $team_id, 'NOT' => array('Tag.id' => $target_ids) );
      }
      
      if( $this->app_user_id > 0){
          $options['conditions']['activation_user'] = $this->app_user_id;
      }
      $total = $this->find( 'count', $options );
      
      if( !empty( $arg['order'] ) )
        $options['order'] = array( $arg['order'] => $arg['direction'] );
      if( !empty( $arg['limit'] ) )
        $options['limit'] = $arg['limit'];
      if( !empty( $arg['offset'] ) )
        $options['offset'] = $arg['offset'];
                                          
      $res = $this->find( 'all', $options );

      $datas = array();
      foreach ($res as $key => $value) {
        $value['Tag']['cdate'] = $this->convertDateTokyo2UTC($value['Tag']['cdate']);
        if( empty($value['Tag']['icon']) ){
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        }else{
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/" . dirname($value['Tag']['icon']) . DS . "th" . basename($value['Tag']['icon']);
          //$value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/". $value['Tag']['icon'];
        }
        $datas[] = $value['Tag'];
      }                        
       return $datas;
  }

    public function SelectByTeams($team_id, $arg = null, &$total = null) {
    
      $team_id = intval($team_id);
      
      $options =  array(
                'conditions' => array(  'Tag.team_id' => $team_id )
            );
       
      if( $this->app_user_id > 0){
          $options['conditions']['activation_user'] = $this->app_user_id;
      }     
      $total = $this->find( 'count', $options );
      
      if( !empty( $arg['order'] ) )
        $options['order'] = array( $arg['order'] => $arg['direction'] );
      if( !empty( $arg['limit'] ) )
        $options['limit'] = $arg['limit'];
      if( !empty( $arg['offset'] ) )
        $options['offset'] = $arg['offset'];
                                          
      $res = $this->find( 'all', $options );
      
      $datas = array();
      foreach ($res as $key => $value) {
        $value['Tag']['cdate'] = $this->convertDateTokyo2UTC($value['Tag']['cdate']);
        if( empty($value['Tag']['icon']) ){
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        }else{
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/" . dirname($value['Tag']['icon']) . DS . "th" . basename($value['Tag']['icon']);
          //$value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/". $value['Tag']['icon'];
        }
        $datas[] = $value['Tag'];
      }
                                    
       return $datas;
  }
    
   public function SelectByIDs($team_id, $ids, $arg = null, &$total = null) {
    
      $team_id = intval($team_id);
      
      $options =  array(
                'conditions' => array(  'Tag.team_id' => $team_id, 'Tag.id' => $ids )
            );
      
      if( $this->app_user_id > 0){
          $options['conditions']['activation_user'] = $this->app_user_id;
      }    
      $total = $this->find( 'count', $options );
      
      if( !empty( $arg['order'] ) )
        $options['order'] = array( $arg['order'] => $arg['direction'] );
      if( !empty( $arg['limit'] ) )
        $options['limit'] = $arg['limit'];
      if( !empty( $arg['offset'] ) )
        $options['offset'] = $arg['offset'];
                                          
      $res = $this->find( 'all', $options );
      
      $datas = array();
      foreach ($res as $key => $value) {
        $value['Tag']['cdate'] = $this->convertDateTokyo2UTC($value['Tag']['cdate']);
        if( empty($value['Tag']['icon']) ){
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        }else{
          //$value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/$team_id" . DS . basename($value['Tag']['icon']);
          $value['Tag']['icon'] = Router::fullbaseUrl() . DS . "upload/plate/". $value['Tag']['icon'];
        }
        $datas[] = $value['Tag'];
      }
                                    
       return $datas;
  }


    /**
     * Return plate extension index
     * @return string extension index
     */
    function getExtensionIndex() {
      $length = 7;
      $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
      $ext_index = null;
      for ($i = 0; strlen($ext_index) < $length; $i++) {
          $ext_index .= $str[rand(0, count($str))];
      }
      
      $options =  array(
                'conditions' => array(  'Tag.ext_index' => $ext_index )
            );
            
      $has_data = $this->find( 'count', $options );
      if( $has_data > 0 ){
        $ext_index = $this->getExtensionIndex();
      }
      return $ext_index;
    }
}


?>
