<?php
App::uses('AppModel', 'Model');
App::uses('Bookmark', 'Model');
/**
 * Model for table links
 *
 * @package       app.Model
 */
class Link extends AppModel {

    const ADDITIONAL_BASE_ID = 1; // Plate tag + User token
    
    const REPLACE_ACCESS_PLATE = "___A_PLATE____";
    const REPLACE_USER_TOKEN = "___U_TOKEN____";

    
    public $useTable = 'links';
    
    /**
     * delete records by tag id
     * @param $tag_id : tag id from tag table 
     */
    function DeleteByTagID( $tag_id ) {
      
      $data_dir = TMP. '/data/smart_plate/rotate/';
      $file_name = $data_dir.$tag_id.'.dat';
      if( file_exists($file_name) ){
        unlink($file_name);
      }
      
      return $this->deleteAll(array('tag_id' => $tag_id),  false );
    }

     /**
     * delete records by bookmark id
     * @param $bookmark_id : bookmark id from bookmark table 
     */
    function DeleteByBookmarkID( $bookmark_id ) {
      
      return $this->deleteAll(array('bookmark_id' => $bookmark_id),  false );
    }

  /**
   * Get url by tag ( XX.00000.0000000 ) from links table
   * @param $tag : tag record from tag table
   */
    function GetURLByTag( $tag ) {
      $url = "";
      if( empty( $tag['bookmark_id']) ) {
          $links = $this->find( 'all', array( 'conditions' => array('tag_id' => $tag['id'])));
      }else{
        $links = $this->find( 'all', array( 'conditions' => array('bookmark_id' => $tag['bookmark_id'])));
        if( count($links) == 0 ){
          $links = $this->find( 'all', array( 'conditions' => array('tag_id' => $tag['id'])));
        }
      }
      
      $links = $this->removeArrayWrapper('Link', $links);
      $link_count = count($links);
      if( $link_count == 0 ){
        return $url;
      }
    
      $type = $links[0]['type'];
      if( !isset($type) ){
        return $url;
      }
      switch($type){
        case Bookmark::TYPE_NORMAL :{
          $sub_type = 0;
        }break;
        case Bookmark::TYPE_OS :{ // normal
        $ua = $_SERVER['HTTP_USER_AGENT'];
    
        if (strpos($ua, 'Android') !== false) {
        $sub_type = 1;
        } elseif ( (strpos($ua, 'iPhone') !== false) || (strpos($ua, 'iPad') !== false)  ) {
        $sub_type = 2;
        } else {
        $sub_type = 9;
        }
        }break;
        case Bookmark::TYPE_TAILS :{ // tails
          $tag_tag = $tag['tag'];
          $url = "http://plate.id/tails/tails.php?tag=$tag_tag&atp=___SP_PLATETYPE____";
          return $url;
        }break;
        case Bookmark::TYPE_RANDOM :{ // randam
          $sub_type = rand(1, $link_count);
        }break;
        case Bookmark::TYPE_ROTATE :{ // rotate
          $data_dir = TMP . '/data/smart_plate/rotate/';
          $file_name = $data_dir.$tag['id'].'.dat';
          
          if( file_exists($file_name) ){
            $rotate_count = file_get_contents($file_name);
            $sub_type = $rotate_count + 1;
            if($link_count < $sub_type ){
              $sub_type = 1;
            }
          }else{
            $sub_type = 1;
          }
          
          $black_list = array( 'ZXing (Android)' );
          $ua = $_SERVER['HTTP_USER_AGENT'];
          
          $write_flag = true;
          foreach ($black_list as $value) {
            if (strpos($ua,$value)!==false){
              $write_flag = false;
            break;
            }
          }
          if($write_flag){
            file_put_contents($file_name, $sub_type,LOCK_EX);
          }
          
        }break;
      } 
      
      foreach ($links as $link) {
      if( $link['sub_type'] == $sub_type ){
          $url = $link['url'];
          
          // add by geo
          if( !empty($link['additional']) && $link['additional'] == self::ADDITIONAL_BASE_ID ){
              $url .= self::REPLACE_ACCESS_PLATE.self::REPLACE_USER_TOKEN;
          }
       /* if( empty($links['bookmark_id']) ){
          $url = $link['url'];
        }else{
          $url = BookmarkModel::SelectByID($link['bookmark_id']);
        }*/
        break;
      }
      }
      
      return $url;
  }

   /**
   * Get url by content id from links table
   * @param $bookmark_id : content id ( bookmark table )
   */
    function GetURLByContentID( $bookmark_id ) {
      $url = "";
      $links = $this->find( 'all', array( 'conditions' => array('bookmark_id' => $bookmark_id)));
      if( count($links) == 0 ){
        return '';
      }
    
      $links = $this->removeArrayWrapper('Link', $links);
      $link_count = count($links);
      if( $link_count == 0 ){
        return $url;
      }
    
      $type = $links[0]['type'];
      if( !isset($type) ){
        return $url;
      }
      switch($type){
        case Bookmark::TYPE_NORMAL :{
          $sub_type = 0;
        }break;
        case Bookmark::TYPE_OS :{ // normal
        $ua = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($ua, 'Android') !== false) {
        $sub_type = 1;
        } elseif ( (strpos($ua, 'iPhone') !== false) || (strpos($ua, 'iPad') !== false)  ) {
        $sub_type = 2;
        } else {
        $sub_type = 9;
        }
        }break;
/*        case Bookmark::TYPE_TAILS :{ // tails
          $tag_tag = $tag['tag'];
          $url = "http://plate.id/tails/tails.php?tag=$tag_tag&atp=___SP_PLATETYPE____";
          return $url;
        }break;
        case Bookmark::TYPE_RANDOM :{ // randam
          $sub_type = rand(1, $link_count);
        }break;
        case Bookmark::TYPE_ROTATE :{ // rotate
          $data_dir = TMP . '/data/smart_plate/rotate/';
          $file_name = $data_dir.$tag['id'].'.dat';
          
          if( file_exists($file_name) ){
            $rotate_count = file_get_contents($file_name);
            $sub_type = $rotate_count + 1;
            if($link_count < $sub_type ){
              $sub_type = 1;
            }
          }else{
            $sub_type = 1;
          }
          
          $black_list = array( 'ZXing (Android)' );
          $ua = $_SERVER['HTTP_USER_AGENT'];
          
          $write_flag = true;
          foreach ($black_list as $value) {
            if (strpos($ua,$value)!==false){
              $write_flag = false;
            break;
            }
          }
          if($write_flag){
            file_put_contents($file_name, $sub_type,LOCK_EX);
          }
          
        }break;
 */
      } 
      
      foreach ($links as $link) {
        if( $link['sub_type'] == $sub_type ){
            $url = $link['url'];
          break;
        }
      }
      
      return $url;
  }
}
?>
