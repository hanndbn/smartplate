<?php
App::uses('AppModel', 'Model');
App::uses('AdditionalElement', 'Model');

/**
 * Application model for device table.
 *
 * @package       app.Model
 */
class CustomValue extends AppModel 
{
    public $useDbConfig = 'access_log_database';
    public $useTable = 'custom_value';
    public $primaryKey = 'id';
    public $recursive = -1;

    public $hasMany = array(
        'AdditionalElement' => array(
            'className' => 'AdditionalElement',
            'conditions' => array('AdditionalElement.custom_value_id = CustomValue.id'),
        )
    );
    
    /**
     *  Load value name from csv 
     *  @param $team_id : number | project id
     */
    private function LoadValueName( $team_id ) {
        
        $res = NULL;
        $filepath = WWW_ROOT.'/api/custom/1872.json';
        
        if( file_exists($filepath)){
            $json_str = file_get_contents($filepath); 
            $res = json_decode($json_str,true);
        }
        
        return $res;
    }
    
    /**
     * Insert custom data with additonal element
     * 
     * @param $tag : array | tag object
     * @param $bookmark : number | bookmark id
     * @param $token : string | in cookie data. 20 charactors
     * @param $values : array | custom elements ( element is number or string )
     */
    public function InsertValue( $tag, $token, $values ) {
        
      if( ! is_string($token) || strlen($token) != 40 ){
          throw new Exception("Invalid user token.", 1011);
      }
      
      $custom_value_id = 0;
      
      $this->create();

      if ($this->save( array('tag' => $tag['tag'], 'team_id' => $tag['team_id'], 'app_user_id' => $tag['activation_user'], 'bookmark_id' => $tag['bookmark_id'], 'user_token'=>$token ) )) {
          $custom_value_id = $this->getLastInsertId();
          if( is_array($values) && count($values) > 0 ) {
            $element = new AdditionalElement();
            foreach ($values as $key => $value) {
                if( is_numeric($value) || is_string($value) ){
                    $element->create();
                    $element->save( array('custom_value_id' => $custom_value_id, 'name' => $key, 'value' => $value) );
                }
            }
          }
      }

      return $custom_value_id;
    }
    
     /**
     * Get custom data with additonal element
     * 
     * @param $team_id : number | project id
     * @param $user_id : number | app user id
     * @param $tag_obj : object | tag object
     * @param $bookmark_obj : object | bookmark object
     * @param $start : date | start search date (UTC)
     * @param $end : date | end sreach date (UTC)
     */
    public function GetValue( $team_id,$user_id, $tag_obj, $bookmark_obj, $start, $end ) {
        
        $options = array();
        if( !empty($tag_obj)){
            $options['conditions'] = array('CustomValue.tag' => $tag_obj['tag']);
    
        }
        if( !empty($bookmark_obj)){
            $options['conditions'] = array('CustomValue.bookmark_id' => $bookmark_obj['id']);
        }
        
        $options['conditions']['team_id'] = $team_id;
        if( !empty($user_id) ){
            $options['conditions']['app_user_id'] = $user_id;
        }
        
        $result = array();
        if( !empty($options) ){
            $options['joins'] = array(  array(  'type' => 'INNER', 
                                                'table' => 'additional_element',
                                                'alias' => 'AdditionalElement', 
                                                'conditions' => array('AdditionalElement.custom_value_id = CustomValue.id')
                                              ));
            $options['fields'] = array('AdditionalElement.name','AdditionalElement.value',"count('AdditionalElement.value') as count",'CustomValue.cdate');
            
            if ($start)  { $options['conditions']['cdate >='] = $start; }
            if ($end)    { $options['conditions']['cdate <='] = $end; }
        
            $options['group'] = array('AdditionalElement.name','AdditionalElement.value');
            $data = $this->find('all',$options);

            $titles = $this->LoadValueName($team_id);
            $tmp = array();
            foreach ($data as $row) {
                $title = $row['AdditionalElement']['name'];
                $value = $row['AdditionalElement']['value'];
                if( !empty($titles[$title][$value]) ){
                    $value = $titles[$title][$value];
                }
                $tmp[$title][] = array( 'name'=>$value, 'count'=> $row[0]['count'] );
            }
            
            foreach ($tmp as $key => $row) {
                $result[] = array('title'=>$key, 'data'=>$row);
            }
        }
        
        return $result;      
    }
}

