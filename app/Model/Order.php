<?php
App::uses('AppModel', 'Model');

/**
 * Model for order_status table
 *  
 * @package       app.Model
 * 
 */
class Order extends AppModel {
    public $useTable = 'order_status';
    public $belongsTo = array(
        'Team' => array(
            'className' => 'Team',
            'foreignKey' => 'team_id')
        );
    public $validate = array(
        'request_date' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Request date is required',
                'allowEmpty' => false
            ),
        ),
        'count' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'allowEmpty' => false
            )
        ),
        'alias' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'allowEmpty' => false
            )
        ),
        'lotNumber' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'allowEmpty' => false
            )
        ),
        'addLotNumber' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'allowEmpty' => false
            )
        ),
        'IDPer1Lot' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'allowEmpty' => false
            )
        )
    );
    
    
    const DOMAIN = "http://plate.id/";
    const CSV_HEADER_STR = "No.,uid,NFC,QR,ShortNFC,ShortQR,Plate.ID,Activation-Code\n";
    const ORDER_CSV_DIR = '/data/smart_plate/order/';
    
    private $output_file_name = '';
    
    /**
     * create plate order csv 
     */
    public function createOrderCSV($order_id)
    {
      $header = mb_convert_encoding(self::CSV_HEADER_STR, "SJIS", "UTF8");
      
      $date = date ( "Y-m-d-H-i-s" );
      
      $data_dir = TMP . self::ORDER_CSV_DIR;
      $this->output_file_name = $data_dir."plate_order_$order_id.csv";
      
      if( !file_exists($this->output_file_name) )
        file_put_contents($this->output_file_name, $header);
    }
    
    /**
     * add plate row data to order csv file
     */
    public function addPlateRowToCSV($id,$index_chara,$lot,$serial,$tag_code,$activate,$ext_index,$ext_type)
    {
      $domain = self::DOMAIN;
      $base = "$id,,";
      $base .= "$domain$index_chara$lot"."N$serial,";
      $base .= "$domain$index_chara$lot"."Q$serial,";
      $base .= "$domain".'N'."$ext_index,";
      $base .= "$domain$ext_type$ext_index,";
      $base .= "$tag_code,$activate\n";
      file_put_contents($this->output_file_name, $base, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * get csv file name by order id
     */
    public function fileName($order_id)
    {
        $data_dir = TMP . self::ORDER_CSV_DIR;
        $csv_file_name = $data_dir."plate_order_$order_id.csv";
        
        return $csv_file_name;
    }
    /**
     * get csv file name by order id
     */
    public function fileURL($order_id)
    {
        $data_dir = TMP . self::ORDER_CSV_DIR;
        $csv_file_name = $data_dir."plate_order_$order_id.csv";
        
        return $csv_file_name;
    }
    
    public function downloadOrderCSV($order_id)
    {
        $data_dir = TMP . self::ORDER_CSV_DIR;
        $file_name = "plate_order_$order_id.csv";
        $file_path = $data_dir.$file_name;

        $this->response->file($file_path);
        $this->response->download($file_name);
    }
    
}
?>
