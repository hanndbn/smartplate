<?php
App::uses('AppModel', 'Model');

/**
 * Model for table tag
 *
 * @package       app.Model
 */
class Tag extends AppModel {

    public $useTable = 'tag';

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
        if( empty($value['Tag']['icon']) )
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
        $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
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
        if( empty($value['Tag']['icon']) )
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
        $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        $datas[] = $value['Tag'];
      }                   
       return $datas;              
    }

    public function SelectByTeamWithLabel($team_id, $label_id, $arg = null, &$total = null) {
    
      $team_id = $team_id;
      
      $options =  array(
                'fields' => array( 'Tag.*','Label.type','LabelData.target_id' ),
                'joins' => array(
                    array(
                        'type' => 'LEFT',
                        'table' => 'label_datas',
                        'alias' => 'LabelData',
                        'conditions' => array(
                            'LabelData.target_id = Tag.id'
                        )
                    ),
                    array(
                        'type' => 'LEFT',
                        'table' => 'label',
                        'alias' => 'Label',
                        'conditions' => array(
                            'Label.id = LabelData.label_id',
                            "Label.type = 'TagModel'" 
                        )
                    )
                ),
            );
      
      if( $label_id){
        $options['conditions'] = array(  'Tag.team_id' => $team_id, 'LabelData.label_id' => $label_id );
      }else{
        $options['conditions'] = array(  'Tag.team_id' => $team_id, "LabelData.target_id IS NULL" );
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
        if( empty($value['Tag']['icon']) )
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
        $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        $datas[] = $value['Tag'];
      }                        
       return $datas;
  }

    public function SelectByTeams($team_id, $arg = null, &$total = null) {
    
      $team_id = intval($team_id);
      
      $options =  array(
                'conditions' => array(  'Tag.team_id' => $team_id )
            );
            
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
        if( empty($value['Tag']['icon']) )
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
        $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        $datas[] = $value['Tag'];
      }
                                    
       return $datas;
  }
    
   public function SelectByIDs($team_id, $ids, $arg = null, &$total = null) {
    
      $team_id = intval($team_id);
      
      $options =  array(
                'conditions' => array(  'Tag.team_id' => $team_id, 'Tag.id' => $ids )
            );
            
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
        if( empty($value['Tag']['icon']) )
          $value['Tag']['icon'] = substr($value['Tag']['tag'], 0,2).substr($value['Tag']['tag'], 3,5).'.jpg';
        $value['Tag']['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $value['Tag']['icon'];
        $datas[] = $value['Tag'];
      }
                                    
       return $datas;
  }
}


?>
