<?php

App::uses('AppModel', 'Model');

/**
 * Model for invoice table
 *  
 * @package       app.Model
 * 
 */
class Invoice extends AppModel {

    public $useTable = 'invoice';
    public $hasMany  = array(
        'InvoiceBreakdown' => array(
            'className' => 'InvoiceBreakdown',
            'conditions' => array('InvoiceBreakdown.invoice_id = Invoice.id'),
        )
    );
    public $belongsTo = array(
        'AccountUser' => array(
            'className' => 'AccountUser',
            'foreignKey' => 'account_user_id')
        );
    public $recursive = -1;

}

?>
