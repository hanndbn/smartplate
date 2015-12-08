<?php
App::uses('AppModel', 'Model');

/**
 * Application model for device table.
 *
 * @package       app.Model
 */
class Image extends AppModel 
{    
  public $target_folder = 'tmp';
    
  const image_max_height = 800;
  const image_max_width  = 800;
  const image_thumb_height = 200;
  const image_thumb_width  = 200;
  
  public function operation_thumb_image($img,$team_id,$id,$w, $h) {
    
    if ($h < static::image_thumb_height && $w < static::image_thumb_width) {
      $dst = $img;
      imagealphablending($dst, false);
      imagesavealpha($dst, true);
    }
    else {
      $dx = (float)static::image_thumb_width / (float)$w;
      $dy = (float)static::image_thumb_height / (float)$h;
   
      if ($dx < $dy) {
        $dw = $w*$dx;
        $dh = $h*$dx;
      } else  {
        $dw = $w*$dy;
        $dh = $h*$dy;
      }
      
      $dst = imagecreatetruecolor($dw, $dh);
      if (!$dst ) { return false; }
    
      imagealphablending($dst, false);
      imagesavealpha($dst, true);
      if (!$dst || !imagecopyresampled($dst, $img, 0, 0, 0, 0, $dw, $dh, $w, $h)) {
        return false;
      }
    }
    
    $filebody = "th$id.png";
    $thumb = WWW_ROOT . 'upload' . DS . $this->target_folder . DS . $team_id . DS . $filebody;
    if (!file_exists(dirname($thumb))) {
      if (!mkdir(dirname($thumb), 0755, true)) {
        return false;
      }
    }
    if (!imagepng($dst, $thumb)) {
      return false;
    }
    return $filebody;
  }

  public function saveImage($team_id, $img_obj) {
    
    $id= md5(time()) ;
    
    $img = $img_obj;
    if (!$img || $img['error'] || !preg_match('!^image/(jpeg|gif|png)!', $img['type'])) {
      return '';
    }
    $fn = $img['tmp_name'];
    switch ($img['type']) {
      case 'image/jpeg':
        $img = imagecreatefromjpeg($fn);
        break;
      case 'image/png':
        $img = imagecreatefrompng($fn);
        break;
      case 'image/gif':
        $img = imagecreatefromgif($fn);
        break;
    }
    if (!$img) {
      return '';
    }
    
    list($w, $h) = getimagesize($fn);
    if ($h < static::image_max_height && $w < static::image_max_width) {
      $dst = $img;
      imagealphablending($dst, false);
      imagesavealpha($dst, true);
    }
    else {
      $dx = (float)static::image_max_width / (float)$w;
      $dy = (float)static::image_max_height / (float)$h;
    
      if ($dx < $dy) {
        $dw = $w*$dx;
        $dh = $h*$dx;
      } else  {
        $dw = $w*$dy;
        $dh = $h*$dy;
      }
    
      $dst = imagecreatetruecolor($dw, $dh);
      if (!$dst ) { return ''; }
     
      imagealphablending($dst, false);
      imagesavealpha($dst, true);
      if (!$dst || !imagecopyresampled($dst, $img, 0, 0, 0, 0, $dw, $dh, $w, $h)) {
        return '';
      }
    }
    $filebody = "$id.png";
    $fn = WWW_ROOT . 'upload' . DS . $this->target_folder . DS . $team_id . DS . $filebody;
    if (!file_exists(dirname($fn))) {
      if (!mkdir(dirname($fn), 0755, true)) {
        return '';
      }
    }
    if (!imagepng($dst, $fn)) {
      return '';
    }
  
    $this->operation_thumb_image($img,$team_id,$id,$w, $h);
  
    return $team_id . DS . $filebody;
  }
}

