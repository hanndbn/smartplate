<?php

/*
 * Controller for Management model
 *
 * Add, edit, delete and log in user in management table
 *
 * @package       app.Controller
 * 
 */

class ManagementsController extends AppController
{

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('System');
        $this->Auth->deny(array('changePassword', 'system_changePassword'));
    }

    public function index()
    {
        return $this->redirect(array('action' => 'login'));
    }

    public function privacyPolicy()
    {
        if ($this->request->is('post')) {
            $rqData = $this->request->data;
            $id = $this->Auth->user('id');
            $status = $rqData['status'];
            $this->Management->id = $id;
            $this->Management->save(array('agree_flag' => $status));
            $redirect = array(
                'redirect' => Router::url(array('controller' => 'pages', 'action' => 'index'))
            );
            echo json_encode($redirect);
            exit;
        }
        $data = $this->Management->findById($this->Auth->user('id'));
        $this->set(array(
            'data' => $data
        ));
        $language =  $this->Session->read('Config.language');
        //$language = $this->params['language'];
        switch ($language) {
            case 'jpn':
                $this->render('privacy');
                break;
            case 'eng':
                $this->render('privacy_eng');
                break;
            case 'cns':
                $this->render('privacy_cns');
                break;
            case 'cnt':
                $this->render('privacy_cnt');
                break;
            default:
                $this->render('privacy');
                break;
        }

    }

    /**
     * Loggin user
     */
    public function login()
    {
        $this->_setModalLayout();
        //if already logged-in, redirect
        if ($this->Session->check('Auth.User')) {
            $this->redirect(array('controller' => 'pages', 'action' => 'index'));
        }

        // if we get the post information, try to authenticate
        if ($this->request->is('post')) {
            $this->autoRender = false;
            $this->response->type('json');

            if ($this->Auth->login()) {
                if ($this->Auth->user()) {
                    $user = $this->Auth->user();
                    $redirect = ($user['authority'] == '3' && $user['agree_flag'] == 0) ? Router::url(array('controller' => 'managements', 'action' => 'privacyPolicy')) : $this->webroot;
                    $this->Management->id = $this->Auth->user('id');
                    $this->Management->saveField('last_login_date', date(DATE_ATOM));
                    if ($this->request->is('ajax')) {
                        return $this->response->body(json_encode(array(
                            'ok' => 1,
                            'redirect' => $redirect
                        )));
                    } else {
                        $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                }
            } else {
                /* $this->Session->setFlash(__('ユーザー名またはパスワードは無効です。'));
                  $this->redirect(array('controller' => 'managements', 'action' => 'login')); */
                return $this->response->body(json_encode(array(
                    'ok' => 0,
                    'message' => __('ユーザー名またはパスワードは無効です。')
                )));
            }
        }
    }

    /**
     * Use to loggout user
     *
     */
    public function logout()
    {
        $this->Session->destroy();
        if ($this->Auth->logout()) {
            $this->redirect('/');
            //$this->redirect(array('controller' => 'pages', 'action' => 'index'));
        }
    }

    /**
     * Change user's password
     */
    public function changePassword()
    {
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->request->data['Management']) {
                $user = $this->Auth->user();
                $rq_user = $this->request->data['Management'];
                $this->Management->id = $this->Auth->user('id');
                if ($this->request->is('ajax')) {
                    $pass = $rq_user['password'];
                    $pass = Security::hash($pass, 'sha1', true);
                    $check = $this->Management->find('first', array(
                        'conditions' => array(
                            'id' => $user['id'],
                            'password' => $pass
                        )
                    ));
                    if ($check) {
                        if ($rq_user['cf_newpassword'] == $rq_user['password_update']) {
                            $rq_user['password'] = $rq_user['password_update'];
                            $this->Management->save(array('password' => $rq_user['password']));
                            echo json_encode(array(
                                'message' => __('パスワードが変更されました。'),
                                'susscess' => '3'
                            ));
                            exit;
                        } else {
                            echo json_encode(array(
                                'message' => __('再度メール記入を確認ください。'),
                                'susscess' => '2'
                            ));
                            exit;
                        }
                    } else {
                        echo json_encode(array(
                            'message' => __('パスワードは無効です。'),
                            'susscess' => '1'
                        ));
                        exit;
                    }
                } else {
                    $this->redirect($this->referer());
                }
            }
        }
    }

    public function system_index()
    {
        return $this->redirect(array('action' => 'login'));
    }

    public function system_logout()
    {
        $this->Session->destroy();
        if ($this->Auth->logout()) {
            $this->redirect('/system');
            //$this->redirect(array('controller' => 'pages', 'action' => 'index'));
        }
    }

    public function system_login()
    {
        $this->_setModalLayout();

        //if already logged-in, redirect
        if ($this->Session->check('Auth.User')) {
            $this->redirect(array('controller' => 'pages', 'action' => 'index'));
        }

        // if we get the post information, try to authenticate
        if ($this->request->is('post')) {

            $this->autoRender = false;
            $this->response->type('json');

            if ($this->Auth->login()) {
                if ($this->Auth->user()) {
                    if ($this->request->is('ajax')) {
                        return $this->response->body(json_encode(array(
                            'ok' => 1,
                            'redirect' => $this->webroot . 'system'
                        )));
                    } else {
                        $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                }
            } else {
                return $this->response->body(json_encode(array(
                    'ok' => 0,
                    'message' => __('ユーザー名またはパスワードは無効です。')
                )));
            }
        }
    }

    /**
     * for system view
     * Change user's password
     */
    public function system_changePassword()
    {
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->request->data['Management']) {
                $user = $this->Auth->user();
                $rq_user = $this->request->data['Management'];
                $this->System->id = $this->Auth->user('id');
                if ($this->request->is('ajax')) {
                    $pass = $rq_user['password'];
                    $pass = Security::hash($pass, 'sha1', true);
                    $check = $this->System->find('first', array(
                        'conditions' => array(
                            'id' => $user['id'],
                            'password' => $pass
                        )
                    ));
                    if ($check) {
                        if ($rq_user['cf_newpassword'] == $rq_user['password_update']) {
                            $rq_user['password'] = $rq_user['password_update'];
                            $this->System->save(array('password' => $rq_user['password']));
                            echo json_encode(array(
                                'message' => __('パスワードが変更されました。'),
                                'susscess' => '3'
                            ));
                            exit;
                        } else {
                            echo json_encode(array(
                                'message' => __('再度メール記入を確認ください。'),
                                'susscess' => '2'
                            ));
                            exit;
                        }
                    } else {
                        echo json_encode(array(
                            'message' => __('パスワードは無効です。'),
                            'susscess' => '1'
                        ));
                        exit;
                    }
                } else {
                    $this->redirect($this->referer());
                }
            }
        }
        $this->render('change_password');
    }

    /**
     * Return last time user loggin in system
     */
    public function system_getLastLoggin()
    {
        $this->getLastLoggin();
    }

    /**
     * Return last time user loggin
     */
    public function getLastLoggin()
    {
        if ($this->Auth->user()) {
            $user_id = $this->Auth->user('id');
            $last_login = $this->Management->find('first', array(
                'conditions' => array('id' => $user_id),
                'fields' => array('last_login_date')
            ));
            return $datas = array('last_login' => $last_login);
        }
    }

}

?>
