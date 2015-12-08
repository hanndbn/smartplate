<?php
App::uses('AppModel', 'Model');

/**
 * Application model for device table.
 *
 * @package       app.Model
 */
class Device extends AppModel 
{
    public $useTable = 'device';
    public $belongsTo = array(
        'User' => array('className' => 'User',
            'foreignKey' => 'user_id'));
}

