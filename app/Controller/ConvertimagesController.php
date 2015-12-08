<?php

App::uses('AppController', 'Controller');
App::uses('Bookmark', 'Model');
App::uses('Image', 'Model');

class ConvertImagesController extends AppController {
  // ...
  public function beforeFilter() {
     parent::beforeFilter();
        $this->Auth->allow(array('controller' => 'convertimages', 'action' => 'index'));
  }   
  
  public function index(){

      $this->autoRender = false;
      
    try {
      $this->loadModel('Bookmark');
      $bookmark_data = $this->Bookmark->find('all', array( 'fields'=> array('id','image','team_id')));
      $bookmark_data = $this->Bookmark->removeArrayWrapper('Bookmark', $bookmark_data);
      
      foreach ($bookmark_data as $key => $value) {
        $image_name = $value['image'];
        $team_id = $value['team_id'];
        if( !empty($image_name) ){
          
          $tmp = explode('/', $image_name);
          $filename = array_pop($tmp);
          $thumb_fileName = 'th'.$filename;
          $thumb_image_name = str_replace($filename, $thumb_fileName, $image_name);
          
          $folder = WWW_ROOT . 'upload' . DS . 'bookmark' . DS . $team_id . DS ;
          $new_filename = $folder. $filename;
          $new_thum_filename = $folder.$thumb_fileName;
          
          if (strpos($image_name,'d.carryfree.kokope.li')!==false){
            if (!file_exists(dirname($new_filename))) {
              if (!mkdir(dirname($new_filename), 0755, true)) {
                throw new Exception("Error make folder", 1);
              }
            }
            $source_folder = '/var/www/virtual/d.carryfree.kokope.li/httpdocs/image/';
            if (file_exists($source_folder.$filename) && !file_exists($new_filename) ) {
                copy($source_folder.$filename,$new_filename);
                if (file_exists($source_folder.$thumb_fileName)) {
                      copy($source_folder.$thumb_fileName,$new_thum_filename);
                }
            }else{
                $filename = '';
            }
            
            $value['image'] = $team_id . DS . $filename;
            $this->Bookmark->save($value);
          }else if (strpos($image_name,'userdata/image')!==false){
            $image_name = str_replace('userdata/image/', '', $image_name);
            $value['image'] = $image_name;
            $this->Bookmark->save($value);
          }
           // print "$image_name >> $new_filename<br/>";
        }
      }
      
    }catch(exception $e){
      $err_message = $e->getMessage();
      $err_code = $e->getCode();
      print '{"status":{"code":"'.$err_code.'","message":"'.$err_message.'"}}';
    }
  }
}

?>