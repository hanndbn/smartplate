<?php

App::uses('AppModel', 'Model');
App::uses('CakeEmail', 'Network/Email');

/**
 * Model for account_user table
 *
 * @package       app.Model
 */
class AccountUser extends AppModel {

    public $useTable = 'account_user';
    public $hasMany = array(
        'Invoice' => array(
            'className' => 'Invoice',
            'conditions' => array('Invoice.account_user_id = AccountUser.id'),
    ));
    public $recursive = -1;
    public $validate = array(
        'family_name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Family name is required',
                'allowEmpty' => false
            )
        ),
        'given_name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Given name is required',
                'allowEmpty' => false
            )
        ),
        'mail' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Email is required',
                'allowEmpty' => false
            ),
        ),
    );

    public function beforeSave($options = array()) {
        
        if( empty($this->data['AccountUser']['family_name']) ){ $this->data['AccountUser']['family_name'] = '';}
        if( empty($this->data['AccountUser']['given_name']) ){ $this->data['AccountUser']['given_name'] = '';}
        
        $data = $this->data['AccountUser'];

        $Email = new CakeEmail('smtp');
        $Email->template('default');
        $Email->emailFormat('html');
        $Email->viewVars(array('userName' => $data['given_name']));
        if( empty($this->data['AccountUser']['mail']) ){
           $this->data['AccountUser']['mail'] = '';
           $Email->to('george@spirals.co.jp');
        }else{
          $Email->to($data['mail']);
          $Email->bcc(array('george@spirals.co.jp'));
        }
        $Email->subject('[SmartPlate]Regist Account');
       // $Email->send();
    }

}

