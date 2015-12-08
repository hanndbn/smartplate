<?php

App::uses('Controller', 'Controller');

/**
 * Controller for AccountUser model
 * 
 * @package       app.Controller
 * 
 */
class AccountUsersController extends AppController {

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('InvoiceBreakdown');
        $this->Auth->allow(array('getAccountUser'));
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

    public function index() {
        
    }

    /**
     * Show list account user in system view
     */
    public function system_index() {

        /* Add filter */

        // Filter
        $conditions = array();
        $joins = array();
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
            return $this->redirect($filter_url);
        } else {
            // Inspect all the named parameters to apply the filters            
            foreach ($this->params['named'] as $param_name => $value) {
                $value = Utility_Str::returnhtml($value);
                // Don't apply the default named parameters used for pagination
                if (!in_array($param_name, array('page', 'sort', 'direction', 'limit'))) {
                    // You may use a switch here to make special filters
                    // like "between dates", "greater than", etc
                    if ($param_name == "name") {
                        $conditions['OR'] = array(
                            array('AccountUser.family_name LIKE' => '%' . str_replace('%', '\%', $value) . '%'),
                            array('AccountUser.given_name LIKE' => '%' . str_replace('%', '\%', $value) . '%'),
                            array('AccountUser.company LIKE' => '%' . str_replace('%', '\%', $value) . '%'),
                            array('AccountUser.address LIKE' => '%' . str_replace('%', '\%', $value) . '%'),
                        );
                    } else {
                        $conditions += array('AccountUser.country' => $value);
                    }

                    $this->request->data['Filter'][$param_name] = $value;
                }
            }
        }

        $this->AccountUser->recursive = -1;
        $this->paginate = array(
            'limit' => 20,
            'joins' => $joins,
            'conditions' => $conditions,
            'order' => array('id' => 'asc')
        );
        // Pass the search parameter to highlight the text
        $this->set('name', isset($this->params['named']['name']) ? $this->params['named']['name'] : "");
        /* end filter */

        $users = $this->paginate('AccountUser');
        if ($users)
            $users = $this->AccountUser->removeArrayWrapper('AccountUser', $users, 'id');

