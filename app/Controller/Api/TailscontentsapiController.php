<?php

App::uses('AppController', 'Controller');
App::uses('BookmarkExtData', 'Model');

  define('TAILS_CODE', 16000);
class TailsContentsAPIController extends AppController {
  // ...
  public function beforeFilter() {
     parent::beforeFilter();
        $this->Auth->allow(array('controller' => 'tailscontentsapi', 'action' => 'index'));
  }   
  public function index(){

      $this->autoRender = false;
      $ext_header = '';
      $ext_footer = '';
      $ext_title = '';
      try {
        $bookmark_id = 0;
        $tag = $this->request->query('tag');
        if( empty($tag) ){
          $bookmark_id = $this->request->query('content');
          if( empty($bookmark_id) ){
            throw new Exception("not found target", 1);
          }
        }
        
        
        $this->loadModel('Bookmark');
        $this->loadModel('BookmarkExtData');
        $this->loadModel('Link');
        $this->loadModel('Tag');
        
        if( $bookmark_id == 0){
          $tag_data = $this->Tag->find('first', array( 'conditions'=> array('tag' => $tag)) );
          if( empty($tag_data) ){
            throw new Exception("tag error", 2);
          }
          $tag_data = $tag_data['Tag'];
          $bookmark_id = $tag_data['bookmark_id'];
          if( !empty($bookmark_id) )
            $bookmark_data = $this->Bookmark->find('first', array( 'conditions'=> array('id' => $bookmark_id)) );
          $team_id = $tag_data['team_id'];
        }else{
          $bookmark_data = $this->Bookmark->find('first', array( 'conditions'=> array('id' => $bookmark_id)) );
          if( empty($bookmark_data) ){
            throw new Exception("content error", 2);
          }
          $team_id = $bookmark_data['Bookmark']['team_id'];
        }
        
        if( $bookmark_id == 0){
          $links = $this->Link->find('all', array( 'conditions'=> array('tag_id' => $tag_data['id'])) );
        }else{
          $links = $this->Link->find('all', array( 'conditions'=> array('bookmark_id' => $bookmark_id)) );
          $bk_ext_data = $this->BookmarkExtData->find('all', array( 'conditions'=> array('bookmark_id' => $bookmark_id)) );
          if( !empty($bk_ext_data) ){
            $bk_ext_data = $this->BookmarkExtData->removeArrayWrapper('BookmarkExtData', $bk_ext_data);
            foreach ($bk_ext_data as $key => $value) {
              if( $value['kind'] == BookmarkExtData::EXT_HEADER ){
                $ext_header = $value['ext_data'];
              }else if ( $value['kind'] == BookmarkExtData::EXT_FOOTER ){
                $ext_footer = $value['ext_data'];
              }else if ( $value['kind'] == BookmarkExtData::EXT_TITLE ){
                $ext_title = $value['ext_data'];
              }
            }
          }
        }
        
        $datas = array();
        
        if( !empty($links) ){
          
          $is_left = TRUE;
          foreach ($links as $key => $link) {
            $link = $link['Link'];
            if( $is_left ){
              $row_data = array();
            }
            
            if( substr($link['url'], 0,8) == 'content:' ){
              $tmp = explode(':', $link['url']);
              $content_id = $tmp[1];
              $link['url'] = $this->Link->GetURLByContentID($content_id);
            }
            $row_data[] = array('url'=>$link['url'],'link_text'=>$link['link_text'],'icon'=>'http://smartplate.pro/img/icon/'.$link['icon'].'.png');
            
            if( $is_left ){
              $is_left = FALSE;
            }else{
              $datas[] = $row_data;
              $is_left = TRUE;
            }
          }
          if( !$is_left ){
            $datas[] = $row_data;
          }
          
        }
        
        if( empty($bookmark_data['Bookmark']['image']) )
          $image = "http://smartplate.pro/icon/logo-smartplate.png";
        else
          $image = Bookmark::imageURL($bookmark_data['Bookmark']['image']);
          //$image = "http://smartplate.pro/upload/bookmark/".$bookmark_data['Bookmark']['image'];
        
        if( empty($bookmark_data['Bookmark']['name']) )
          $name = "";
        else
          $name = $bookmark_data['Bookmark']['name'];
        
        $data_json = json_encode($datas);
        
        print '{"status":{"code":0},"name":"'.$name.'","image":"'.$image.'","team":'.$team_id.',"tails":'.$data_json
                            .',"ext_header":"'.$ext_header.'","ext_footer":"'.$ext_footer.'","ext_title":"'.$ext_title
                            .'"}';
        
      }catch(exception $e){
          
        $err_message = $e->getMessage();
        $err_code = $e->getCode();
        print '{"status":{"code":"'.$err_code.'","message":"'.$err_message.'"}}';
      }
  }
}

?>