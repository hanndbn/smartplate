<?php

App::uses('AppModel', 'Model');
/**
 * Model for table label_datas
 *
 * @package       app.Model
 */
class LabelData extends AppModel {

    public $useTable = 'label_datas';
    public $belongsTo = array(
        'Label' => array('className' => 'Label',
            'foreignKey' => 'label_id')
    );
    
    public function InsertLabelData(  $label_id, $target_id ) {
      $data = array();
      $this->create();
      $data['target_id'] = $target_id;
      $data['cdate'] = date('Y-m-d H:i:s');
      $data['label_id'] = $label_id;
      $this->save($data);
      $label_data_id = $this->getLastInsertId();

      return $label_data_id;
    }
}

?>
