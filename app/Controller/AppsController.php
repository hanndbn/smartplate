<?php

App::uses('AppController', 'Controller');

/**
 * Controller for App model
 * 
 * @package       app.Controller
 * 
 */
class AppsController extends AppController {

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('AccessLog');
        $this->loadModel('User');
        $this->loadModel('Master');
        $this->loadModel('Label');
        $this->loadModel('Link');
        $this->loadModel('Tag');
        $this->Auth->deny();
    }

    /**
     * Build a URL will all the search elements in it
     */
    function filter() {
        // the page we will redirect to
        $url['action'] = 'index';

        // build a URL will all the search elements in it
        // the resulting URL will be
        // example.com/cake/posts/index/Search.keywords:mykeyword/Search.tag_id:3
        foreach ($this->data as $k => $v) {
            foreach ($v as $kk => $vv) {
                $url[$k . '.' . $kk] = $vv;
            }
        }

        // redirect the user to the url
        $this->redirect($url, null, true);
    }

    /**
     * Display App's label
     */
    public function label() {

        $input = $this->request->data;

        if ($this->request->is('post')) {
            $options = array(
                'order' => array('display_order' => 'asc'),
                'conditions' => array('type' => 'UserModel')
            );

            if (isset($input['label_status']) && $input['label_status'] != '') {
                $options['conditions']['AND'] = array('status' => $input['label_status']);
            }

            if (!empty($input['search']) && $input['search'] != '') {
                $options['conditions']['OR'] = array(
                    array('label LIKE' => '%' . str_replace('%', '\%', $input['search']) . '%'),
                );
            }

            $labels = $this->Label->find('all', $options);
            $labels = $this->Label->removeArrayWrapper('Label', $labels, 'id');
            // Search for parent label
            $labels += $this->Label->findAllParents($labels);
        } else {
            $labels = $this->Label->getLabelsArray('UserModel');
        }

        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        $labelscount = $this->Label->query('
            SELECT label_id, count(*) AS total 
            FROM label_datas 
            WHERE label_id in (SELECT id FROM label WHERE type = \'UserModel\') 
            GROUP BY label_id'
        );

        $count = array();

        foreach ($labelscount as $label) {
            $count[$label['label_datas']['label_id']] = array(
                'id' => $label['label_datas']['label_id'],
                'total' => $label[0]['total']
            );
        }

        $this->set(array(
            'labels' => $labels,
            'count' => $count,
            'type' => 'UserModel',
        ));

        return $this->render('/Labels/index');
    }

    /**
     * Show list app user information
     */
    public function index() {
        $input = $this->request->data;
        /* Add filter */
        //List Label
        $labels = $this->Label->getLabelsArray('UserModel');
        $this->set('labels', $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels)));

        // Filter
        $conditions = array();
        //Transform POST into GET
        if (($this->request->is('post') || $this->request->is('put')) && isset($this->data['Filter'])) {
            $filter_url['controller'] = $this->request->params['controller'];
            $filter_url['action'] = $this->request->params['action'];
            // We need to overwrite the page every time we change the parameters
            $filter_url['page'] = 1;

            // for each filter we will add a GET parameter for the generated url
            if (isset($this->data['Filter'])) {
                foreach ($this->data['Filter'] as $name => $value) {
                    if ($value != trim('')) {
                        // You might want to sanitize the $value here
                        // or even do a urlencode to be sure
                        $filter_url[$name] = Utility_Str::escapehtml($value);
                    }
                }
            }
            // now that we have generated an url with GET parameters, 
            // we'll redirect to that page
            $this->Session->write('Access.filter.app', $filter_url);
            return $this->redirect($filter_url);
        } else {
            // Inspect all the named parameters to apply the filters
            $joins = array();
            $need_clear_fileter = true;
            foreach ($this->params['named'] as $param_name => $value) {
                $value = Utility_Str::returnhtml($value);
                // Don't apply the default named parameters used for pagination
                if (!in_array($param_name, array('page', 'sort', 'direction', 'limit'))) {
                    // You may use a switch here to make special filters
                    // like "between dates", "greater than", etc
                    if ($param_name == "name") {
                        $conditions += array(
                            array('User.name LIKE' => '%' . str_replace('%', '\%', $value) . '%'),
                        );
                    } else {
                        $joins = array(
                            array(
                                'table' => 'label_datas',
                                'alias' => 'lbd',
                                'type' => 'INNER',
                                'conditions' => array('User.id = lbd.target_id')
                            )
                        );
                        $conditions += array('lbd.label_id' => $value);
                    }

                    $this->request->data['Filter'][$param_name] = $value;
                    $need_clear_fileter = false;
                }
            }
            if( $need_clear_fileter ){
              $this->Session->write('Access.filter.app', '');
            }
        }
        $this->User->recursive = 0;
		$team_id = Configure::read('teamId');
        $conditions += array('User.team_id' => $team_id);
        $this->paginate = array(
            'limit' => 20,
            'joins' => $joins,
            'conditions' => $conditions,
            'order' => array('id' => 'asc')
        );

        // Pass the search parameter to highlight the text
        $this->set('name', isset($this->params['named']['name']) ? $this->params['named']['name'] : "");

        /* end filter */

        $users = $this->paginate('User');

        //var_dump($users);die;

        $count = array();
        if ($users) {
            $this->loadModel('Link');       //add geo
            $this->loadModel('Tag');       //add geo
            $this->loadModel('AccessLog');	//

            $users = $this->User->removeArrayWrapper('User', $users, 'id');

            $concat = 'CONCAT(access_log.p_head, ".", access_log.p_lot, ".", access_log.p_num)';
            
            foreach ($users as &$user) {
                $id = $user['id'];

                //$this->User->Device->recursive = -1;
                //$devices = $this->User->Device->find('all', array(
                //    'conditions' => array('Device.user_id' => $id),
                //));
                //$user['devices'] = $this->User->removeArrayWrapper('Device', $devices, 'id');
                
                $tags = $this->Tag->find('all', array(
                    'fields' => array('tag'),
                    'conditions' => array('Tag.activation_user' => $id),
                ));
                $tags = $this->Tag->removeArrayWrapper('Tag', $tags);

                $tag_string = '';
                foreach ($tags as $key => $value) {
                    if( $tag_string != '' ) { $tag_string .= ','; }
                    $tag_id = $value['tag'];
                    $tag_string .= "'$tag_id'";
                }
                $count[$id] = array( 'id'=>$id,'total'=>count($tags));
                // Get plate access_log
                
				if( $tag_string != ''){
				    $tag_sql = " AND $concat in ($tag_string)"; 
                
    				// change geo >>
                    $accs = $this->AccessLog->query("
                        SELECT COUNT($concat) as acc_count 
                        FROM access_log 
                        WHERE team_id = $team_id".$tag_sql
                    );
    
                    foreach ($accs as $acc) {
                        $user['access'] = $acc[0]['acc_count'];
                    }
                } else{
                    $user['access'] = 0;
                }
                // Get app's label                 
                $labels = $this->Label->LabelData->find('all', array(
                    'conditions' => array('Label.type' => 'UserModel', 'LabelData.target_id' => $id),
                    'fields' => 'Label.label, Label.id'
                ));

                $user['labels'] = $this->User->removeArrayWrapper('Label', $labels, 'id');
            };
        }
        $this->set('users', $users);
