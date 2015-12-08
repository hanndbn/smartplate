<?php

App::uses('APIController', 'Controller');

define('NOT_FOUND_PARAMETAR', 'Parametar error.');
define('NOT_REGIST_PLATE', 'Not regist plate.');
define('ACTIVATED_PLATE', 'Activated plate.');
define('NOT_LOGIN', 'Not login.');
define('INVALID_ACTIVATION_CODE', 'Invalid activation code.');
define('ACTIVATION_FAILED', 'Activation Failed.');

class ActivateAPIController extends APIController {
    // ...

    public function process() {

        $data_dir = TMP . 'data/smart_plate/save_datas/';

        if (empty($this -> request -> query['tagurl'])) {
            throw new Exception("not found tag url", 5);
        }
        if (empty($this -> request -> query['mode'])) {
            $this -> request -> query['mode'] = 0;
        }

        $uuid = $this -> request -> query['i'];
        $local = $this -> request -> query['l'];
        $app = $this -> request -> query['a'];
        $tag_url = $this -> request -> query['tagurl'];
        $mode = $this -> request -> query['mode'];
        if (!empty($this -> request -> query['name'])) {
            $name = $this -> request -> query['name'];
        } else {
            $name = '';
        }

        $this -> loadModel('Tag');
        $this -> loadModel('TagHistory');
        $this -> loadModel('User');

        $tag_url_data = explode('/', $tag_url);

        $tag_id_base = $tag_url_data[count($tag_url_data) - 1];

        $tag_hrad = substr($tag_id_base, 0, 2);
        $tag_lot = substr($tag_id_base, 2, 5);
        $tag_num = substr($tag_id_base, 8);

        $tag_id = "$tag_hrad.$tag_lot.$tag_num";

        $res = $this -> Tag -> find('first', array('conditions' => array('tag' => $tag_id)));

        // Check Registed tag
        if (empty($res)) {
            throw new RuntimeException(ACTIVATION_FAILED, 6);
        }
        $tag_data = $res['Tag'];

        if ($mode == 2) {// only check
            if (empty($tag_data['team_id'])) {
                $is_activate = 0;
            } else {
                if ($tag_data['team_id'] == $this -> session['team_id']) {
                    $is_activate = 1;
                } else {
                    $is_activate = 2;
                    $this -> result['user']= $this->User->UserByTeam($tag_data['team_id']);
                }
            }
            $this -> result['status']['activate'] = $is_activate;
            return;

        } else {
            // Check Regitsed Activation Code
            if (empty($tag_data['activation_code'])) {
                throw new RuntimeException(ACTIVATION_FAILED, 9);
            }
        }

        $tag_history_data = array();
        $tag_history_data["tag_id"] = $tag_data['id'];
        $tag_last_limit_data = $this -> TagHistory -> GetLastLimitDateByTagID($tag_data['id']);


        // mode:1 deactivate
        if ($mode == 1) {
            // Check Team ID
            if (!empty($tag_data['team_id']) && $tag_data['team_id'] != $this -> session['team_id']) {
                throw new RuntimeException(INVALID_ACTIVATION_CODE, 8);
            }
            $tag_data['team_id'] = 0;
            $tag_data['activation_user'] = 0;
            $tag_data['bookmark_id'] = 0;
            $tag_data['name'] = '';

            $this -> loadModel('Link');
            $this -> Link -> DeleteByTagID($tag_data['id']);

            $tag_history_data["type"] = TagHistory::DEACTIVE;

            // mode:0 activate
        } else {

        
            if (empty($_REQUEST['acode'])) {
                throw new RuntimeException(NOT_FOUND_PARAMETAR, 4);
            }

            $activation_code = $this -> request -> query['acode'];

            // Check Activation Code
            if ($tag_data['activation_code'] != $activation_code) {
                throw new RuntimeException(INVALID_ACTIVATION_CODE, 11);
            }

            // Check Team ID
            if (!empty($tag_data['team_id']) && $tag_data['team_id'] != $this -> session['team_id']) {
                //print "$tag_data->team_id != $session_data->team_id";
                throw new RuntimeException(ACTIVATION_FAILED, 10);
            }
            
            if (!empty($tag_last_limit_data['limit_date']) && $app == 'sp') {
                if ($tag_last_limit_data['limit_date'] < date('Y-m-d H:i:s')) {
                    throw new RuntimeException("Expiration date has passed", 12);
                }
            }
            
            $tag_data['team_id'] = $this -> session['team_id'];
            $tag_data['activation_user'] = $this -> session['user_id'];
            $tag_history_data["type"] = TagHistory::ACTIVE;
            if (!empty($name)) { $tag_data['name'] = $name;
            }

            if (empty($tag_last_limit_data['limit_date']) && $app == 'sp') {
                $tag_history_data["limit_date"] = date('Y-m-d H:i:s', strtotime('+183 day'));
            }

        }
        $this -> Tag -> save($tag_data, false);
        $this -> TagHistory -> create($tag_history_data);
        $this -> TagHistory -> save();

    }

}
?>