        $this->set('users', $users);
        $this->render('index');
    }

    /**
     * Display create account user form
     */
    public function add() {
        $accountUserID = $this->getAccountUser();
        if($accountUserID != NULL){
            return $this->redirect(array('controller' => 'account_users', 'action' => 'edit', $accountUserID));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if (isset($this->request->data['AccountUser'])) {
                $this->request->data['AccountUser']['admin_id'] = $this->Auth->user('id');
                $this->request->data['AccountUser']['regist_date'] = date('Y-m-d H:i:s');
                $this->request->data['AccountUser']['status'] = 1;

                if ($this->AccountUser->save($this->request->data['AccountUser'])) {
                    $newRecord = $this->AccountUser->getLastInsertId();
                    $this->Session->setFlash(__('アカウントを保存しました。'), 'alert-box', array('class' => 'alert-success'));
                    return $this->redirect(array('controller' => 'account_users', 'action' => 'edit', $newRecord));
                } else {
                    $this->Session->setFlash(__('新規アカウントを作成することはできません。'), 'alert-box', array('class' => 'alert-danger'));
                }

                $this->redirect(array('controller' => 'pages', 'action' => 'index'));
            }
        }
        $this->render('edit');
    }

    /**
     * Update data from ajax form
     * 
     * @return json 
     */
    public function system_quickEdit() {
        if ($this->request->is('post')) {
            $rq_data = $this->request->data;
            $type = $rq_data['type'];
            $date = date('Y-m-d H:i:s');
            if ($type == 2) {
                $id = $rq_data['id'];
                $input = $rq_data['input'];
                $f_name = $input[0];
                $l_name = $input[1];
                $this->AccountUser->updateAll(array('family_name' => "'{$f_name}'", 'given_name' => "'{$l_name}'", 'update_date' => "'{$date}'"), array('id' => $id));
            } elseif ($type == 6) {
                $ids = $rq_data['ids'];
                foreach ($ids as $v_id) {
                    $status = $this->AccountUser->find('first', array(
                        'conditions' => array('id' => $v_id),
                        'fields' => array('status')
                    ));
                    if ($status['AccountUser']['status'] == 1) {
                        $status = 0;
                    } else {
                        $status = 1;
                    }
                    $this->AccountUser->updateAll(array('status' => $status, 'update_date' => "'{$date}'"), array('id' => $v_id));
                }
            }

            $this->Session->setFlash(__('アカウントを保存しました。'), 'alert-box', array('class' => 'alert-success'));

            echo json_encode(1);
            exit;
        }
    }

    /**
     * Delete user record
     */
    public function system_delete() {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        $rq_data = $this->request->data;
        $ids = $rq_data['id'];

        //Delete record in invoice_breakdown and invoice table
        foreach ($ids as $id) {
            $invoiceIds = $this->AccountUser->Invoice->find('all', array(
                'conditions' => array(
                    'account_user_id' => $id
                ),
                'fields' => 'Invoice.id'
            ));
            if ($invoiceIds) {
                $invoiceIds = $this->AccountUser->Invoice->removeArrayWrapper('Invoice', $invoiceIds);
                foreach ($invoiceIds as $invoiceId) {
                    $i_id = $invoiceId['id'];
                    $this->InvoiceBreakdown->deleteAll(array('invoice_id' => $i_id), false);
                    $this->AccountUser->Invoice->deleteAll(array('Invoice.id' => $i_id), false);
                }
            }
        }
        if ($this->AccountUser->delete($ids)) {
            $this->Session->setFlash(__('アカウントが削除されました。'), 'alert-box', array('class' => 'alert-success'));
        } else {
            $this->Session->setFlash(__('アカウントを削除することはできません。'), 'alert-box', array('class' => 'alert-danger'));
        }
        echo json_encode(1);
        exit;
    }

    /**
     * Show user detail
     * 
     * @param int $id user id
     * @return array 
     */
    public function system_detail($id = null) {
        $this->_setModalLayout();

        $id = intval($id);

        if (!$id) {
            $this->Session->setFlash(__('アカウントのIDを提供してください。'), 'alert-box', 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }

        /* Get user */

        $user = $this->AccountUser->findById($id);
        if (!$user) {
            $this->Session->setFlash(__('アカウントのIDは無効です。'), 'alert-box', 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }

        $this->set(array(
            'user' => $user['AccountUser']
        ));
        $this->render('detail');
    }

    /**
     * Update user record
     * 
     * @param int $id user id
     */
    public function edit($id = null) {

        $id = intval($id);
        if (!$id) {
            $this->Session->setFlash(__('アカウントのIDを提供してください。'), 'alert-box', 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('controller' => 'pages', 'action' => 'index'));
        }

        $user = $this->AccountUser->findById($id);
        if (!$user) {
            $this->Session->setFlash(__('アカウントのIDは無効です。'), 'alert-box', 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('controller' => 'pages', 'action' => 'index'));
        }
        if($user['AccountUser']['admin_id'] != $this->Auth->user('id')){
            return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->request->data['AccountUser']) {
                $this->AccountUser->id = $id;
                $this->request->data['AccountUser']['update_date'] = date('Y-m-d H:i:s');
                $this->request->data['AccountUser']['admin_id'] = $this->Auth->user('id');
                if ($this->AccountUser->save($this->request->data['AccountUser'])) {
                    $this->Session->setFlash(__('アカウントを保存しました。'), 'alert-box', array('class' => 'alert-success'));
//                    return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                } else {
                    $this->Session->setFlash(__('アカウントをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                    return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                }
            }
        }
        if (!$this->request->data) {
            $this->request->data = $user;
        }
        $this->set(array('user' => $user['AccountUser']));
        $this->render('edit');
    }

    /** Return Account user ID for Admin menu
     * 
     * @return int Account user ID
     */

    public function getAccountUser() {
        $this->loadModel('AccountUser');
        if ($this->Auth->user('authority') == 1) {
            $adminID = $this->Auth->user('id');
            $accountUserID = $this->AccountUser->find('first', array(
                'conditions' => array(
                    'admin_id' => $adminID
                )
            ));
            if ($accountUserID) {
                return $accountUserID['AccountUser']['id'];
            } else {
                return null;
            }
        }
    }
  
}