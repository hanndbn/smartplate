<?php

App::uses('AppModel', 'Model');
App::uses('User', 'Model');
App::uses('Tag', 'Model');
App::uses('Bookmark', 'Model');

/**
 * Model for access_log table
 *
 * @package       app.Model
 */
class AccessLog extends AppModel {
   // public $name = 'access_log';
    public $useDbConfig = 'access_log_database';
    public $useTable = 'access_log';
    public $primaryKey = 'id';
    public $target_time_zone = 'UTC';
    public $app_user_id = 0;

    const TYPE_HOUR       = 0;
    const TYPE_MINUTE     = 1;

    const TYPE_PLATE      = 0;
    const TYPE_CONTENTS   = 1;

    const TYPR_APP_APPLICATION   = 'sp';
    const TYPR_APP_CLOUD         = 'cf';

    private static $activate_list = null;
    private static $filter_user_plate_list = null;

    /**
     * Get number of platinum plate
     * @param int $id User's team_id
     * @return int number of platinum plate
     */
    public function platinum_query($id = null, $type = false) {
        $query_date = date('Y-m-d');
        // First day of the month.
        $fday = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $lday = date('Y-m-t', strtotime($query_date));

        $options = array(
            'fields' => array('CONCAT(p_head, "", p_lot, "", p_num) as p_path', 'COUNT(CONCAT(p_head, "", p_lot, "", p_num)) AS plate_count'),
            'group' => 'p_path',
        );
        if ($id) {
            $options['conditions'] = array('team_id' => $id,'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday));
        }
        $platinumplate = $this->find('all', $options);
        $platinumplate = $this->removeArrayWrapper('0', $platinumplate);
        $i = 0;
        $excess = array();
        foreach ($platinumplate as $p_plate) {
            (int)$p_count = $p_plate['plate_count'];
            if ($p_count >= 1000) {
                if ($type) {
                    $excess[] = $p_count - 1000;
                } else {
                    $i = $i + 1;
                }
            }
        }
        $platinum_number = ($type) ? $excess : $i;
        return $platinum_number;
    }

    /**
     * Json用配列の生成
     * @param $dbObj queryで得られた結果オブジェクト
     * @return array
     */
    private function makeDatas($dbObj)
    {
      $data_arr = array( 'details'=> array(array('type'=>'N','count'=>0),array('type'=>'Q','count'=>0)));
      $total_count = 0;
      if( !empty($dbObj) ){

        foreach ($dbObj as $key => $value) {
          $type = $value['AccessLog']['type'];
          if( $type == 'N')
            $index = 0;
          else
            $index = 1;
          $data_arr['details'][$index]['type']=$type;
          $data_arr['details'][$index]['count']=$value[0]['count'];
          $total_count += $value[0]['count'];
        }
      }
      $data_arr['count'] = $total_count;
      return $data_arr;
    }


    /**
     * Tag 検索SQL文字列の追加
     * @param $tag JA.00000.0000000
     * @param $sql 追加対象となる文字列
     * @return 追加されたarray
     */
    private function addTagString($tag, $conditions)
    {
      if( !empty($tag) ){
        $tag = explode('.', $tag);
        $p_head = $tag[0];
        $p_lot  = $tag[1];
        $p_num  = $tag[2];
        $conditions['p_head'] = $p_head;
        $conditions['p_lot']  = $p_lot;
        $conditions['p_num']  = $p_num;
      }
      return $conditions;
   }

    /**
     * アクセスユーザーのActivateされているタグリストを追加する
     * @param $sql
     * @return array
     */
    private function addkActivatePalteList( $conditions )
    {
        if( !empty(self::$activate_list) ){
          $conditions["CONCAT(p_head,'.',p_lot,'.',p_num)"] = self::$activate_list;
        }else{
          $conditions["CONCAT(p_head,'.',p_lot,'.',p_num)"] = '00.00000.0000000';
        }
        return $conditions;
    }
    /**
     * アクセスユーザーのActivateされているタグリストを追加する
     * @param $sql
     * @return String
     */
    private function addkActivatePalteListString( $sql )
    {
        if( !empty(self::$activate_list) ){
          $activate_list_string = '';
          foreach (self::$activate_list as $key => $value) {
            if( !empty($activate_list_string)){ $activate_list_string .=","; }
              $activate_list_string .= "'$value'";
          }
          $sql .= " AND CONCAT(p_head,'.',p_lot,'.',p_num) in ($activate_list_string)";
        }
        return $sql;
    }
    /**
     * アクセスユーザーのActivateされているタグリストを追加する
     * @param $sql
     * @return String
     */
    private function addkFilterAppUserPalteListString( $sql )
    {
        if( !empty(self::$filter_user_plate_list) ){
          $list_string = '';
          foreach (self::$filter_user_plate_list as $key => $value) {
            if( !empty($list_string)){ $list_string .=","; }
              $list_string .= "'$value'";
          }
          $sql .= " AND CONCAT(p_head,'.',p_lot,'.',p_num) in ($list_string)";
        }
        return $sql;
    }
    /**
     * 各タイムゾーンの日時からUTCに変換
     * @param $timezone
     * @param $date
     * @return string
     */
    public function convertUTC( $timezone,$date )
    {
        $t = new DateTime($date, new DateTimeZone($timezone));
        $t->setTimeZone(new DateTimeZone('UTC'));
        $result_date = $t->format('Y-m-d H:i:s');

        return $result_date;
    }

    /**
     * 各タイムゾーンの日時にUTCから変換
     * @param $timezone
     * @param $date
     * @return string
     */
    public function convertUTC2TimeZone( $timezone,$date )
    {
      $t = new DateTime($date, new DateTimeZone('UTC'));
    $t->setTimeZone(new DateTimeZone($timezone));
    $result_date = $t->format('Y-m-d H:i:s');

        return $result_date;
    }

    /**
     * アクセスユーザーのActivateされているタグリストをロードする
     * @param $team_id
     * @return array
     */
    public function loadActivatePalteList($team_id)
    {
        $tagModel = new Tag();

        $res = $tagModel->find( 'all', array( 'conditions'  => array( 'team_id'  => $team_id ),
                                              'fields'      => array( 'tag' )
                                ));
        if( !empty($res) ){
            self::$activate_list = array();
            foreach ($res as $key => $value) {
              self::$activate_list[] = $value['Tag']['tag'];
            }
        }else{
          self::$activate_list = NULL;
        }
        $tagModel = NULL;
    }

    /**
     * Activation_userでフィルタリングしたタグリストをロードする
     * @return array
     */
    public function filterAppUserPalteList($user_id)
    {
        if( empty(self::$filter_user_plate_list) ){
            $userModel = new User();
            $value = $userModel->find( 'first', array( 'conditions'  => array( 'id'  => $user_id ),
                                                  'fields'      => array( 'power' )
                                    ));
            if( empty($value['User']['power'] ) || $value['User']['power'] < 100 ){
                $userModel = NULL;
                $this->app_user_id = 0;
                self::$filter_user_plate_list = NULL;
                return;
            }
            $tagModel = new Tag();
            $this->app_user_id = $user_id;
            
            $res = $tagModel->find( 'all', array( 'conditions'  => array( 'activation_user'  => $this->app_user_id ),
                                                  'fields'      => array( 'tag' )
                                    ));
            if( !empty($res) ){
                self::$filter_user_plate_list = array();
                foreach ($res as $key => $value) {
                  self::$filter_user_plate_list[] = $value['Tag']['tag'];
                }
            }else{
              self::$filter_user_plate_list = NULL;
            }
            $tagModel = NULL;
        }
    }
     /**
     * タップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getContentsTap($team_id, $timezone = 'Asia/Tokyo', $start_date, $end_date, $app = null, $bookmark_id = null)
    {
        $team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id );
        if ($start_date)   { $conditions['created_at >='] = $start_date; }
        if ($end_date)   { $conditions['created_at <'] = $end_date; }
        if ($bookmark_id)   { $conditions['bookmark_id'] = $bookmark_id; }

        $res = $this->find( 'all', array( 'conditions'  => $conditions,
                                          'fields'      => array( 'p_type as type','COUNT(id) as count' ),
                                          'group'       => array( 'p_type' )
                                ));

        $data_arr = $this->makeDatas($res);

        return $data_arr;
    }

	/**
	 * OS毎のタップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getContentsTapByOs($team_id, $timezone = 'Asia/Tokyo', $start_date, $end_date, $app = null, $bookmark_id = null) {

		$team_id = intval ( $team_id );

		$options = array (
				'team_id' => $team_id
		);
		if ($start_date) {
			$options ['created_at >='] = $start_date;
		}
		if ($end_date) {
			$options ['created_at <'] = $end_date;
		}
		if ($bookmark_id) {
			$options ['bookmark_id'] = $bookmark_id;
		}

		$android_options ['LOCATE("Windows Phone", ua)'] = 0;
		$android_options ['LOCATE("Android", ua) !='] = 0;
		$ios_options ['LOCATE("Windows Phone", ua)'] = 0;
		$ios_options ['OR'] ['LOCATE("iPhone", ua) !='] = 0;
		$ios_options ['OR'] ['LOCATE("iPad", ua) !='] = 0;
		$ios_options ['OR'] ['LOCATE("iPod", ua) !='] = 0;

		// 全件取得
		$all_ua_count = $this->find ( 'count', array (
				'conditions' => $options
		) );
		// Android件数取得
		$android_ua_count = $this->find ( 'count', array (
				'conditions' => array_merge ( $options, $android_options )
		) );
		// iOS件数取得
		$ios_ua_count = $this->find ( 'count', array (
				'conditions' => array_merge ( $options, $ios_options )
		) );
		// その他件数取得
		$other_ua_count = $all_ua_count - $android_ua_count - $ios_ua_count;

		$res ['count'] = $android_ua_count + $ios_ua_count + $other_ua_count;

		$res ['details'] [0] ['count'] = $android_ua_count;
		$res ['details'] [0] ['type'] = "A";

		$res ['details'] [1] ['count'] = $ios_ua_count;
		$res ['details'] [1] ['type'] = "I";

		$res ['details'] [2] ['count'] = $other_ua_count;
		$res ['details'] [2] ['type'] = "O";

		return $res;

	}

     /**
     * トータルタップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getTotalContentsTap($team_id, $app = null, $bookmark_id = null)
    {
        return $this->getContentsTap($team_id, null, null, null, $app, $bookmark_id);
    }

	/**
	 * OS毎のトータルタップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getTotalContentsTapByOs($team_id, $app = null, $bookmark_id = null) {
		return $this->getContentsTapByOs ( $team_id, null, null, null, $app, $bookmark_id );
	}

    /**
     * 本日のタップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getDailyContentsTap($team_id, $timezone = 'Asia/Tokyo', $app = null, $bookmark_id = null)
    {
        $start_date = $this->convertUTC($timezone, date("Y-m-d 00:00:00"));
        $end_date = $this->convertUTC($timezone, date("Y-m-d 23:59:59"));

        return $this->getContentsTap($team_id, $timezone, $start_date, $end_date, $app, $bookmark_id);
    }

	/**
	 * OS毎の本日のタップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getDailyContentsTapByOs($team_id, $timezone = 'Asia/Tokyo', $app = null, $bookmark_id = null) {
		$start_date = $this->convertUTC ( $timezone, date ( "Y-m-d 00:00:00" ) );
		$end_date = $this->convertUTC ( $timezone, date ( "Y-m-d 23:59:59" ) );

		return $this->getContentsTapByOs ( $team_id, $timezone, $start_date, $end_date, $app, $bookmark_id );
	}

    /**
     * 週間のタップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getWeeklyContentsTap($team_id, $timezone = 'Asia/Tokyo', $app = null, $bookmark_id = null)
    {
        $start_date = $this->convertUTC($timezone, date("Y-m-d 00:00:00", strtotime('-1 week +1 day')));
        $end_date = $this->convertUTC($timezone, date("Y-m-d 23:59:59"), strtotime('now'));

        return $this->getContentsTap($team_id, $timezone, $start_date, $end_date, $app, $bookmark_id);
    }

	/**
	 * OS毎の週間のタップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getWeeklyContentsTapByOs($team_id, $timezone = 'Asia/Tokyo', $app = null, $bookmark_id = null) {
		$start_date = $this->convertUTC ( $timezone, date ( "Y-m-d 00:00:00", strtotime ( '-1 week +1 day' ) ) );
		$end_date = $this->convertUTC ( $timezone, date ( "Y-m-d 23:59:59" ), strtotime ( 'now' ) );

		return $this->getContentsTapByOs ( $team_id, $timezone, $start_date, $end_date, $app, $bookmark_id );
	}

    /**
     * 月間のタップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getMonthlyContentsTap($team_id, $timezone = 'Asia/Tokyo', $app = null, $bookmark_id = null)
    {
        $start_date = $this->convertUTC($timezone, date("Y-m-d 00:00:00", strtotime('-30 day')));
        $end_date = $this->convertUTC($timezone, date("Y-m-d 23:59:59"), strtotime('now'));

        return $this->getContentsTap($team_id, $timezone, $start_date, $end_date, $app, $bookmark_id);
    }

	/**
	 * OS月間のタップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getMonthlyContentsTapByOs($team_id, $timezone = 'Asia/Tokyo', $app = null, $bookmark_id = null) {
		$start_date = $this->convertUTC ( $timezone, date ( "Y-m-d 00:00:00", strtotime ( '-30 day' ) ) );
		$end_date = $this->convertUTC ( $timezone, date ( "Y-m-d 23:59:59" ), strtotime ( 'now' ) );

		return $this->getContentsTapByOs ( $team_id, $timezone, $start_date, $end_date, $app, $bookmark_id );
	}

    /**
     * タップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getTap($team_id, $timezone = 'Asia/Tokyo', $start_date, $end_date, $app = null, $tag = null)
    {
        $team_id = intval($team_id);
        
        $conditions = array( 'team_id'  => $team_id );
        
        if ($start_date)   { $conditions['created_at >='] = $start_date; }
        if ($end_date)   { $conditions['created_at <'] = $end_date; }
        //if ($app)   { $conditions['app'] = $app; }

        if( !empty($tag) ){
          $conditions = $this->addTagString($tag, $conditions);
        }else{
            if( $this->app_user_id > 0 ){
                $conditions[ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
            }else{
              $conditions = $this->addkActivatePalteList($conditions);
            }
        }
        if ( $app == self::TYPR_APP_CLOUD ){
            $conditions['bookmark_id !='] = 0;
        }

        $res = $this->find( 'all', array( 'conditions'  => $conditions,
                                          'fields'      => array( 'p_type as type','COUNT(*) as count' ),
                                          'group'       => array( 'p_type' )
                                ));

        $data_arr = $this->makeDatas($res);

        return $data_arr;
    }


	/**
	 * OSタップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getTapByOs($team_id, $timezone = 'Asia/Tokyo', $start_date, $end_date, $app = null, $tag = null) {
		$team_id = intval ( $team_id );
		$options = array (
				'team_id' => $team_id
		);
		if ($start_date) {
			$options ['created_at >='] = $start_date;
		}
		if ($end_date) {
			$options ['created_at <'] = $end_date;
		}

		if (! empty ( $tag )) {
			$options = $this->addTagString ( $tag, $options );
		} else {
            if( $this->app_user_id > 0 ){
                $options[ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
            }else{
              $options = $this->addkActivatePalteList ( $options );
            }
		}
		if ($app == self::TYPR_APP_CLOUD) {
			$options ['bookmark_id !='] = 0;
		}

		$android_options ['LOCATE("Windows Phone", ua)'] = 0;
		$android_options ['LOCATE("Android", ua) !='] = 0;

		$ios_options ['LOCATE("Windows Phone", ua)'] = 0;
		$ios_options ['OR'] ['LOCATE("iPhone", ua) !='] = 0;
		$ios_options ['OR'] ['LOCATE("iPad", ua) !='] = 0;
		$ios_options ['OR'] ['LOCATE("iPod", ua) !='] = 0;

		// 全件取得
		$all_ua_count = $this->find ( 'count', array (
				'conditions' => $options
		) );

		// Android件数取得
		$android_ua_count = $this->find ( 'count', array (
				'conditions' => array_merge ( $options, $android_options )
		) );
		// iOS件数取得
		$ios_ua_count = $this->find ( 'count', array (
				'conditions' => array_merge ( $options, $ios_options )
		)
		 );
		// その他件数取得
		$other_ua_count = $all_ua_count - $android_ua_count - $ios_ua_count;

		$res ['count'] = $android_ua_count + $ios_ua_count + $other_ua_count;

		$res ['details'] [0] ['count'] = $android_ua_count;
		$res ['details'] [0] ['type'] = "A";

		$res ['details'] [1] ['count'] = $ios_ua_count;
		$res ['details'] [1] ['type'] = "I";

		$res ['details'] [2] ['count'] = $other_ua_count;
		$res ['details'] [2] ['type'] = "O";

		return $res;
	}

    /**
     * トータルタップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getTotalTap($team_id, $app = null, $tag = null)
    {
        return $this->getTap($team_id, null, null, null, $app, $tag);
    }

	/**
	 * OSトータルタップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getTotalTapByOs($team_id, $app = null, $tag = null) {
		return $this->getTapByOs ( $team_id, null, null, null, $app, $tag );
	}

    /**
     * 本日のタップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getDailyTap($team_id, $timezone = 'Asia/Tokyo', $app = null, $tag = null)
    {
        $start_date = $this->convertUTC($timezone, date("Y-m-d 00:00:00"));
        $end_date = $this->convertUTC($timezone, date("Y-m-d 23:59:59"));

        return $this->getTap($team_id, $timezone, $start_date, $end_date, $app, $tag);
    }

	/**
	 * 本日のOS毎のタップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getDailyTapByOs($team_id, $timezone = 'Asia/Tokyo', $app = null, $tag = null) {
		$start_date = $this->convertUTC ( $timezone, date ( "Y-m-d 00:00:00" ) );
		$end_date = $this->convertUTC ( $timezone, date ( "Y-m-d 23:59:59" ) );
		return $this->getTapByOs ( $team_id, $timezone, $start_date, $end_date, $app, $tag );
	}

    /**
     * 週間のタップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getWeeklyTap($team_id, $timezone = 'Asia/Tokyo', $app = null, $tag = null)
    {
        $start_date = $this->convertUTC($timezone, date("Y-m-d 00:00:00", strtotime('-1 week +1 day')));
        $end_date = $this->convertUTC($timezone, date("Y-m-d 23:59:59"), strtotime('now'));

        return $this->getTap($team_id, $timezone, $start_date, $end_date, $app, $tag);
    }

	/**
	 * 週間のOS毎タップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getWeeklyTapByOs($team_id, $timezone = 'Asia/Tokyo', $app = null, $tag = null) {
		$start_date = $this->convertUTC ( $timezone, date ( "Y-m-d 00:00:00", strtotime ( '-1 week +1 day' ) ) );
		$end_date = $this->convertUTC ( $timezone, date ( "Y-m-d 23:59:59" ), strtotime ( 'now' ) );

		return $this->getTapByOs ( $team_id, $timezone, $start_date, $end_date, $app, $tag );
	}

    /**
     * 月間のタップ数を取得
     * @param $team_id
     * @return mixed
     */
    public function getMonthlyTap($team_id, $timezone = 'Asia/Tokyo', $app = null, $tag = null)
    {
        $start_date = $this->convertUTC($timezone, date("Y-m-d 00:00:00", strtotime('-30 day')));
        $end_date = $this->convertUTC($timezone, date("Y-m-d 23:59:59"), strtotime('now'));

        return $this->getTap($team_id, $timezone, $start_date, $end_date, $app, $tag);
    }

	/**
	 * 月間のOS毎タップ数を取得
	 *
	 * @param
	 *        	$team_id
	 * @return mixed
	 */
	public function getMonthlyTapByOs($team_id, $timezone = 'Asia/Tokyo', $app = null, $tag = null) {
		$start_date = $this->convertUTC ( $timezone, date ( "Y-m-d 00:00:00", strtotime ( '-30 day' ) ) );
		$end_date = $this->convertUTC ( $timezone, date ( "Y-m-d 23:59:59" ), strtotime ( 'now' ) );

		return $this->getTapByOs ( $team_id, $timezone, $start_date, $end_date, $app, $tag );
	}

    /**
     * タイプ毎のタップ数
     */
    public function getTotalTapByTagAndType($team_id, $tag, $type, $start_date = null, $end_date = null, $app)
    {
		$team_id = intval ( $team_id );
		$conditions = array (
				'team_id' => $team_id
		);

		if (! empty ( $tag )) {
			$conditions = $this->addTagString ( $tag, $conditions );
		} else {
            if( $this->app_user_id > 0 ){
                $conditions[ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
            }else{
              $conditions = $this->addkActivatePalteList ( $conditions );
            }
		}
		if ($app == self::TYPR_APP_CLOUD) {
			$conditions ['bookmark_id !='] = 0;
		}

		if ($start_date) {
			$conditions ['created_at >='] = $start_date;
		}
		if ($end_date) {
			$conditions ['created_at <='] = $end_date;
		}
		if (! empty ( $type )) {
			$conditions ['p_type'] = $type;
		}

		// if ($app) { $conditions['app'] = $app; }
		if ($app == 'cf') {
			$conditions ['bookmark_id !='] = 0;
		}

		$conditions = $this->addTagString ( $tag, $conditions );

		$res = $this->find ( 'count', array (
				'conditions' => $conditions,
				'fields' => array (
						'id'
				)
		) );
		if (empty ( $res )) {
			$res = 0;
		}

		return $res;
    }

    /**
     * 日毎のタップ数
     */
    public function getTotalTapByTagAndDate($team_id, $tag, $start_date = null, $end_date = null, $app)
    {
		$team_id = intval ( $team_id );
		$conditions = array (
				'team_id' => $team_id
		);

		if (! empty ( $tag )) {
			$conditions = $this->addTagString ( $tag, $conditions );
		} else {
            if( $this->app_user_id > 0 ){
                $conditions[ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
            }else{
              $conditions = $this->addkActivatePalteList ( $conditions );
            }
		}
		if ($app == self::TYPR_APP_CLOUD) {
			$conditions ['bookmark_id !='] = 0;
		}

		if ($start_date) {
			$conditions ['created_at >='] = $start_date;
		}
		if ($end_date) {
			$conditions ['created_at <='] = $end_date;
		}
		// if ($app) { $conditions['app'] = $app; }
		if ($app == 'cf') {
			$conditions ['bookmark_id !='] = 0;
		}

		$t = new DateTime ( "2015-1-1 00:00", new DateTimeZone ( $this->target_time_zone ) );
		$time_offset = $t->getOffset () / 60 / 60;
		if ($time_offset > 0) {
			$sign = '+';
		} else {
			$sign = '-';
		}
		$time_offset = abs ( $time_offset );

		$conditions = $this->addTagString ( $tag, $conditions );

		$res = $this->find ( 'all', array (
				'conditions' => $conditions,
				'fields' => array (
						"DATE_FORMAT(created_at $sign INTERVAL $time_offset HOUR,'%Y-%m-%d') as _date",
						"COUNT(*) as _count"
				),
				'group' => array (
						"DATE_FORMAT(created_at $sign INTERVAL $time_offset HOUR,'%Y-%m-%d')"
				),
				'order' => array (
						'_date'
				)
		) );
		return $res;
    }

    /**
     * 日毎のタップ数
     */
    public function getTotalTapByContentAndDate($team_id, $bookmark_id, $start_date = null, $end_date = null, $app)
    {
        $team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id );
        if( !empty($bookmark_id)){
          $conditions['bookmark_id'] = $bookmark_id;
        }else{
          //$conditions['bookmark_id !='] = 0;
        }
        if ($start_date)   { $conditions['created_at >='] = $start_date; }
        if ($end_date)   { $conditions['created_at <='] = $end_date; }
       // if ($app)   { $conditions['app'] = $app; }

        $t = new DateTime("2015-1-1 00:00", new DateTimeZone($this->target_time_zone));
        $time_offset = $t->getOffset()/60/60;
        if( $time_offset > 0 ){
          $sign = '+';
        }else{
          $sign = '-';
        }
        $time_offset = abs($time_offset);

        $res = $this->find( 'all', array( 'conditions'  => $conditions,
                                          'fields'      => array( "DATE_FORMAT(created_at $sign INTERVAL $time_offset HOUR,'%Y-%m-%d') as _date","COUNT(*) as _count" ),
                                          'group'       => array( "DATE_FORMAT(created_at $sign INTERVAL $time_offset HOUR,'%Y-%m-%d')" ),
                                          'order'       => array( '_date' )
                                ));
        return $res;
    }
    /**
     * 時間毎のタップ数
     */
    public function getTotalTapByTagAndTime($team_id, $tag, $type, $start_date = null, $end_date = null, $app, $time_type = self::TYPE_HOUR)
    {
        $team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id );

        if ($start_date)   { $conditions['created_at >='] = $start_date; }
        if ($end_date)   { $conditions['created_at <='] = $end_date; }
        if ($app == 'cf') {
          $conditions['bookmark_id !='] = 0;
        }
       // if ($app)   { $conditions['app'] = $app; }

        if ($type)   { $conditions['p_type'] = $type; }

        $conditions = $this->addTagString( $tag, $conditions);
            if( $this->app_user_id > 0 ){
                $conditions[ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
            }else{
              $conditions = $this->addkActivatePalteList ( $conditions );
            }

        if( $time_type == self::TYPE_HOUR) {
          $res = $this->find( 'all', array( 'conditions'  => $conditions,
                                            'fields'      => array( "hour(created_at) as _time","COUNT(*) as _count" ),
                                            'group'       => array( "hour(created_at)" ),
                                            'order'       => array( '_time' )
                                  ));
        } else {
          $res = $this->find( 'all', array( 'conditions'  => $conditions,
                                            'fields'      => array( "minute(created_at) as _time","COUNT(*) as _count" ),
                                            'group'       => array( "minute(created_at)" ),
                                            'order'       => array( '_time' )
                                  ));
        }
        return $res;
    }

    /**
     * 時間毎のタップ数
     */
    public function getTotalTapByContentsAndTime($team_id, $bookmark_id, $type, $start_date = null, $end_date = null, $app, $time_type = self::TYPE_HOUR)
    {
        $team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id);
        if ($start_date)   { $conditions['created_at >='] = $start_date; }
        if ($end_date)   { $conditions['created_at <='] = $end_date; }
        if ( !empty($bookmark_id) ) {
          $conditions['bookmark_id'] = $bookmark_id;
        }
       // if ($app)   { $conditions['app'] = $app; }

        if ($type)   { $conditions['p_type'] = $type; }

        if( $time_type == self::TYPE_HOUR) {
          $res = $this->find( 'all', array( 'conditions'  => $conditions,
                                            'fields'      => array( "hour(created_at) as _time","COUNT(*) as _count" ),
                                            'group'       => array( "hour(created_at)" ),
                                            'order'       => array( '_time' )
                                  ));
        } else if( $time_type == self::TYPE_MINUTE) {
          $res = $this->find( 'all', array( 'conditions'  => $conditions,
                                            'fields'      => array( "minute(created_at) as _time","COUNT(*) as _count" ),
                                            'group'       => array( "minute(created_at)" ),
                                            'order'       => array( '_time' )
                                  ));
        }

        return $res;
    }
    /**
     * タイプ毎のタップ数
     */
    public function getTotalTapByTeamAndType($team_id, $type, $arg, $start_date = null, $end_date = null, $app = null)
    {
        //$team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id );

        if ($start_date)  { $conditions['created_at >='] = $start_date; }
        if ($end_date)    { $conditions['created_at <='] = $end_date; }
        if ($type)        { $conditions['p_type'] = $type; }
       // if ($app)   { $conditions['app'] = $app; }
        if ($app != 'sp') {
          $conditions['bookmark_id !='] = 0;
        }
            if( $this->app_user_id > 0 ){
                $conditions[ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
            }else{
              $conditions = $this->addkActivatePalteList ( $conditions );
            }
        $res = $this->find( 'all', array( 'conditions'  => $conditions,
                                            'fields'      => array( "CONCAT(p_head,'.',p_lot,'.',p_num) as tag",'COUNT(id) as tap_count' ),
                                            'group'       => array( "CONCAT(p_head,'.',p_lot,'.',p_num)"),
                                            'order'       => array( "tap_count" => $arg['direction'] ),
                                            'limit'       => $arg['limit'],
                                            'offset'      => $arg['offset']
                                ));
        $res_array = array();

        $tagModel = new Tag();
        foreach ($res as $key => $data) {
          if(empty($type)){
            $res_array[$data[0]['tag']]['total'] = intval($data[0]['tap_count']);
            $res_tag = $tagModel->find( 'first', array( 'conditions'  => array('tag'=>$data[0]['tag']),
                                            'fields' => array('icon','name') ) );
            $res_array[$data[0]['tag']]['name'] = $res_tag['Tag']['name'];
            $res_array[$data[0]['tag']]['tag'] = $data[0]['tag'];
            if( !empty($res_tag['Tag']['icon']) ){
              $filename = Router::fullbaseUrl() . DS . 'upload' . DS . 'plate' . DS . $res_tag['Tag']['icon'];
              $res_array[$data[0]['tag']]['icon'] = $filename;
            }else{
              $res_array[$data[0]['tag']]['icon'] = substr($data[0]['tag'], 0,2).substr($data[0]['tag'], 3,5).'.jpg';
              $res_array[$data[0]['tag']]['icon'] = Router::fullbaseUrl() . DS . 'plate_thumb' . DS . $res_array[$data[0]['tag']]['icon'];
            }
          }else
            $res_array[$data[0]['tag']][$type] = intval($data[0]['tap_count']);
        }
        return $res_array;
    }
    /**
     * タイプ毎のタップ数(Contents)
     */
    public function getTotalContentsByTeamAndType($team_id, $type, $arg, $start_date = null, $end_date = null, $app = null)
    {
        //$team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id );

        if ($start_date)  { $conditions['created_at >='] = $start_date; }
        if ($end_date)    { $conditions['created_at <='] = $end_date; }
        if ($type)        { $conditions['p_type'] = $type; }
        if ($app !== 'sp') {
          //$conditions['bookmark_id !='] = 0;
            $count_target = "bookmark_id,contents";
        }else{
          $count_target = "contents";
        }
        $options = array( 'conditions'  => $conditions,
                                            'fields'      => array( "contents as contents",'count(id) as tap_count','bookmark_id' ),
                                            'group'       => array( $count_target )
                                );
        
        if (!empty($arg['direction']))  { $options['order'] = array( "tap_count" => $arg['direction'] ); }
        if (!empty($arg['limit']))  { $options['limit'] = $arg['limit']; }
        if (!empty($arg['offset']))  { $options['offset'] = $arg['offset']; }
                  
        $res = $this->find( 'all', $options );
        $res_array = array();
        $bookmark = new Bookmark();
        foreach ($res as $key => $value) {
          $data = $value['AccessLog'];
          $data['tap_count'] = $value[0]['tap_count'];

          $contents = $data['contents'];
          $contents = str_replace('http://plate.id/ad/sp.php?url=', '', $contents);
          $contents = str_replace('http://plate.id/ad/spc.php?url=', '', $contents);
          $contents = str_replace('http://plate.id/ad/carryfree.php?url=', '', $contents);
          $contents = str_replace('http://plate.id/ad/check.php?url=', '', $contents);

          if ($app !== 'sp') {
             $index =  $data['contents'].'_'.$data['bookmark_id'];
          }else{
             $index =  $contents;
          }
          if(empty($type)){
            $res_array[$index]['contents'] = $contents;
            $res_array[$index]['total'] = intval($data['tap_count']);
            $res_array[$index]['bookmark_id'] = $data['bookmark_id'];
            $res_array[$index]['icon'] = '';
            $res_array[$index]['name'] = '';
            if( !empty($data['bookmark_id']) ){
              $res_book = $bookmark->find( 'first', array( 'conditions'  => array('id'=>$data['bookmark_id']),
                                              'fields' => array('image','name') ) );
              if( $res_book ){
              $res_array[$index]['name'] = $res_book['Bookmark']['name'];
              if( !empty($res_book['Bookmark']['image']) ){
                $filename =  Bookmark::imageURL( $res_book['Bookmark']['image'] );
                $res_array[$index]['icon'] = $filename;
              }
              }
            }
          }else{
            $res_array[$index][$type] = intval($data['tap_count']);
          }
        }
        return $res_array;
    }

    /**
     * 期間中の稼働プレート数
     */
    public function getTotalPlateCountByTeam($team_id, $start_date = null, $end_date = null, $app = null)
    {

        $team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id );

        if ($start_date)   { $conditions['created_at >='] = $start_date; }
        if ($end_date)   { $conditions['created_at <='] = $end_date; }
       // if ($app)   { $conditions['app'] = $app; }
        if ($app != 'sp') {
          $conditions['bookmark_id !='] = 0;
        }

            if( $this->app_user_id > 0 ){
                $conditions[ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
            }else{
              $conditions = $this->addkActivatePalteList ( $conditions );
            }

        $res = $this->find( 'count', array( 'conditions'  => $conditions,
                                            'fields'      => array( "CONCAT(p_head,'.',p_lot,'.',p_num) as tag"),
                                            'group'       => array( "CONCAT(p_head,'.',p_lot,'.',p_num)" ),
                                            'order'       => array( "tag" )
                                ));
        if( empty($res) ){ $res=0; }
        return $res;
    }

    /**
     * 期間中の稼働コンテンツ数
     */
    public function getTotalContentsCountByTeam($team_id, $start_date = null, $end_date = null, $app = null)
    {
        $team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id );

        if ($start_date)   { $conditions['created_at >='] = $start_date; }
        if ($end_date)   { $conditions['created_at <='] = $end_date; }
        if ($app !== 'sp') {
          //$conditions['bookmark_id !='] = 0;
          $count_target = "bookmark_id,contents";
        }else{
          $count_target = "contents";
        }

        //$conditions = $this->addkActivatePalteList( $conditions );

        $res = $this->find( 'count', array( 'conditions'  => $conditions,
                                            'fields'      => array( "id" ),
                                            'group'       => array( $count_target),
                                            'order'       => array( $count_target )
                                ));

        if( empty($res) ){ $res=0; }

        return $res;
    }
    /**
     * 初回タップ日時
     */
    public function getFirstPlateAccessByTeam($team_id, $app = null)
    {
        $team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id );

       // if ($app)   { $conditions['app'] = $app; }


        $res = $this->find( 'first', array( 'conditions'  => $conditions,
                                            'fields'      => array( "MIN(created_at) as min_date" )
                                ));

        $min_date =  $res[0]['min_date'];
        return $min_date;
    }

