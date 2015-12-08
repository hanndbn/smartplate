<?php

/**
 * Controller for Manager Account
 *
 * @package       app.Controller
 *
 */
class AccountsController extends AppController
{

    public $uses = 'Management';

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Team');
        $this->loadModel('Label');
        $this->loadModel('AccessLog');
        $this->loadModel('Bookmark');
        $this->loadModel('Tag');
        $this->loadModel('AccountUser');
        $this->Auth->deny();
    }

    /**
     * Build a URL will all the search elements in it
     */
    function filter()
    {
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
     * for system view
     * Show list admin account information
     */
    public function system_index()
    {
        $this->index();
        $this->render('index');
    }

    /**
     * Show list Account information
     */
    public function index()
    {

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
                        $conditions += array(
                            array('Management.name LIKE' => '%' . str_replace('%', '\%', $value) . '%')
                        );
                    } elseif ($param_name == "manager") {
                        $conditions += array('Management.name' => $value);
                    }
                    $this->request->data['Filter'][$param_name] = $value;
                }
            }
        }

        if ($this->request->prefix == 'system') {
            $conditions += array(
                'Management.authority' => 1,
            );
        } else {
            if ($this->Auth->user('authority') == '1') {
                $conditions += array(
                    'Management.authority' => 2,
                    'Management.parent_id' => $this->Auth->user('id')
                );
            } else {
                $conditions += array(
                    'Management.authority' => 3,
                    'Management.parent_id' => $this->Auth->user('id')
                );
            }
        }
        $this->Management->recursive = -1;
        $this->paginate = array(
            'limit' => 20,
            'order' => array('id' => 'asc'),
            'conditions' => $conditions
        );
        /* end filter */

        $users = $this->paginate('Management');

        if (count($users) > 0) {
            $query_date = date('Y-m-d H:i:s');
            // First day of the month.
            $fday = date('Y-m-01 H:i:s', strtotime($query_date));
            // Last day of the month.
            $lday = date('Y-m-t H:i:s', strtotime($query_date));
            $concat = 'CONCAT(p_head, ".", p_lot, ".", p_num)';
            foreach ($users as &$user) {
                $id = $user['Management']['id'];
                // Get team's data
                $teams = $this->Team->find('all', array(
                    'conditions' => array(
                        'management_id' => $id
                    ),
                    'fields' => array('id')
                ));
                $team_id = array();
                if ($this->request->prefix != 'system') {
                    if ($this->Auth->user('authority') == '1') {
                        $team_id = $this->Management->getAdminProjects($id);
                    } elseif ($this->Auth->user('authority') == '2') {
                        $projects = $this->Management->getProjects($id);
                        foreach ($projects as $key => $value) {
                            $team_id[] = $key;
                        }
                    } else {
                        $team_id = $user['Management']['team_id'];
                    }
                } else {
                    $team_id = $this->Management->getAdminProjects($id);
                }
                // Get total account children
                $child_acc = $this->Management->find('count', array(
                    'conditions' => array('parent_id' => $id)
                ));
                $user['Management']['child_count'] = $child_acc;

                // Get total content
                $tt_content = $this->Bookmark->find('count', array(
                    'conditions' => array('team_id' => $team_id)
                ));
                $user['Management']['content_count'] = $tt_content;

                // Get total plate
                $tt_plate = $this->Tag->find('count', array(
                    'conditions' => array('team_id' => $team_id)
                ));
                $user['Management']['plate_count'] = $tt_plate;

                // Get last login
                $login_time = $this->Management->find('first', array(
                    'conditions' => array('Management.id' => $id),
                    'fields' => array('last_login_date')
                ));
                $user['Management']['last_login'] = $login_time['Management']['last_login_date'];
            }
//            print_r($users);
//            die;
        }
        /* Add filler */

        //List Management
        if ($this->request->prefix == 'system') {
            $option = array(
                'conditions' => array('authority' => 1),
                'group' => 'name'
            );
        } else {
            if ($this->Auth->user('authority') == 1) {
                $option = array(
                    'conditions' => array('authority' => 2, 'parent_id' => $this->Auth->user('id')),
                    'group' => 'name'
                );
            } else {
                $option = array(
                    'conditions' => array('authority' => 3, 'parent_id' => $this->Auth->user('id')),
                    'group' => 'name'
                );
            }
        }
        $list_manager = $this->Management->find('list', $option);
