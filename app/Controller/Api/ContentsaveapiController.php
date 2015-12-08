<?php

App::uses('APIController', 'Controller');
App::uses('Bookmark', 'Model');
App::uses('Links', 'Model');
App::uses('Label', 'Model');
App::uses('LableData', 'Model');

class ContentSaveAPIController extends APIController {
    // ...

    public function process() {

        $bookmark_id = $this->request->query('id');
        $label_ids = $this->request->query('label_ids');
        $name = $this->request->query('name');
        $image = $this->request->query('image');
        
        if (empty($image)) {
                throw new Exception("not found image", 16);
        }else{
            $image = "tmp/".$image.".png";
        }
        
        $ext_data =array();
        $ext_data['image'] = $image;

        $type = $this->request->query('type');
        if (empty($type)) {
            $type = 0;
        }
        // check for url without initiraize(type != 9)
        if ($type != 9) {
            $urls = $this->request->query('url');
            if (empty($urls)) {
                throw new Exception("not found url", 6);
            }else if (!is_array($urls) && count($urls) <= 0) {
                throw new Exception("invalid url datas", 7);
            }
        }

        // check for tiles data
        if ($type == 2) {
            $link_texts = $this->request->query('txt');
            if (empty($link_texts)) {
                throw new Exception("not found link text datas", 12);
            }else if (!is_array($link_texts)) {
                throw new Exception("invalid link text datas", 13);
            }

            $icons = $this->request->query('icon');
            if (empty($icons)) {
                throw new Exception("not found icon datas", 14);
            }else if (!is_array($icons) && count($icons) <= 0) {
                throw new Exception("invalid icon datas", 15);
            }

            if (count($urls) != count($link_texts) || count($urls) != count($icons)) {
                throw new Exception("The number of data of URL and ICON and LinkText is different.", 16);
            }
            $ext_data['icons'] = $icons;
            $ext_data['link_texts'] = $link_texts;
        }

        $this->loadModel('Bookmark');
        $this->loadModel('Link');
        $this->loadModel('Label');
        $this->loadModel('LabelData');

        if (empty($bookmark_id)) {
            $bookmark_id = $this->Bookmark->InsertBookmarkWithLink( $this->session['team_id'], $this->session['user_id'], $name, $urls, $type, $ext_data);
        } else {
            $bookmark_data = $this->Bookmark->find('first', array('conditions' => array('id' => $bookmark_id), 'fields' => array('id', 'team_id')));

            if (empty($bookmark_data)) {
                throw new Exception("not regist contents", 10);
            }
            if ($this->session['team_id'] != $bookmark_data['Bookmark']['team_id']) {
                throw new Exception("different team", 11);
            }
            $this->Bookmark->UpdateBookmark($bookmark_id, $this->session['team_id'], $this->session['user_id'], $name, $urls, $type, $ext_data);
        }

        if (is_array($label_ids)) {
            $this->Label->deleteLabelDataByTargetID($this->session['team_id'], Label::MODEL_BOOKMARK, $bookmark_id);
            foreach ($label_ids as $key => $label_id) {
                if ($label_id == -1) {
                    break;
                }
                if ($this->Label->hasLabelByID($this->session['team_id'], Label::MODEL_BOOKMARK, $label_id)) {
                    $this->LabelData->InsertLabelData($label_id, $bookmark_id);
                }
            }
        }
        $this->result['new_id'] = intval($bookmark_id);
    }

}
?>