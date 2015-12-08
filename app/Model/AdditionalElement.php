<?php
App::uses('AppModel', 'Model');

/**
 * Application model for device table.
 *
 * @package       app.Model
 */
class AdditionalElement extends AppModel 
{
    public $useDbConfig = 'access_log_database';
    public $useTable = 'additional_element';
    
    public $belongsTo = array(
        'Label' => array('className' => 'CustomValue',
            'foreignKey' => 'custome_value_id')
    );
}