    /**
     * 初回タップ日時
     */
    public function getFirstContentsAccessByTeam($team_id, $app = null)
    {
        $team_id = intval($team_id);

        $conditions = array( 'team_id'  => $team_id,'bookmark_id !=' => 0 );

       // if ($app)   { $conditions['app'] = $app; }


        $res = $this->find( 'first', array( 'conditions'  => $conditions,
                                            'fields'      => array( "MIN(created_at) as min_date" )
                                ));

        $min_date =  $res[0]['min_date'];
        return $min_date;
    }
    /**
     * ユニークユーザ数
     * @param $team_ids number in array
     * @return ユニークユーザ数
     */
    public function countUniqueUser($team_ids, $type, $target, $time_zone, $start_date = null, $end_date = null, $app = null)
    {
      $result = null;
      if( is_array($team_ids) ){
        $options = array( 'conditions' => array( 'team_id'  => $team_ids ) );
      }

      if( !empty($target) ) {
        if( $type == self::TYPE_PLATE ){
          $options['conditions'] = $this->addTagString($target,$options['conditions']);
        }else if( $type == self::TYPE_CONTENTS ){
          $options['conditions']['bookmark_id'] = $target;
        }
      }else{
        if( $type == self::TYPE_PLATE ){
                if( $this->app_user_id > 0 ){
                    $options['conditions'][ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
                }else{
                  $options['conditions'] = $this->addkActivatePalteList ( $options['conditions'] );
                }
        }
      }
      if ($start_date)   { $options['conditions']['created_at >='] = $start_date; }
      if ($end_date)   { $options['conditions']['created_at <='] = $end_date; }

      $options['group'] = 'cookie';

      $result = $this->find( 'count', $options);
      
      return $result;
   }
    /**
     * リピートユーザ数
     * @param $team_ids number in array
     * @return リピートユーザ数
     */
    public function countRepeatUser($team_ids, $type, $target, $time_zone, $start_date = null, $end_date = null, $app = null)
    {
      $result = null;

      $team_ids_string = '';
      if( is_array($team_ids) ){
        foreach ($team_ids as $key => $value) {
          if( $team_ids_string != '' ){ $team_ids_string .= ','; }
          $team_ids_string .= $value;
        }
      }

      $sql_target = '';
      $active_plate_string = '';
      if( !empty($target) ) {
        if( $type == self::TYPE_PLATE ){
            $tag = explode('.', $target);
            $p_head = $tag[0];
            $p_lot  = $tag[1];
            $p_num  = $tag[2];
          $sql_target = " AND p_head = '$p_head' AND p_lot = $p_lot AND p_num = $p_num";
        }else if( $type == self::TYPE_CONTENTS ){
          $sql_target = " AND bookmark_id = $target";
        }
      }else{
        if( $type == self::TYPE_PLATE ){
                if( $this->app_user_id > 0 ){
                  $active_plate_string = $this->addkFilterAppUserPalteListString("");
                }else{
                  $active_plate_string = $this->addkActivatePalteListString("");
                }
        }
      }
      $sql =  "SELECT cookie FROM access_log
                      WHERE team_id in ($team_ids_string)
                        AND created_at < '$start_date' $sql_target $active_plate_string";
      $sql .= " GROUP BY cookie";

      $result = $this->query( $sql );

      $result = $this->removeArrayWrapper('access_log', $result);
      $cookies = '';

      if( empty($result) ){
        // 期間以前のログなしのためリピートユーザもなし
        return 0;
      }

      foreach ($result as $key => $value) {
        if( $cookies != '' ){ $cookies .= ','; }
        $cookies .= "'".$value['cookie']."'";
      }
      $cookies = "AND cookie in( $cookies )";

      $sql = "SELECT count(id) as rep FROM access_log
                WHERE team_id in ($team_ids_string)
                  $cookies
                  AND created_at >= '$start_date'
                  AND created_at <= '$end_date'
                  $active_plate_string
                  $sql_target";

      $sql .= " GROUP BY cookie";

      $result = $this->query( $sql );
      $result = $this->getNumRows();

      return $result;
   }
	/**
	 * OS毎ユーザエージェント数数
	 *
	 * @param $team_ids number
	 *        	in array
	 * @return ユーザエージェント数数
	 */
	public function getUACount($team_ids, $type, $bookmark_id, $_tag, $time_zone, $start_date = null, $end_date = null, $app = null) {

		$team_ids = intval ( $team_ids );
		$options = array (
				'team_id' => $team_ids
		);

		if ($type == self::TYPE_PLATE) {
			if (! empty ( $_tag )) {
				$options = $this->addTagString ( $_tag, $options );
			} else {
                if( $this->app_user_id > 0 ){
                    $options[ "CONCAT(p_head,'.',p_lot,'.',p_num)" ] = self::$filter_user_plate_list;
                }else{
                  $options = $this->addkActivatePalteList ( $options );
                }
			}
			if ($app == self::TYPR_APP_CLOUD) {
				$options ['bookmark_id !='] = 0;
			}
		} else if ($type == self::TYPE_CONTENTS) {
			if ($app == self::TYPR_APP_CLOUD && ! empty ( $bookmark_id )) {
				$options ['bookmark_id'] = $bookmark_id;
			}
		}

		if ($start_date) {
			$options ['created_at >='] = $start_date;
		}
		if ($end_date) {
			$options ['created_at <='] = $end_date;
		}

		$android_options ['LOCATE("Windows Phone", ua)'] = 0;
		$android_options ['LOCATE("Android", ua) !='] = 0;

		$ios_options ['LOCATE("Windows Phone", ua)'] = 0;
		$ios_options ['OR'] ['LOCATE("iPhone", ua) !='] = 0;
		$ios_options ['OR'] ['LOCATE("iPad", ua) !='] = 0;
		$ios_options ['OR'] ['LOCATE("iPod", ua) !='] = 0;

		// 全件取得
		$all_ua_count = $this->find ( 'count', array (
				'conditions' => $options
		) );


		// Android件数取得
		$android_ua_count = $this->find ( 'count', array (
				'conditions' => array_merge ( $options, $android_options )
		) );
		// iOS件数取得
		$ios_ua_count = $this->find ( 'count', array (
				'conditions' => array_merge ( $options, $ios_options )
		) );
		// その他件数取得
		$other_ua_count = $all_ua_count - $android_ua_count - $ios_ua_count;

		$res ['analytics'] ['and'] = $android_ua_count;
		$res ['analytics'] ['ios'] = $ios_ua_count;
		$res ['analytics'] ['oth'] = $other_ua_count;


		return $res;
	}
}

?>