<?php

App::uses('AppModel', 'Model');
/**
 * Model for table invoice_breakdown
 *
 * @package       app.Model
 */
class InvoiceBreakdown extends AppModel {

    public $useTable = 'invoice_breakdown';
    public $belongsTo = array(
        'Invoice' => array('className' => 'Invoice',
            'foreignKey' => 'invoice_id')
    );

}

?>
