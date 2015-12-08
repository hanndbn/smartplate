<?php
/**
 * ConvertAPIController
 *
 * Use convert from App data to cloud DB data
 */

App::uses('APIController', 'Controller');
App::uses('Bookmark', 'model');
App::uses('Tag', 'model');
App::uses('Label', 'model');
App::uses('LabelData', 'model');
App::uses('Links', 'model');
App::uses('User', 'model');
App::uses('AccessLog', 'model');

function get_bookmark_id($item, $key, &$prefix) {
    $res = 0;
    if ($item['url'] == $prefix['url']) {
        $prefix['bookmark_id'] = $item['bookmark_id'];
    }
}

class ConvertAPIController extends APIController {
    // ...

    public function convertLog($team_id) {
        
        $options = array(   'fields' => array(  'DISTINCT(Bookmark.id)', 'Bookmark.team_id', 'Link.url'),
                                                'joins' => array(   array(  'type' => 'LEFT', 
                                                                            'table' => 'links', 
                                                                            'alias' => 'Link', 
                                                                            'conditions' => array('Link.bookmark_id = Bookmark.id'))) );

        $options['conditions'] = array('Bookmark.team_id' => $team_id );

        $data_array = $this->Bookmark->find('all', $options);
        
        $this->loadModel('AccessLog');
        
        foreach ($data_array as $key => $value) {
            $bookmark_data = $value['Bookmark'];
            $link_data = $value['Link'];
            if (!$this->AccessLog->updateAll(array('bookmark_id' => $bookmark_data['id']), array('team_id' => $team_id, 'contents' => $link_data['url'], 'bookmark_id' => 0))) {
                throw new Exception("ipdate bookmark id faild", 14);
            }
        }
    }

