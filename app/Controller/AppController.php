<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package        app.Controller
 * @link        http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    public $helpers = array(
        'Timthumb.Timthumb',
        'Form' => array('className' => 'ExtendedForm'),
        'Html' => array('className' => 'MyHtml')
    );
    public $teamId = 0;
    // sessions support
    // authorization for login and logut redirect
    public $components = array(
        'Cookie',
        'Session',
        'Auth' => array(
            'authorize' => array('Controller')
        )
    );

    /**
     * perform logic that needs to happen before each controller action
     */
    public function beforeFilter()
    {
        $this->_setLanguage();
        if ($this->Auth->user()) {
            $this->teamId = $this->Auth->user('team_id');
            Configure::write('teamId', $this->teamId);
        }

        $isSystem = !empty($this->request->params['system']);
        Configure::write('backEnd', $isSystem);

        $this->Auth->loginAction = array(
            'controller' => 'managements',
            'action' => 'login'
        );
        $this->Auth->authenticate = array(
            'Form' => array(
                'userModel' => 'Management',
                'passwordHasher' => array(
                    'className' => 'Simple',
                    'hashType' => 'sha1'
                ),
                'fields' => array('username' => 'login_name', 'password' => 'password')
            ),
        );
        if ($this->request->prefix == 'system') {
            $this->Auth->authenticate = array(
                'Form' => array(
                    'userModel' => 'System',
                    'passwordHasher' => array(
                        'className' => 'Simple',
                        'hashType' => 'sha1'
                    ),
                    'fields' => array('username' => 'login_name', 'password' => 'password')
                ),
            );
        }
        $this->Auth->allow(array('login', 'system_login', 'getLastLoggin'));
        /* Check Privacy Policy */
        if ($this->request->prefix != 'system' && $this->Auth->user('authority') == 3) {
            $this->loadModel('Management');
            $agreeFlag = $this->Management->find('first', array(
                'conditions' => array(
                    'id' => $this->Auth->user('id')
                ),
                'fields' => 'agree_flag'
            ));
            if ($agreeFlag['Management']['agree_flag'] == 0) {
                $controller = $this->request->params['controller'];
                $action = $this->request->params['action'];
                $allowAction = array('logout', 'privacyPolicy');
                if ($controller != 'managements') {
                    return $this->redirect(array('controller' => 'managements', 'action' => 'privacyPolicy'));
                } else {
                    if (!in_array($action, $allowAction)) {
                        echo $action;
                        return $this->redirect(array('controller' => 'managements', 'action' => 'privacyPolicy'));
                    }
                }
            }
        }


    }

    /**
     * Set Session and cookie the language url
     */
    private function _setLanguage()
    {
        //if the cookie was previously set, and Config.language has not been set
        //write the Config.language with the value from the Cookie
        if ($this->Cookie->read('lang') && !$this->Session->check('Config.language')) {
            $this->Session->write('Config.language', $this->Cookie->read('lang'));
        } //if the user clicked the language URL
        else if (isset($this->params['language']) &&
            ($this->params['language'] != $this->Session->read('Config.language'))
        ) {
            //then update the value in Session and the one in Cookie
            $this->Session->write('Config.language', $this->params['language']);
            $this->Cookie->write('lang', $this->params['language'], false, '2 days');
        }
    }

    /**
     * override redirect
     */
    public function redirect($url, $status = NULL, $exit = true)
    {
        if ($this->Auth->user()) {
            if (!isset($url['language']) && $this->Session->check('Config.language')) {
                $url['language'] = $this->Session->read('Config.language');
            }
        }
        parent::redirect($url, $status, $exit);
    }

    /**
     * Get all team_id by user's authority
     * @param int $authority user's authority
     * @return array array contains project ids
     */
    function getTeamid($authority)
    {
        $t_ids = array();
        if ($authority == 1) {
            $t_ids = $this->Management->getAdminProjects($this->Auth->user('id'));
        } elseif ($authority == 2) {
            $list_project = $this->Management->getProjects($this->Auth->user('id'));
            foreach ($list_project as $key => $value) {
                $t_ids[] = $key;
            }
        } elseif ($authority == 3) {
            $t_ids = $this->Auth->user('team_id');
        }

        return $t_ids;
    }


    /**
     * Allow registered user access functions
     */
    public function isAuthorized($user)
    {
        if ($this->request->prefix == 'system') {
            return true;
        } else {
            $controller = $this->request->params['controller'];
            $action = $this->request->params['action'];
            switch ($controller) {
                // Editor can access manager functions
                case 'bookmarks':
                    if ($user['authority'] === '2' || $user['authority'] === '1') {
                        if ($action === 'label') {
                            return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                        } else {
                            return true;
                        }
                    } elseif ($user['authority'] === '3') {
                        return true;
                    } else {
                        return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                    break;
                case 'tags':
                    if ($user['authority'] === '1' || $user['authority'] === '2') {
                        if ($action === 'label') {
                            return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                        } else {
                            return true;
                        }
                    } elseif ($user['authority'] === '3') {
                        return true;
                    } else {
                        return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                    break;
                case 'apps':
                    if ($user['authority'] === '3') {
                        return true;
                    } else {
                        return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                    break;
                // Only manager can access project functions
                case 'teams':
                    if ($user['authority'] === '2') {
                        if ($action === 'label') {
                            return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                        } else {
                            return true;
                        }
                    } else {
                        return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                    break;
                case 'account_users':
                    if ($this->request->params['action'] == 'edit' || $this->request->params['action'] == 'add') {
                        if ($user['authority'] === '1') {
                            return true;
                        } else {
                            return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                        }
                    } else {
                        return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                    break;
                case 'accounts':
                    if ($user['authority'] === '2' || $user['authority'] === '1') {
                        return true;
                    } else {
                        return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                    break;
                case 'invoices':
                case 'invoices_breakdown':
                    if ($user['authority'] === '1') {
                        return true;
                    } else {
                        return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                    break;
                case 'languages':
                    return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    break;
                case 'labels':
                    if ($user['authority'] === '3') {
                        return true;
                    } else {
                        return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
                    }
                    break;
                default:
                    return true;
                    break;
            }
            // Default deny
            return $this->redirect(array('controller' => 'pages', 'action' => 'index'));
        }
    }

    /**
     * Remove page layout if we are using modal/popup
     * and redirect to current controller index if current request is not ajax
     */
    protected function _setModalLayout()
    {
        if ($this->request->is('ajax')) {
            $this->autoLayout = false;
        } else if ($this->request->is('get')) {
            if ($this->params['controller'] != 'managements') {
                return $this->redirect(array('controller' => $this->params['controller'], 'action' => 'index'));
            }
        }
    }

}