/*
        $platesCount = $this->Link->query('
            SELECT user_id, count(DISTINCT tag.id) AS total 
            FROM links 
            LEFT JOIN tag ON (tag.id = links.tag_id)
            WHERE tag.team_id ='. $this->Auth->user('team_id'). '
            GROUP BY user_id'
        );


        foreach ($platesCount as $plate) {
            $count[$plate['links']['user_id']] = array(
                'id' => $plate['links']['user_id'],
                'total' => $plate[0]['total']
            );
        }*/
        $total = $this->User->find('count', array(
            'conditions' => array(
                'team_id' => $this->Auth->user('team_id')
            )
        ));
        $this->set(array(
            'count' => $count,
            'total' => $total
        ));
    }

    /**
     * Show user detail
     * 
     * @param int $id user id
     * @return array 
     */
    public function detail($id = null) {
        $this->_setModalLayout();

        $id = intval($id);

        if (!$id) {
            $this->Session->setFlash(__('アプリアカウントのIDを提供してください。'), 'alert-box', 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }

        /* Get user */

        $user = $this->User->findById($id);
        if (!$user) {
            $this->Session->setFlash(__('アカウントのIDは無効です。'), 'alert-box', 'alert-box', array('class' => 'alert-danger'));
            $this->redirect($this->referer());
        }

        $labels = $this->Label->label_Query($id, 'UserModel');

        $this->set(array(
            'user' => $user['User'],
            'labels' => $labels
        ));
    }

    /**
     * Update user record
     * 
     * @param int $id user id
     */
    public function edit($id = null) {
        $this->_setModalLayout();
        $id = intval($id);

        if (!$id) {
            $this->Session->setFlash(__('アカウントのIDを提供してください。'), 'alert-box', 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }

        $user = $this->User->findById($id);
        if (!$user) {
            $this->Session->setFlash(__('アプリアカウントのIDは無効です。'), 'alert-box', 'alert-box', array('class' => 'alert-danger'));
            $this->redirect($this->referer());
        }

        $labels = $this->Label->getLabelsArray('UserModel');
        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        /* Get bookmark's label ids */
        $currentLabels = $this->Label->label_id_Query($id, 'UserModel');

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->User->id = $id;

            $userInput = $this->request->data['User'];
            $labelInput = $this->request->data['Label'];

            $userInput['team_id'] = $labelInput['team_id'] = $this->Auth->user('team_id');

            if ($userInput['password'] == '') {
                unset($userInput['password']);
            } else {
                $userInput['password'] = $this->Auth->password($userInput['password']);
            }

            if ($labelInput['add_new_text'] != null) {
                $labelInput['parent_id'] = !empty($labelInput['new_label']) ? $labelInput['new_label'] : 0;
                $labelInput['type'] = 'UserModel';
                $labelInput['label'] = $labelInput['add_new_text'];
                $labelInput['cdate'] = date('Y-m-d H:i:s');
                if ($this->Label->save($labelInput)) {
                    // Get new ID record
                    $newId = $this->Label->getLastInsertId();
                } else {
                    $this->Session->setFlash(__('Can not create new label'), 'alert-box', array('class' => 'alert-danger'));
                    $this->redirect(array('action' => 'index'));
                }
            }
            if ($labelInput['new_label'] != null) {
                if ($labelInput['add_new_text'] == null) {
                    if (isset($this->request->data['_label'])) {
                        if (in_array($labelInput['new_label'], $this->request->data['_label'])) {
                            $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                            $this->redirect(array('action' => 'index'));
                        }
                    }
                }
            }

            if (!empty($this->request->data['_label'])) {
                // Update exited label
                $own_labels = $this->request->data['_label'];
                if (count($own_labels) != count(array_unique($own_labels))) {
                    $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                    $this->redirect(array('action' => 'index'));
                } else {
                    foreach ($own_labels as $key => $value) {
                      if( $value >= 0){
                        $this->Label->LabelData->query("UPDATE label_datas SET label_id = {$value} WHERE target_id = {$id} AND label_id = {$key}");
                      } else {
                        $this->Label->LabelData->query("DELETE FROM label_datas WHERE target_id = {$id} AND label_id = {$key}");
                      }
                    }
                }
            }
            $userInput['cdate'] = date('Y-m-d H:i:s');
            unset($userInput['power']);
            
            if ($this->User->save($userInput)) {
                // Check duplicate label_datas
                $check_label_datas = $this->Label->LabelData->find('all', array(
                    'conditions' => array('target_id' => $id),
                    'fields' => array('label_id')
                ));

                $labelData = $this->Label->removeArrayWrapper('LabelData', $check_label_datas, 'label_id');

                $ids = array_keys($labelData);
                $date = date('Y-m-d H:i:s');
                if (!empty($newId) && !in_array($newId, $ids)) {
                    $this->Label->LabelData->save(array(
                        'target_id' => $this->User->id,
                        'cdate' => "'{$date}'",
                        'label_id' => $newId
                    ));
                }

                if (!empty($labelInput['new_label']) && !in_array($labelInput['new_label'], $ids)) {
                    $this->Label->LabelData->save(array(
                        'target_id' => $this->User->id,
                        'cdate' => "'{$date}'",
                        'label_id' => $labelInput['new_label']
                    ));
                }

                $this->Session->setFlash(__('アカウントを保存しました。'), 'alert-box', array('class' => 'alert-success'));
            }

           $filter_url = $this->Session->read('Access.filter.app');
           if( empty($filter_url) ){
              $this->redirect(array('action' => 'index'));
           }else{
              $this->redirect($filter_url);
           }
           // return $this->redirect(array('action' => 'index'));
        }

        if (!$this->request->data) {
            $this->request->data = $user;
        }

        $this->set(array(
            'user' => $user,
            'labels' => $labels,
            'currentLabels' => $currentLabels
        ));
    }

    /**
     * Add new user record
     */
    public function add() {
        $this->_setModalLayout();

        $labels = $this->Label->getLabelsArray('UserModel');
        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        $this->set('labels', $labels);

        if ($this->request->is('post')) {

            $userInput = $this->request->data['User'];
            $labelInput = $this->request->data['Label'];

            if ($existingUser = $this->User->findByLoginName($userInput['login_name'])) {
                $this->Session->setFlash(__('このアカウントは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                return $this->redirect($this->referer());
            }


            $userInput['team_id'] = $labelInput['team_id'] = $this->Auth->user('team_id');
            $userInput['password'] = $this->Auth->password($userInput['password']);

            if ($labelInput['add_new_text'] != null) {
                $labelInput['parent_id'] = !empty($labelInput['new_label']) ? $labelInput['new_label'] : 0;
                $labelInput['type'] = 'UserModel';
                $labelInput['label'] = $labelInput['add_new_text'];
                $labelInput['cdate'] = date('Y-m-d H:i:s');
                $existing = $this->Label->find('all', array('conditions' => array(
                        'label' => $labelInput['add_new_text'],
                        'team_id' => $labelInput['team_id'],
                        'type' => $labelInput['type'],
                )));
                if (empty($existing)) {
                    if ($this->Label->save($labelInput)) {
                        // Get new ID record
                        $newId = $this->Label->getLastInsertId();
                    } else {
                        $this->Session->setFlash(__('新規ラベルを作成することはできません。'), 'alert-box', array('class' => 'alert-danger'));
                        $this->redirect(array('action' => 'index'));
                    }
                } else {
                    $this->Session->setFlash(__('このラベルは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                    return $this->redirect($this->referer());
                }
            }
            $date = date('Y-m-d H:i:s');
            if ($this->User->save($userInput)) {
                if (!empty($labelInput['new_label']) || isset($newId)) {
                    $this->Label->LabelData->save(array(
                        'target_id' => $this->User->getLastInsertId(),
                        'label_id' => isset($newId) ? $newId : $labelInput['new_label'],
                        'cdate' => "'{$date}'"
                    ));
                }

                $this->Session->setFlash(__('アカウントを保存しました。'), 'alert-box', array('class' => 'alert-success'));
            }

            return $this->redirect(array('action' => 'index'));
        }

        return $this->render('edit');
    }

    /**
     * Update user's label record
     */
    public function ajaxLabel() {
        $this->_setModalLayout();
        if (isset($_REQUEST['id'])) {
            $target_id = $_REQUEST['id'];
            //Get List Label Hierachi
            $labels = $this->Label->getLabelsArray('UserModel');
            $listLabelHierachy = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

            $this->set(array(
                'labels' => $listLabelHierachy,
                'target_id' => $target_id
            ));
            return $this->render('ajax_label', 'ajax');
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            if (isset($this->request->data['User'])) {
                $rq_label = $this->request->data['User'];
                $label_ids = explode(',', $rq_label['label_id']);
                $target_ids = explode(',', $rq_label['target_id']);
                $old_label = $this->Label->label_id_Query($target_ids, 'UserModel');
                // Delete old records
                $this->Label->LabelData->deleteAll(array('target_id' => $target_ids, 'label_id' => $old_label), false);

                // Insert new records
                foreach ($label_ids as $label_id) {
                    foreach ($target_ids as $target_id) {
                        $this->Label->LabelData->save(array('label_id' => $label_id, 'target_id' => $target_id, 'cdate' => date('Y-m-d H:i:s')));
                    }
                }

                $this->Session->setFlash(__('アカウントを保存しました。'), 'alert-box', array('class' => 'alert-success'));
                $this->redirect(array('controller' => 'apps', 'action' => 'index'));
            }
        }

        return $this->redirect(array('controller' => 'apps', 'action' => 'index'));
    }

    /**
     * Update user's name record
     */
    public function ajaxName() {
        $this->autoRender = false;

        if ($this->request->is('ajax')) {
            $input = $this->request->data;

            if (!empty($input['id']) && !empty($input['name'])) {
                $now = date('Y-m-d H:i:s');
                $name = $input['name'];
                $this->User->updateAll(array('name' => "'$name'", 'cdate' => "'$now'"), array('id' => $input['id']));

                $this->Session->setFlash(__('アカウントを保存しました。'), 'alert-box', array('class' => 'alert-success'));

                echo json_encode(array(
                    'success' => 1,
                    'redirect' => 1
                ));
                exit;
            }
        }

        return $this->redirect('/');
    }

    /**
     * Update user's status record
     */
    public function ajaxStatus() {
        $this->autoRender = false;

        if ($this->request->is('ajax')) {
            $input = $this->request->data;

            if (!empty($input['id'])) {
                $users = $this->User->find('all', array(
                    'conditions' => array('User.id' => $input['id'])
                ));

                $users = $this->User->removeArrayWrapper('User', $users, 'id');

                foreach ($users as $key => $user) {
                    $status = $user['status'] ? 0 : 1;

                    $this->User->save(array('id' => $key, 'status' => $status, 'cdate' => date('Y-m-d H:i:s')));
                }

                $this->Session->setFlash(__('アカウントを保存しました。'), 'alert-box', array('class' => 'alert-success'));

                echo json_encode(array(
                    'success' => 1,
                    'redirect' => 1
                ));
                exit;
            }
        }

        return $this->redirect('/');
    }

    /**
     * Delete user record
     */
    public function ajaxDelete() {
        if ($this->request->is('ajax')) {
            $input = $this->request->data;

            if (!empty($input['id'])) {
                foreach ($input['id'] as $key => $value) {
                    $lb_id = $this->Label->LabelData->find('all', array(
                        'conditions' => array(
                            'Label.type' => 'UserModel',
                            'LabelData.target_id' => $value
                        ),
                        'fields' => array('Label.id')
                    ));
                    $lb_id = $this->Label->removeArrayWrapper('Label', $lb_id, 'id');
                    foreach ($lb_id as $label) {
                        $this->Label->LabelData->deleteAll(array('target_id' => $value, 'label_id' => $label), false);
                    }                   
                }
                $this->User->deleteAll(array('User.id' => $input['id']), false);

                $this->Session->setFlash(__('アカウントが削除されました。'), 'alert-box', array('class' => 'alert-success'));

                echo json_encode(array(
                    'success' => 1,
                    'redirect' => 1
                ));
                exit;
            }
        }

        return $this->redirect('/');
    }

}