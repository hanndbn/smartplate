<?php

/*
 * Controller for Order model
 *
 * @package       app.Controller
 *
 */

class OrdersController extends AppController {

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();
    }

    /**
     * Show list order
     */
    public function index() {
    	$this->loadModel('Tag');
		// csvアップロード
	    if(!empty($this->request->data)){
	        $uploaddir = WWW_ROOT."upload".DS."csv".DS;
	        $uploadfile = $uploaddir . date("YmdHi").".csv";

	        if(!move_uploaded_file($this->request->data['Order']['tmp_name'], $uploadfile)){
	            $this->Session->setFlash(__('ファイル形式はCSV（.csv）でアップロードしてください。'), 'alert-box', array('class' => 'alert-danger'));
	        	$this->redirect(array('controller' => 'orders', 'action' => 'index'));
	        }

	        $text_type = array (
	        		"text/csv",
	        		"application/vnd.ms-excel"
	        );

	        if (! in_array ( $this->request->data['Order']['type'], $text_type )) {
	        	$this->Session->setFlash(__('ファイル形式はCSV（.csv）でアップロードしてください。'), 'alert-box', array('class' => 'alert-danger'));
	        	$this->redirect(array('controller' => 'orders', 'action' => 'index'));
	        }

	        setlocale(LC_ALL, 'ja_JP.UTF-8');
	        $data = file_get_contents($uploadfile);
	        $data = mb_convert_encoding($data, 'UTF-8', 'sjis-win');
	        $temp = tmpfile();
	        $csv  = array();
	        fwrite($temp, $data);
	        rewind($temp);

	        while (($data = fgetcsv($temp, 0, ",")) !== FALSE) {
	        	$csv[] = $data;
	        }
	        fclose($temp);


	        $this->Tag->updatePlateRowByCSV($csv);
	        $this->redirect('index');
	    }

       $this->loadModel('Management');
        $this->paginate = array(
            'limit' => 20,
            'order' => array('id' => 'asc'),
        );
        if($this->request->prefix != 'system'){
            if ($this->Auth->user('authority') == '2' || $this->Auth->user('authority') == '1') {
                $listUsers = $this->Management->getChildID($this->Auth->user('id'));
                $team_id = $this->Management->getUserTeamID($listUsers);
                $search_conditions = array('team_id' => $team_id );
            }
        }

        if( isset($search_conditions) ){
          $orders = $this->paginate('Order',$search_conditions);
        }else{
          $orders = $this->paginate('Order');
        }

        if(isset($this->params['named']['act']) && $this->params['named']['act']=='download'){
            $this->download($this->params['named']['num']);
        }
        // Get Plan type
        $this->loadModel('Plan');
        $this->loadModel('Order');
        foreach ($orders as &$order) {
            $planID = $order['Team']['plan'];
            $planType = $this->Plan->find('first', array(
                'conditions' => array(
                    'id' => $planID
                ),
                'fields' => 'type'
            ));
            if( empty($planType['Plan']) ){
              $order['planType'] = '';
            } else {
              $order['planType'] = ($planType['Plan']['type']) ? $planType['Plan']['type'] : '';
            }

            // get csv file link
            $csv_file_name = $this->Order->fileName($order['Order']['id']);
            if( file_exists($csv_file_name) ){
              $order['csv'] = $order['Order']['id'];
            } else {
              $order['csv'] = '';
            }
        }
        $this->set(array(
            'orders' => $orders
        ));
    }

    public function system_index() {
        $this->index();
        $this->render('index');
    }

    /**
     * Create new Plate record
     * @param int $id Order status ID
     */
    public function releasePlate($id) {
        $this->_setModalLayout();
        $this->loadModel('Management');
        $this->loadModel('Team');
        $this->loadModel('Tag');
        //Get list management_id
        $listAdminID = $this->Management->find('list', array(
            'conditions' => array(
                'authority' => 1
            ),
            'fields' => array('id', 'login_name')
        ));
        array_unshift($listAdminID,"未設定");
        //Get list Project id
        $listProjectID = $this->Team->find('list', array(
            'fields' => array('id', 'name')
        ));
        array_unshift($listProjectID,"未設定");
        $this->set(array(
            'id' => $id,
            'listUserID' => $listAdminID,
            'listProjectID' => $listProjectID
        ));

        if ($this->request->is('post')) {
            if (isset($this->request->data['Order'])) {
                $this->loadModel('Order');

                $rqData = $this->request->data['Order'];
                $alias = strtoupper($rqData['alias']);
                $lotNumber = (int) $rqData['lotNumber'];
                $addLotNumber = (int) $rqData['addLotNumber'];
                $IDPer1Lot = (int) $rqData['IDPer1Lot'];
                $userID = $rqData['userID'];
                $teamID = $rqData['teamID'];
                //Calculate lot number
                $serial_arr = array();
                $date = date('Y-m-d H:i:s');
                $flag = 0;

                if( $id == 0 ){
                  // new order : not allocated
                  $order_data = array();
                  $order_data['team_id'] = 1; // for aquabit team
                  $order_data['management_id'] = 1;
                  $order_data['count'] = $addLotNumber;
                  $order_data['status'] = 5;
                  $order_data['request_date'] = date('Y-m-d H:i:s');
                  if ($this->Order->save($order_data)) {
                    $id = $this->Order->getLastInsertID();
                  }else{
                    $result = array(
                        'result' => 'false'
                    );
                    echo json_encode($result);
                    exit;
                  }
                }
                $this->Order->createOrderCSV($id);
                for ($i = 0; $i < $addLotNumber; $i++) {
                    $lot = $lotNumber + $i;
                    for ($j = 0; $j < $IDPer1Lot; $j++) {
                        $activate = rand(100000, 999999);
                        $serial = $this->getSerial($serial_arr, "$alias.$lot.");
                        $serial_arr[] = $serial;
                        $tag_code = "$alias.$lot.$serial";
                        $ext_index = $this->Tag->getExtensionIndex();
                        $tagdata = array(
                            'Tag' => array(
                                'tag' => $tag_code,
                                'team_id' => $teamID,
                                'management_id' => $userID,
                                'activation_code' => $activate,
                                'ext_index' => $ext_index,
                                'order_status_id' => $id,
                                'cdate' => "$date"
                            )
                        );
                        $this->Tag->create();
                        if ($this->Tag->save($tagdata)) {
                            $last_id = $this->Tag->getLastInsertID();
                            $this->Order->addPlateRowToCSV($last_id,$alias,$lot,$serial,$tag_code,$activate,$ext_index,'Q');
                        }else{
                            $flag = 1;
                        }
                    }
                }
//                $this->redirect(array('action' => 'index'));
                if ($flag == 1) {
                    $result = array(
                        'result' => 'false'
                    );
                } else {
                    $result = array(
                        'result' => 'pass'
                    );
                }
                echo json_encode($result);
                exit;
            }
        }
    }

    public function system_releasePlate($id) {
        $this->releasePlate($id);
        $this->render('release_plate');
    }

    public function system_download($id) {
        $this->download($id);
    }

    public function orderRegist() {
        $this->_setModalLayout();
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->request->data['Order']['team_id'] = $this->Auth->user('team_id');
            $this->request->data['Order']['status'] = 1;
            $this->request->data['Order']['request_date'] = date('Y-m-d H:i:s');
            if ($this->Order->save($this->request->data['Order'])) {
                $this->Session->setFlash(__('新規注文のアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                $this->redirect(array('controller' => 'tags', 'action' => 'index'));
            } else {
                $this->Session->setFlash(__('エラーが発生しました。ご注文内容はまだ保存されていません。'), 'alert-box', array('class' => 'alert-danger'));
                $this->redirect(array('controller' => 'tags', 'action' => 'index'));
            }
        }
    }

    /**
     * Change Order status
     *
     * @return json
     */
    public function changeStatus() {
        if ($this->request->is('post')) {

            $id = $this->request->data['id'];
            $status = $this->request->data['status'];
            $this->Order->id = $id;
            if ($this->Order->save(array('status' => $status))) {
                $this->Session->setFlash(__('Change Status susscess。'), 'alert-box', array('class' => 'alert-success'));
            } else {
                $this->Session->setFlash(__('Change Status fail。'), 'alert-box', array('class' => 'alert-danger'));
            }
            echo json_encode(1);
            exit;
        }
    }

    public function system_changeStatus(){
        $this->changeStatus();
    }

    /**
     * Return plate serial number
     * @param array $serial_arr
     * @param str $tag_code_base
     * @return int plate serial number
     */
    function getSerial($serial_arr, $tag_code_base) {
        $this->loadModel('Tag');
        $serial = rand(1000000, 9999999);
        while (array_search($serial, $serial_arr)) {
            $serial = rand(1000000, 9999999);
        }
        $tag_data = $this->Tag->find('first', array(
            'conditions' => array(
                'tag' => $tag_code_base . $serial
            )
        ));
        if (!empty($tag_data)) {
            $serial = getSerial($serial_arr, $tag_code_base);
        }
        return $serial;
    }

    /**
     * Export bookmark records to csv
     */
    public function system_export() {
        $this->loadModel("Bookmark");
        $this->loadModel("Master");
        $this->loadModel("Link");
        $this->response->download("export.csv");
        $datas = $this->Bookmark->find('all', array(
            'fields' => array('id', 'name')
        ));
        foreach ($datas as &$data) {
            $id = $data['Bookmark']['id'];
            // Get link.url
            $linkUrls = $this->Link->find('all', array(
                'conditions' => array(
                    'bookmark_id' => $id
                ),
                'fields' => 'url'
            ));
            $data['Bookmark']['links:url'] = array();
            foreach ($linkUrls as $linkUrl) {
                $data['Bookmark']['links:url'][] = ($linkUrl['Link']['url']) ? $linkUrl['Link']['url'] : '';
            }
            $data['Bookmark']['links:url'] = ($data['Bookmark']['links:url']) ? '"' . implode(' ', array_filter($data['Bookmark']['links:url'])) . '"' : '';
            $DBConfige = get_class_vars('DATABASE_CONFIG');
            $accesslogDB = $DBConfige['access_log_database']['database'];
            // AccessLog count
            $query = $this->Bookmark->query(
                    "select master.bookmark_type as bookmark_kind, team.name as team_name, (SELECT COUNT(*) FROM {$accesslogDB}.access_log WHERE access_log.bookmark_id = bookmark.id) as tap_count, count(tag.id) as plates
                        FROM bookmark as bookmark
                        LEFT JOIN master as master ON (bookmark.kind = master.id)
                        LEFT JOIN team as team ON (team.id = bookmark.team_id)
                        LEFT JOIN tag as tag ON (bookmark.id = tag.bookmark_id)
                        WHERE bookmark.id = {$id}"
            );
            $data['Bookmark']['bookmark:kind'] = $query[0]['master']['bookmark_kind'];
            $data['Bookmark']['team:name'] = $query[0]['team']['team_name'];
            $data['Bookmark']['tap count'] = $query[0][0]['tap_count'];
            $data['Bookmark']['Plates'] = $query[0][0]['plates'];
        }
        $datas = $this->Bookmark->removeArrayWrapper('Bookmark', $datas);
        $_serialize = 'datas';
        $_bom = true;
        $_header = array('Name', 'Kind', 'Url', 'Tap Count', 'Plates', 'Project Name');
        $_extract = array('name', 'bookmark:kind', 'links:url', 'tap count', 'Plates', 'team:name');
        $this->viewClass = 'CsvView.Csv';
        $this->set(compact('datas', '_serialize', '_bom', '_header', '_extract'));
    }

    public function download($order_id)
    {
        $data_dir = TMP . Order::ORDER_CSV_DIR;
        $file_name = "plate_order_$order_id.csv";
        $file_path = $data_dir.$file_name;

        $this->response->file($file_path);
        $this->response->download($file_name);
    }
}

?>
