<?php

App::uses('AppController', 'Controller');
App::uses('BookmarkExtData', 'Model');

class AccessHistoryController extends AppController {
  // ...
  public function beforeFilter() {
     parent::beforeFilter();
        $this->Auth->allow(array('controller' => 'accesshistory', 'action' => 'index'));
  }   
  public function index(){

      $this->autoRender = false;
      try {
        $bookmark_id = 0;
        $cookie = $this->request->query('tk');
        if( empty($cookie) ){
          throw new Exception("not found token", 1);
        }
        
        $this->loadModel('Bookmark');
        $this->loadModel('Link');
        $this->loadModel('User');
        $this->loadModel('AccessLog');
        
      $sql = "SELECT bookmark_id, team_id, contents, p_type, created_at FROM access_log  
        WHERE bookmark_id != 0 AND ( team_id in (1550,1551,1552,1553,1554,1555,1556,1557,1558,1559,1560,1561,1562,1563,1564,1565,1566,1567,1568,1569,1570,1571,1572,1573,1574,1575,1576,1577,1578,1579,1580,1581,1582,1583,1584,1585,1586,1587,1588,1589,1590,1591,1592,1593,1594,1596,1598,1599,1600,1601,1602,1603,1604,1605,1606,1607,1608,1609,1610)
        OR (team_id = 1597 AND bookmark_id = 3461) )
        AND cookie ='$cookie' GROUP BY team_id ORDER BY created_at DESC";
        
        $result = $this->AccessLog->query( $sql );
        
        $result = $this->AccessLog->removeArrayWrapper('access_log', $result);
        $count = count($result);
        if( empty($count) ){ $count = 0;}
        
        $access_list = array();
        foreach ($result as $key => $value) {
          $url = str_replace('___SP_PLATETYPE____', $value['p_type'], $value['contents']);
          //$bookmark_data = $this->Bookmark->findById($value['bookmark_id']);
          //$bookmark_name = $bookmark_data['Bookmark']['name'];
          $user_data = $this->User->findByTeamId($value['team_id'],array('name'));
          $user_name = $user_data['User']['name'];
          
          $t = new DateTime($value['created_at'], new DateTimeZone('UTC'));
          $t->setTimeZone(new DateTimeZone('Asia/Tokyo'));
          $result_date = $t->format('d日 H:i');
          $access_list[] = array('user_name'=>$user_name,'url'=>$url,'date'=>$result_date);
        }
        
        $data_json = json_encode($access_list);
        
        print '{"status":{"code":0},"count":'.$count.',"list":'.$data_json.'}';
        
      }catch(exception $e){
          
        $err_message = $e->getMessage();
        $err_code = $e->getCode();
        print '{"status":{"code":"'.$err_code.'","message":"'.$err_message.'"}}';
      }
  }
}

?>