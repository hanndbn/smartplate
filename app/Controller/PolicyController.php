<?php

/*
 * Controller for PrivacyPolicy
 *
 *
 * @package       app.Controller
 * 
 */

class PolicyController extends AppController
{
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow(array('controller' => 'policy', 'action' => 'index'));
    }
    
    public function index()
    {
        $language =  $this->Session->read('Config.language');
        switch ($language) {
            case 'jpn':
                $this->render('/Managements/privacy');
                break;
            case 'eng':
                $this->render('/Managements/privacy_eng');
                break;
            case 'cns':
                $this->render('/Managements/privacy_cns');
                break;
            case 'cnt':
                $this->render('/Managements/privacy_cnt');
                break;
            default:
                $this->render('/Managements/privacy');
                break;
        }

    }

}

?>