//        print_r($list_manager); die;
        $this->set(array(
            'users' => $users,
            'list_manager' => $list_manager
        ));
    }

    /**
     * for system view
     * Show account detail
     *
     * @param int $id project id
     * @return array
     */
    public function system_detail($id)
    {
        $this->detail($id);
        $this->render('detail');
    }

    /**
     * Show account detail
     *
     * @param int $id account id
     * @return array
     */
    public function detail($id)
    {
        $this->_setModalLayout();
        if (!$id) {
            $this->Session->setFlash(__('Please provide an account id'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Management->recursive = 0;
        $account = $this->Management->findById($id);
        //Get project name
        $team_name = $this->Team->find('first', array(
            'conditions' => array(
                'id' => $account['Management']['team_id']
            ),
            'fields' => array('Team.name')
        ));

        $this->set(array(
            'account' => $account,
            'team_name' => $team_name
        ));
        //print_r($account); die;
    }

    /**
     * for system view
     * Update admin user record
     *
     * @param int $id project id
     */
    public function system_edit($id)
    {
        $this->edit($id);
        $this->render('edit');
    }

    /**
     * Update editor record
     *
     * @param int $id account id
     */
    public function edit($id)
    {
        $this->_setModalLayout();

        if (!$id) {
            $this->Session->setFlash(__('Please provide an account id'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        //List project
        if ($this->request->prefix == 'system') {
            $list_project = $this->Team->find('list', array(
                'group' => 'name'
            ));
        } else {
            if ($this->Auth->user('authority') == 2) {
                $list_project = $this->Management->getProjects($this->Auth->user('id'));
            } else {
                $list_project = $this->Management->getProjects($this->Auth->user('id'), $autho = 1);
            }
        }
        $user = $this->Management->findById($id);
        if (!$user) {
            $this->Session->setFlash(__('Invalid Account ID Provided'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (isset($this->request->data['Management'])) {
            $rq_data = $this->request->data['Management'];
            $this->Management->id = $id;
            $this->request->data['Management']['update_date'] = date('Y-m-d H:i:s');
            if ($this->request->data['Management']['login_name'] == $this->request->data['Management']['newlogin_name']) {
                $existing = '';
            } else {
                $existing = $this->Management->find('all', array(
                    'conditions' => array(
                        'login_name' => $this->request->data['Management']['newlogin_name'],
                    )));
            }

            if (empty($existing)) {
                $this->request->data['Management']['login_name'] = $this->request->data['Management']['newlogin_name'];
                if (trim($this->request->data['Management']['_password']) != '') {
                    $this->request->data['Management']['password'] = $this->request->data['Management']['_password'];
                }

                if ($this->Management->save($this->request->data['Management'])) {
                    $this->Session->setFlash(__('The Account has been successfully updated.'), 'alert-box', array('class' => 'alert-success'));
                    $this->redirect(array('action' => 'index'));
                } else {
                    $this->Session->setFlash(__('Unable to update your account'), 'alert-box', array('class' => 'alert-danger'));
                    $this->redirect(array('action' => 'index'));
                }
            } else {
                $this->Session->setFlash(__('This account already exist.'), 'alert-box', array('class' => 'alert-danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        if (!$this->request->data) {
            $this->request->data = $user;
        }
        $this->set(array('list_project' => $list_project, 'user' => $user));
    }

    /**
     * for system view
     * Add new admin account
     */
    public function system_add()
    {
        $this->add();
        $this->render('edit');
    }

    /**
     * Add new account record
     */
    public function add()
    {
        $this->_setModalLayout();
        //List project
        if ($this->request->prefix == 'system') {
            $list_project = $this->Team->find('list', array(
                'group' => 'name'
            ));
        } else {
//                $list_project = $this->Management->getProjects($this->Auth->user('id'));
            $list_project = $this->Team->find('list', array(
                'conditions' => array(
                    'management_id' => $this->Auth->user('id')
                ),
                'group' => 'name'
            ));
        }

        $this->set(array(
            'list_project' => $list_project
        ));

        if ($this->request->is('post') || $this->request->is('put')) {
            if (isset($this->request->data['Management'])) {
//                print_r($this->request->data['Management']); die;
                $date = date('Y-m-d H:i:s');
                $this->request->data['Management']['parent_id'] = $this->Auth->user('id');
                $this->request->data['Management']['regist_date'] = $date;
                $existing = $this->Management->find('first', array(
                    'conditions' => array(
                        'login_name' => $this->request->data['Management']['login_name'],
                    )));

                if (empty($existing)) {
                    if ($this->Management->save($this->request->data['Management'])) {
                        $new_id = $this->Management->getLastInsertId();
//                        if (isset($this->request->data['Management']['team_id']) && $this->request->data['Management']['team_id'] != null) {
//                            $this->Team->updateAll(array('management_id' => $new_id), array('Team.id' => $this->request->data['Management']['team_id']));
//                        }
                        if ($this->request->data['Management']['authority'] == 1) {
                            // Add new account user record
                            $AcountUserData = array(
                                'admin_id' => $new_id,
                                'regist_date' => $date
                            );
                            $this->AccountUser->save($AcountUserData);
                        }
                        $this->Session->setFlash(__('New Account has been successfully saved.'), 'alert-box', array('class' => 'alert-success'));
                        return $this->redirect(array('action' => 'index'));
                    } else {
                        $this->Session->setFlash(__('Unable to add new account'), 'alert-box', array('class' => 'alert-danger'));
                        return $this->redirect(array('action' => 'index'));
                    }
                } else {
                    $this->Session->setFlash(__('This account already exist.'), 'alert-box', array('class' => 'alert-danger'));
                    return $this->redirect(array('action' => 'index'));
                }
            }
        }
        $this->render('edit');
    }

    /**
     * for system view
     * Quick edit account information
     */
    public function system_quickEdit()
    {
        $this->quickEdit();
    }

    /**
     * Get data from ajax quick edit form
     *
     * @return json
     */
    public function quickEdit()
    {
        if ($this->request->is('post')) {
            $rq_data = $this->request->data;
            $type = $rq_data['type'];

            if ($type == 2) {
                $cr_date = date('Y-m-d H:i:s');
                $id = $rq_data['id'];
                $input = str_replace("'", "''", $rq_data['input']);
                if ($this->Management->updateAll(
                    array(
                        'Management.name' => "'{$input}'",
                        'update_date' => "'{$cr_date}'"
                    ), array(
                        'Management.id' => $id
                    )
                )
                ) {
                    $this->Session->setFlash(__('アカウントのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                } else {
                    $this->Session->setFlash(__('アカウントをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                }
            } elseif ($type == 6) {
                $ids = $rq_data['ids'];
                foreach ($ids as $v_id) {
                    $visible = $this->Management->find('first', array(
                        'conditions' => array('id' => $v_id),
                        'fields' => array('status')
                    ));
                    $status = ($visible['Management']['status'] == 1) ? 0 : 1;
                    $this->Management->updateAll(array('status' => $status), array('Management.id' => $v_id));
                }
                //print_r($this->Team->getDataSource()->getLog(false, false));
//                die;
            }

            echo json_encode(1);
            exit;
        }
    }

}

?>
