<?php

App::uses('APIController', 'Controller');
App::uses('Tag', 'Controller');
App::uses('CustomValue', 'Controller');
App::uses('AdditionalElement', 'Controller');

class CustomDataAPIController extends APIController {
    // ...
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('index');
        $this->Auth->allow(array('controller' => 'customdataapi', 'action' => 'type'));
    }

    public function type() {
        $this->autoRender = false;
        try {
            if (empty($this->request->query['pi'])) {
                throw new Exception("need palte id.", 1);
            }
            if (empty($this->request->query['ut'])) {
                throw new Exception("need user token.", 2);
            }
            if (empty($this->request->query['at'])) {
                throw new Exception("need action type.", 3);
            }
            if (empty($this->request->query['tk'])) {
                throw new Exception("need API Token.", 4);
            }

            if (!preg_match('/^([A-Za-z][A-Za-z]).\d{5}\.\d{7}$/', $this->request->query['pi'])) {
                throw new Exception("Invalid plate.", 7);
            }

            $tag = $this->request->query['pi'];
            $user_token = $this->request->query['ut'];
            $action = $this->request->query['at'];
            $api_token = $this->request->query['tk'];

            $bookmark = 0;
            $values = array();

            $this->loadModel('Tag');
            $this->loadModel('CustomValue');

            // get bookmark id.
            $options = array();
            $options['conditions'] = array('Tag.tag' => $tag);

            $tag_data = $this->Tag->find('first', $options);

            if (empty($tag_data)) {
                throw new Exception("not found plate data.", 5);
            }

            if (!empty($this->request->query['val'])) {
                $values = $this->request->query['val'];
            } elseif ( !empty($this->request->data['val'])){
                $values = $this->request->data['val'];
            }
            if (! is_array($values)) {
                throw new Exception("invalid custom value.", 6);
            }
            $values['action'] = $action;
            
            $this->CustomValue->InsertValue($tag_data['Tag'], $user_token, $values);

            echo '{"status":{"code":"0","message":""}}';

        } catch( exception $e ) {
            $mess = $e->getMessage();
            $code = $e->getCode();
            echo '{"status":{"code":' . $code . ',"message":"' . $mess . '"}}';
        }

    }

    public function process() {

        if ($this->request->is('get')) {
            date_default_timezone_set('UTC');

            $this->loadModel('CustomValue');
            $this->loadModel('User');

            $app = $this->request->query('a');
            $_tag = $this->request->query('tag');
            $_cid = $this->request->query('content_id');
            $start = $this->request->query('start');
            $end = $this->request->query('end');
            
            $tag_obj = NULL;
            $bookmark_obj = NULL;
            
            if( $app != 'cf' ){
                throw new Exception("Invalid Application.", 2);
            }
            
            $options = array();
            if( !empty($_tag) ){
                $this->loadModel('Tag');
                $options['conditions'] = array('Tag.tag' => $_tag);
                $tag_obj = $this->Tag->find('first', $options);
            } else if ( !empty($_cid) ) {
                $this->loadModel('Bookmark');
                $options['conditions'] = array('Bookmark.id' => $_cid);
                $bookmark_obj = $this->Bookmark->find('first', $options);
            } 

            if ($start) {
                $start = date('Y-m-d H:i:s', strtotime($start));
            }else
                $start_hour =  NULL;
            
            if ($end) {
                $end = date('Y-m-d H:i:s', strtotime($end));
            }else
                $end =  NULL;
            
            $user = $this->User->findById($this->session['user_id']);
            if( $user['User']['power'] >= 100 ){
                $user_id = $this->session['user_id'];
            }else{
                $user_id = 0;
            }
            $res = $this->CustomValue->GetValue($this->session['team_id'],$user_id,$tag_obj['Tag'], $bookmark_obj['Bookmark'], $start,$end);
            
            $custom_data = $res;
            
            $this->result['analytics'] = array( "action" => array(),
                                                "action_total" => 0,
                                                "custom" => $custom_data);
                                                
        }
    }

}
?>