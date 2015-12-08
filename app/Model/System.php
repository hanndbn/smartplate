<?php
App::uses('AppModel', 'Model');

/**
 * Model for system table
 *  
 * @package       app.Model
 * 
 */
class System extends AppModel {
    public $useTable = 'system';
    public $validate = array(
        'login_name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'A username is required',
                'allowEmpty' => false
            ),
        ),
        'password' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'A password is required',
                'allowEmpty' => false
            )
        )
    );
    
    /**
     * Action will be called before save record to database.
     *
     * @param array $options The record prepare to store.
     * @return bool Fallback to our parent true.
     */
    public function beforeSave($options = array()) {
        // hash our password
        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = Security::hash(
                            $this->data[$this->alias]['password'], 'sha1', true
            );
        }

        // if we get a new password, hash it
        if (isset($this->data[$this->alias]['password_update'])) {
            $this->data[$this->alias]['password'] = Security::hash(
                            $this->data[$this->alias]['password'], 'sha1', true
            );
        }

        // fallback to our parent
        return parent::beforeSave($options);
    }
    
}
?>