    public function process() {

        $this->loadModel('Bookmark');
        $this->loadModel('Tag');
        $this->loadModel('Label');
        $this->loadModel('LabelData');
        $this->loadModel('Links');
        $this->loadModel('User');
        
        if (User::isConvertedUser($this->session['user_id'])) {
           // $this->log('error:12 user id:'.$this->session['user_id'],LOG_DEBUG);
           // throw new Exception("already converted user", 12);
        }
        $json_str = $this->request->data('datas');
    
        if (empty($json_str)) {
            throw new Exception("not found data", 1);
        }

        if (!is_string($json_str)) {
            throw new Exception("invalid data", 3);
        }

        $data = json_decode($json_str, true);

        if (empty($data)) {
            $this->log('error:4 json:'.$json_str,LOG_DEBUG);
            throw new Exception("invalid data", 4);
        }
        if (empty($data["tables"])) {
            $this->log($data,LOG_DEBUG);
            throw new Exception("invalid data", 5);
        }

        $data = $data["tables"];

        $datasource = $this->Bookmark->getDataSource();
        try {

            $datasource->begin();
            // Convert Bookmark data
            $bookmark_convert_ids = array();
            if (!empty($data["bookmark"])) {
                foreach ($data["bookmark"] as $key => $value) {
                    $this->Bookmark->create();
                    $cdate = date("Y-m-d H:i:s", strtotime($value['cdate']));
                    $image = 'tmp/'.$value['image'].'.png';
                    if (!$this->Bookmark->save(array('team_id' => $this->session['team_id'], 'visible' => 1, 'name' => $value['name'], 'image' => $image, 'cdate' => $cdate))) {
                        $this->log(array('team_id' => $this->session['team_id'], 'visible' => 1, 'name' => $value['name'], 'image' => $image, 'cdate' => $cdate),LOG_DEBUG);
                        throw new Exception("save content data faild", 6);
                    }

                    $server_bookmark_id = $this->Bookmark->getLastInsertId();
                    $local_bookmark_id = $data["bookmark"][$key]['bookmark_id'];

                    $bookmark_convert_ids["$local_bookmark_id"] = $server_bookmark_id;
                    
                    // create links recode
                    $this->Links->create();
                    $links_tmp_data = array();
                    $links_tmp_data['tag_id'] = 0;
                    $links_tmp_data['bookmark_id'] = $server_bookmark_id;
                    $links_tmp_data['type'] = 0;
                    $links_tmp_data['sub_type'] = 0;
                    $links_tmp_data['icon'] = 0;
                    $links_tmp_data['url'] = $value['url'];
                    $links_tmp_data['udate'] = date("Y-m-d H:i:s");
                    $links_tmp_data['cdate'] = date("Y-m-d H:i:s");

                    if (!$this->Links->save($links_tmp_data) ) {
                        $this->log($links_tmp_data,LOG_DEBUG);
                        throw new Exception("save links data faild", 13);
                    }
                }
            }

            if (!empty($data["label"])) {
                foreach ($data["label"] as $key => $value) {
                    if ($value["label"] != '') {
                        $this->Label->create();
                        $cdate = date("Y-m-d H:i:s", strtotime($value['cdate']));
                        if ($this->Label->save(array('team_id' => $this->session['team_id'], 'type' => Label::MODEL_BOOKMARK, 'level' => 0, 'parent_id' => 0, 'label' => $value['label'], 'cdate' => $cdate))) {
                            $server_label_id = $this->Label->getLastInsertId();
                        } else {
                            $server_label = $this->Label->find('first', array('conditions' => array('team_id' => $this->session['team_id'], 'type' => Label::MODEL_BOOKMARK, 'level' => 0, 'parent_id' => 0, 'label' => $value['label'])));
                            if (empty($server_label)) {
                                $this->log("save label data faild",LOG_DEBUG);
                                throw new Exception("save label data faild", 7);
                            }
                            $server_label_id = $server_label['Label']['id'];
                        }
                        $target_bookmark_id = $bookmark_convert_ids[$value["link_id"]];

                        if ($server_label_id != NULL && $target_bookmark_id != NULL) {
                            $this->LabelData->create();
                            if (!$this->LabelData->save(array('label_id' => $server_label_id, 'target_id' => $target_bookmark_id))) {
                                $this->log(array('code' => 8,'label_id' => $server_label_id, 'target_id' => $target_bookmark_id),LOG_DEBUG);
                                throw new Exception("save label data faild", 8);
                            }
                        }
                    }
                }
            }

            // Convert Link data
            if (!empty($data["links"])) {
                $converted_tag_ids = array();
                $server_links_data = $this->Links->find('all', array('conditions' => array('user_id' => $this->session['user_id'], 'bookmark_id' => 0)));
                foreach ($server_links_data as $key => $value) {
                    if ($value["Links"]["type"] > 0) {
                        // create bookmark recode

                        if (array_search($value["Links"]['tag_id'], $converted_tag_ids) !== FALSE) {
                            continue;
                        }
                        $this->Bookmark->create();
                        $this->Bookmark->save(array('team_id' => $this->session['team_id'], 'visible' => 1, 'name' => 'Convert from App'));
                        $new_id = $this->Bookmark->getLastInsertId();
                        if (!$this->Links->updateAll(array('bookmark_id' => $new_id, 'udate' => "'" . date("Y-m-d H:i:s") . "'"), array('tag_id' => $value["Links"]['tag_id']))) {
                            throw new Exception("save link data faild", 9);
                        }

                        // remember converted id
                        $converted_tag_ids[] = $value["Links"]['tag_id'];

                    } else {
                        // update bookmark_id on links recode
                        $this->Links->create();
                        array_walk($data["bookmark"], 'get_bookmark_id', &$value["Links"]);
                        $value["Links"]['bookmark_id'] = $bookmark_convert_ids[$value["Links"]['bookmark_id']];
                        $value["Links"]['udate'] = date("Y-m-d H:i:s");

                        if (!$this->Links->updateAll(array('bookmark_id' => $value["Links"]['bookmark_id'], 'udate' => "'" . date("Y-m-d H:i:s") . "'"), array('tag_id' => $value["Links"]['tag_id']))) {
                            $this->log(array('code' => 10,'bookmark_id' => $value["Links"]['bookmark_id'], 'udate' => "'" . date("Y-m-d H:i:s") . "'"),LOG_DEBUG);
                            throw new Exception("save plate data faild", 10);
                        }
                    }
                }
            }

            // Convert Tag data
            $tag_convert_ids = array();
            if (!empty($data["tag"])) {
                foreach ($data["tag"] as $key => $value) {
                    $tag_data = $this->Tag->find('first', array('conditions' => array('tag' => $value['tag'])));

                    if ($tag_data['Tag']['team_id'] == $this->session['team_id']) {
                        $links_data = $this->Links->find('first', array('conditions' => array('user_id' => $this->session['user_id'], 'tag_id' => $tag_data['Tag']['id'])));
                        if (!empty($links_data)) {
                            if (!empty($value['name'])) {
                                $tag_data['Tag']['name'] = $value['name'];
                            }else{
                                $tag_data['Tag']['name'] = 'undefined';
                            }
                            $tag_data['Tag']['bookmark_id'] = $links_data['Links']['bookmark_id'];
                            $tag_data["Tag"]['cdate'] = date("Y-m-d H:i:s");

                            if (!$this->Tag->save($tag_data['Tag'])) {
                                $this->log($tag_data['Tag'],LOG_DEBUG);
                                throw new Exception("save plate data faild", 11);
                            }
                        }
                    }
                }
            }
            $datasource->commit();
            User::converted($this->session['user_id']);
            
            $this->convertLog($this->session['team_id']);
        } catch (exception $e) {
            $datasource->rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}
?>