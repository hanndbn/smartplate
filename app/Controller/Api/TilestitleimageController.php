<?php

App::uses('APIController', 'Controller');
App::uses('Image', 'Model');

define('NOT_FOUND_FILE_DATA', 'not found file data.');
define('INVALID_FILE_DATA', 'invalid file data.');
define('OTHER_ERROR', 'other error.');

class TilesTitleImageController extends APIController
{
    public function process()
    {
        $id = $this->request->query['id'];
        try {
            $this->loadModel('BookmarkExtData');
            if ($this->request->is('get')) {
                $bookmarkExtDataDto = $this->BookmarkExtData->find("first", array(
                    'fields' => array(
                        'ext_data'
                    ),
                    'conditions' => array(
                        'BookmarkExtData.bookmark_id' => $id,
                        'BookmarkExtData.kind' => BookmarkExtData::EXT_TITLE_HEADER_IMAGE
                    )
                ));
                if (isset($bookmarkExtDataDto['BookmarkExtData']['ext_data'])) {
                    $filename = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']) . '/upload/bookmark/' . $bookmarkExtDataDto['BookmarkExtData']['ext_data'];
                    if (file_exists($filename)) {
                        $bookmarkExtData = explode('\\', $bookmarkExtDataDto['BookmarkExtData']['ext_data']);
                        if (isset($bookmarkExtData[1]) && strtolower(pathinfo($bookmarkExtData[1], PATHINFO_EXTENSION)) == strtolower('PNG')) {
                            return;
                        } else {
                            throw new Exception(INVALID_FILE_DATA, "2");
                        }
                    } else {
                        throw new Exception(NOT_FOUND_FILE_DATA, "1");
                    }
                }
                return;
            }
            if ($this->request->is('post')) {
                $image = $this->request->form['image'];
                if(isset($image)){
                        $ImageModel = new Image();
                        $ImageModel->target_folder = 'bookmark';
                        $ImageModel->saveImage('titles', $image);
                }
            }
        } catch (exception $e) {
            throw new Exception(OTHER_ERROR, "3");
        }
    }
}

?>