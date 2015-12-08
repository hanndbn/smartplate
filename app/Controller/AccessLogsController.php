<?php

App::uses('AppController', 'Controller');
App::uses('ContentsAllAPIController', 'Controller');
App::uses('ConnectionManager', 'Model');

/**
 * Controller for Accesslog model
 * Showing record data from accsess_log table to view
 * @package       app.Controller
 *
 */
class AccessLogsController extends AppController
{

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Management');
        $this->Auth->allow();
    }

    /*
     * Get access_log datas
     * 
     * To render Pie chart (iphone, android, other)
     * Showing total NFC, QR, OS to view
     * 
     * @param empty
     * @return array data from access_log table. 
     */

    public function getalldata()
    {

        $query_date = date('Y-m-d');
        $erlday = $this->AccessLog->convertUTC('Asia/Tokyo',date('Y-m-d 00:00:00'));
        $nighday = $this->AccessLog->convertUTC('Asia/Tokyo',date('Y-m-d 24:00:00'));
        //$erlday = date('Y-m-d', strtotime(date('Y-m-d 00:00:00')));
        //$nighday = date('Y-m-d', strtotime(date('Y-m-d 24:00:00')));
        // First day of the month.
        $fday = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $lday = date('Y-m-t', strtotime($query_date));
        if (!$this->Session->check('Auth.User') || $this->request->prefix == 'system') {
            /* Total datas */
            $ttdatas = $this->AccessLog->find('count');

            /* Total access in day */
            $accdaily = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday))
            ));
            /* Total access in month */
            $accmonthly = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday))
            ));
            /* Show OS data */
            $iphone = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.ua LIKE' => '%iPhone%')
            ));
            $android = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.ua LIKE' => '%Android%')
            ));
            // Other data
            $other = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'NOT' => array(
                        'AccessLog.ua' => '',
                        'AccessLog.ua LIKE' => '%Android%',
                        'AND' => array('AccessLog.ua LIKE' => '%iPhone%')
                    ),
                )
            ));
            /* Show NFC and QR */
            $nfc = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.p_type' => 'N')
            ));
            $qr = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.p_type' => 'Q')
            ));
        } elseif ($this->request->prefix != 'system' && $this->Session->check('Auth.User')) {

            $t_ids = $this->getTeamid($this->Auth->user('authority'));
            /* Total datas */
            $ttdatas = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'team_id' => $t_ids
                )
            ));

            /* Total access in day */
            $accdaily = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday),
                    'team_id' => $t_ids
                )
            ));
            /* Total access in month */
            $accmonthly = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday),
                    'team_id' => $t_ids
                )
            ));
            /* Show OS data */
            $iphone = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.ua LIKE' => '%iPhone%',
                    'team_id' => $t_ids
                )
            ));
            $android = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.ua LIKE' => '%Android%',
                    'team_id' => $t_ids
                )
            ));
            // Other data
            $other = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'NOT' => array(
                        'AccessLog.ua' => '',
                        'AccessLog.ua LIKE' => '%Android%',
                        'AND' => array('AccessLog.ua LIKE' => '%iPhone%')
                    ),
                    'team_id' => $t_ids
                )
            ));
            /* Show NFC and QR */
            $nfc = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.p_type' => 'N',
                    'team_id' => $t_ids
                )
            ));
            $qr = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.p_type' => 'Q',
                    'team_id' => $t_ids
                )
            ));
        }
        if (isset($this->params['requested'])) {
            return array(
                'daily' => $accdaily,
                'monthly' => $accmonthly,
                'total' => $ttdatas,
                'iphone' => $iphone,
                'android' => $android,
                'other' => $other,
                'nfc' => $nfc,
                'qr' => $qr);
        }
    }

    /**
     * Get Content Status by day, month
     */
    public function contentStatus()
    {
        $query_date = date('Y-m-d');
        $erlday = $this->AccessLog->convertUTC('Asia/Tokyo',date('Y-m-d 00:00:00'));
        $nighday = $this->AccessLog->convertUTC('Asia/Tokyo',date('Y-m-d 24:00:00'));
        //$erlday = date('Y-m-d', strtotime(date('Y-m-d 00:00:00')));
       // $nighday = date('Y-m-d', strtotime(date('Y-m-d 24:00:00')));
        // First day of the month.
        $fday = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $lday = date('Y-m-t', strtotime($query_date));
        /* Query content status in day */
        $accdaily = $this->AccessLog->find('count', array(
            'conditions' => array(
                'AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday),
                "AccessLog.contents != ''"),
            'fields' => array('AccessLog.contents'),
                'group' => array('AccessLog.contents')
        ));
        /* Query content status in month */
        $accmonthly = $this->AccessLog->find('count', array(
            'conditions' => array(
                'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday), "AccessLog.contents != ''"),
            'fields' => array('AccessLog.contents'),
                'group' => array('AccessLog.contents')
        ));

        if ($this->request->prefix != 'system' && $this->Session->check('Auth.User')) {
            $t_ids = $this->getTeamid($this->Auth->user('authority'));
            $accdaily = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday),
                    "AccessLog.contents != ''",
                    'team_id' => $t_ids
                ),
                'fields' => array('AccessLog.contents'),
                'group' => array('AccessLog.contents')
            ));
            /* Query content status in month */
            $accmonthly = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday), "AccessLog.contents != ''", 'team_id' => $t_ids),
                'fields' => array('AccessLog.contents'),
                'group' => array('AccessLog.contents')
            ));
        }
        if (isset($this->params['requested'])) {
            return $datas = (array(
                'daily' => $accdaily,
                'monthly' => $accmonthly
            ));
        }
    }

    /**
     * Get number plate by day, month
     */
    public function plateStatus()
    {
        $query_date = date('Y-m-d');
        $erlday = $this->AccessLog->convertUTC('Asia/Tokyo',date('Y-m-d 00:00:00'));
        $nighday = $this->AccessLog->convertUTC('Asia/Tokyo',date('Y-m-d 24:00:00'));
       // $erlday = date('Y-m-d', strtotime(date('Y-m-d 00:00:00')));
      //  $nighday = date('Y-m-d', strtotime(date('Y-m-d 24:00:00')));
        // First day of the month.
        $fday = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $lday = date('Y-m-t', strtotime($query_date));
        
        if ($this->request->prefix != 'system' && $this->Session->check('Auth.User')) {
            $t_ids = $this->getTeamid($this->Auth->user('authority'));
            /* Query plate status in day */
            $accdaily = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday), "AccessLog.path != '' ", 'team_id' => $t_ids),
                'fields' => array('CONCAT(p_head, p_lot, p_num)'),
                'group' => array('CONCAT(p_head, p_lot, p_num)')
            ));
            /* Query plate status in month */
            $accmonthly = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday), array("AccessLog.path != '' ", 'team_id' => $t_ids)),
                'fields' => array('CONCAT(p_head, p_lot, p_num)'),
                'group' => array('CONCAT(p_head, p_lot, p_num)')
            ));
            /* Query platinum plate */
            $platinumplate = $this->AccessLog->platinum_query($t_ids);
        }else{
            /* Query plate status in day */
            $accdaily = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday), "AccessLog.path != '' "),
                    'fields' => array('CONCAT(p_head, p_lot, p_num)'),
                    'group' => array('CONCAT(p_head, p_lot, p_num)')
            ));
            /* Query plate status in month */
            $accmonthly = $this->AccessLog->find('count', array(
                'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday), array("AccessLog.path != '' ")),
                    'fields' => array('CONCAT(p_head, p_lot, p_num)'),
                    'group' => array('CONCAT(p_head, p_lot, p_num)')
            ));
            /* Query platinum plate */
            $platinumplate = $this->AccessLog->platinum_query();
        }
        if (isset($this->params['requested'])) {
          if( empty($accdaily) ){ $accdaily = 0;}
          if( empty($accmonthly) ){ $accmonthly = 0;}
          if( empty($platinumplate) ){ $platinumplate = 0;}
            return $datas = (array(
                'daily' => $accdaily,
                'monthly' => $accmonthly,
                'platinum' => $platinumplate
            ));
        }
    }

    /**
     * Display data in dashboard line chart
     *
     * @return json
     */
    function ajaxDatePicker()
    {
        if ($this->request->is('post')) {
            $requestData = $this->request->data;
            $from = $requestData['from'];
            $to = $requestData['to'];
            // Get the number of days between 2 date
            $date1 = new DateTime($from);
            $date2 = new DateTime($to);
            $difference = $date1->diff($date2);
            $numberOfDay = $difference->days;
            $t_ids = $this->getTeamid($this->Auth->user('authority'));
            if ($numberOfDay == 0) {
                for ($h = 0; $h < 24; $h++) {
                    $tth[] = date('Y M d H:s', strtotime('+' . $h . 'hour', strtotime($from)));
                    $hours[] = date('H:i:s', strtotime('+' . $h . 'hour', strtotime($to)));
                }
                $dts = implode(",", $tth);

                /* Query data */
                foreach ($hours as $hour) {
                    $fhour = date('Y-m-d H:i:s', strtotime(date("$from $hour")));
                    $schour = date('Y-m-d H:i:s', strtotime('+ 1 hour', strtotime($fhour)));
                    if (!$this->Session->check('Auth.User') || $this->request->prefix == 'system') {
                        $countplates = $this->countPlates($fhour, $schour);
                        $nq = $countplates['N'];
                        $qrs = $countplates['Q'];
                        $tt = $nq + $qrs;
                    } elseif ($this->request->prefix != 'system' && $this->Session->check('Auth.User')) {
                        if ($this->Session->read('Access.screen') == 'bookmark') { // Get data for bookmark detail screen
                            $bookmarkID = $this->Session->read('Access.id');
                            $countplates = $this->countPlates($fhour, $schour, $bookmarkID);
                            $nq = $countplates['N'];
                            $qrs = $countplates['Q'];
                            $tt = $nq + $qrs;
                        } elseif ($this->Session->read('Access.screen') == 'plate') { // Get data for plate detail screen
                            $path = $this->Session->read('Access.tag');
                            $countplates = $this->countPlates($fhour, $schour, null, $path);
                            $nq = $countplates['N'];
                            $qrs = $countplates['Q'];
                            $tt = $nq + $qrs;
                        } else {
                            $countplates = $this->countPlates($fhour, $schour, null, null, $t_ids);
                            $nq = $countplates['N'];
                            $qrs = $countplates['Q'];
                            $tt = $nq + $qrs;
                        }
                    }
                    $nds[] = $nq;
                    $qds[] = $qrs;
                    $tds[] = $tt;
                }
                /* Data response */
                $nfc = join(",", $nds);
                $qr = join(",", $qds);
                $total = join(",", $tds);
                $catd = $dts;
            } else {
                for ($m = $numberOfDay; $m >= 0; $m--) {
                    $displayDate[] = date('Y M d', strtotime('-' . $m . 'day', strtotime($to)));
                    $days[] = date('Y-m-d', strtotime('-' . $m . 'day', strtotime($to)));
                }
                $dmt = join(",", $displayDate);
                /* Query */
                foreach ($days as $day) {
                    $fday = date('Y-m-d', strtotime(date("$day  00:00:00")));
                    $lday = date('Y-m-d', strtotime(date("$day 24:00:00")));
                    if (!$this->Session->check('Auth.User') || $this->request->prefix == 'system') {
                        $countplates = $this->countPlates($fday, $lday);
                        $nm = $countplates['N'];
                        $qm = $countplates['Q'];
                        $ttm = $nm + $qm;
                    } elseif ($this->request->prefix != 'system' && $this->Session->check('Auth.User')) {
                        if ($this->Session->read('Access.screen') == 'bookmark') { // Get data for bookmark detail screen
                            $bookmarkID = $this->Session->read('Access.id');
                            $countplates = $this->countPlates($fday, $lday, $bookmarkID);
                            $nm = $countplates['N'];
                            $qm = $countplates['Q'];
                            $ttm = $nm + $qm;
                        } elseif ($this->Session->read('Access.screen') == 'plate') { // Get data for plate detail screen
                            $path = $this->Session->read('Access.tag');
                            $countplates = $this->countPlates($fday, $lday, null, $path);
                            $nm = $countplates['N'];
                            $qm = $countplates['Q'];
                            $ttm = $nm + $qm;
                        } else {
                            $countplates = $this->countPlates($fday, $lday, null, null, $t_ids);
                            $nm = $countplates['N'];
                            $qm = $countplates['Q'];
                            $ttm = $nm + $qm;
                        }
                    }
                    $nms[] = $nm;
                    $qms[] = $qm;
                    $ttms[] = $ttm;
                }
                /* End */

                /* Data response */
                $nfc = join(",", $nms);
                $qr = join(",", $qms);
                $total = join(",", $ttms);
                $catd = $dmt;
            }
            echo json_encode(array($nfc, $qr, $total, $catd));
            exit;
        }
    }


    /**
     * Count NFC and QR plates between $startTime and $endTime
     * @param string $startTime start time query
     * @param string $endTime end time query
     * @param int $bookmarkID (optional)
     * @param string $path (optional) tag:tag
     * @param int $teamID (optional) team_id of user login
     * @return mixed
     */
    private
    function countPlates($startTime, $endTime, $bookmarkID = null, $path = null, $teamID = null)
    {
        $startTime = $this->AccessLog->convertUTC('Asia/Tokyo', $startTime);
        $endTime = $this->AccessLog->convertUTC('Asia/Tokyo', $endTime);
        $result = array();
        if ($bookmarkID) {
            $countplates = $this->AccessLog->query(
                "SELECT SUM(CASE WHEN `AccessLog`.`p_type` = 'N' THEN 1 ELSE 0 END) as N_count
                    , SUM(CASE WHEN `AccessLog`.`p_type` = 'Q' THEN 1 ELSE 0 END) as Q_count
                    FROM `access_log`.`access_log` as AccessLog
                    WHERE (`AccessLog`.`p_type` IN ('N','Q'))
                    AND `AccessLog`.`created_at` BETWEEN '{$startTime}' AND '{$endTime}'
                    AND `AccessLog`.`bookmark_id` = $bookmarkID"
            );
        } elseif ($path) {
            $path = explode('.', $path);
            $countplates = $this->AccessLog->query(
                "SELECT SUM(CASE WHEN `AccessLog`.`p_type` = 'N' THEN 1 ELSE 0 END) as N_count
                    , SUM(CASE WHEN `AccessLog`.`p_type` = 'Q' THEN 1 ELSE 0 END) as Q_count
                    FROM `access_log`.`access_log` as AccessLog
                    WHERE (`AccessLog`.`p_type` IN ('N','Q'))
                    AND `AccessLog`.`created_at` BETWEEN '{$startTime}' AND '{$endTime}'
                    AND `AccessLog`.`p_head` = '{$path[0]}'
                    AND `AccessLog`.`p_lot` = $path[1]
                    AND `AccessLog`.`p_num` = $path[2]"
            );
        } elseif ($teamID) {
            if (is_array($teamID))
                $teamID = implode(',', $teamID);
            $countplates = $this->AccessLog->query(
                "SELECT SUM(CASE WHEN `AccessLog`.`p_type` = 'N' THEN 1 ELSE 0 END) as N_count
                    , SUM(CASE WHEN `AccessLog`.`p_type` = 'Q' THEN 1 ELSE 0 END) as Q_count
                    FROM `access_log`.`access_log` as AccessLog
                    WHERE (`AccessLog`.`p_type` IN ('N','Q'))
                    AND `AccessLog`.`created_at` BETWEEN '{$startTime}' AND '{$endTime}'
                    AND `AccessLog`.`team_id` IN ($teamID)"
            );
        } else {
            $countplates = $this->AccessLog->query(
                "SELECT SUM(CASE WHEN `AccessLog`.`p_type` = 'N' THEN 1 ELSE 0 END) as N_count
                    , SUM(CASE WHEN `AccessLog`.`p_type` = 'Q' THEN 1 ELSE 0 END) as Q_count
                    FROM `access_log`.`access_log` as AccessLog
                    WHERE (`AccessLog`.`p_type` IN ('N','Q')) AND `AccessLog`.`created_at` BETWEEN '{$startTime}' AND '{$endTime}'"
            );
        }
        $result['N'] = ($countplates[0][0]['N_count']) ? $countplates[0][0]['N_count'] : 0;
        $result['Q'] = ($countplates[0][0]['Q_count']) ? $countplates[0][0]['Q_count'] : 0;
        return $result;
    }

    /**
     * Count weeks beetwen 2 days
     *
     * @param (month, year)
     * @return int number week beetwen 2 days
     */
    function datediffInWeeks($date1, $date2)
    {
        $first = DateTime::createFromFormat('Y-m-d', $date1);
        $second = DateTime::createFromFormat('Y-m-d', $date2);
        if ($date1 > $date2)
            return datediffInWeeks($date2, $date1);
        return floor($first->diff($second)->days / 7);
    }

    /* calculate number of days in a month
     *
     * @param (month, year)
     * @return int number day of month
     */

    /**
     * for system view
     */
    public
    function system_getstatus()
    {
        $this->getstatus();
    }
   
    /**
     * for system view
     */
    private
    function _getstatusData($fday, $lday)
    {
      $result = array('n'=>0,'q'=>0,'t'=>0); 
      if (!$this->Session->check('Auth.User') || $this->request->prefix == 'system') {
          $countplates = $this->countPlates($fday, $lday);
          $result['n'] = $countplates['N'];
          $result['q'] = $countplates['Q'];
          $result['t'] = $result['n'] + $result['q'];
      } elseif ($this->request->prefix != 'system' && $this->Session->check('Auth.User')) {
          if ($this->Session->read('Access.screen') == 'bookmark') { // Get data for bookmark detail screen
              $bookmarkID = $this->Session->read('Access.id');
              $countplates = $this->countPlates($fday, $lday, $bookmarkID);
              $result['n'] = $countplates['N'];
              $result['q'] = $countplates['Q'];
              $result['t'] = $result['n'] + $result['q'];
          } elseif ($this->Session->read('Access.screen') == 'plate') { // Get data for plate detail screen
              $path = $this->Session->read('Access.tag');
              $countplates = $this->countPlates($fday, $lday, null, $path);
              $result['n'] = $countplates['N'];
              $result['q'] = $countplates['Q'];
              $result['t'] = $result['n'] + $result['q'];
          } else {
              // team_id
              $t_ids = $this->getTeamid($this->Auth->user('authority'));
              
              $countplates = $this->countPlates($fday, $lday, null, null, $t_ids);
              $result['n'] = $countplates['N'];
              $result['q'] = $countplates['Q'];
              $result['t'] = $result['n'] + $result['q'];
          }
      }
      return $result;
    }

    /** Get Access Log status through ajax
     * To render Line chart (NFC, QR, Total)
     *
     * @param null
     * @return json [nfc,qr,total,catd,mydate]
     */
    public
    function getstatus()
    {

        $rq_data = $this->request->data;
        // get type
        $type = $rq_data['type'];
        $current_date = $rq_data['mydate'];
        $cr_week = date('Y-m-d', strtotime($current_date));
        $cr_month = date('Y-m-d', strtotime($current_date));
        $year = date('Y', strtotime($current_date));

        $nav = (int)$rq_data['myNav'];
        // parse about any English textual datetime description into a Unix timestamp
        $ts = strtotime($current_date);
        // calculate the number of days since Monday
        $dow = date('w', $ts);
        $offset = $dow - 1;
        if ($offset < 0) {
            $offset = 6;
        }
        // calculate timestamp for the Monday
        $ts = $ts - $offset * 86400;
        $cal_week = date('Y-m-d', $ts);
        // get Nav to know Previous or Next
        if ($nav == 1) {
            $current_date = date('Y-m-d', strtotime('+ 1 day', strtotime($current_date)));
            $cal_week = date('Y-m-d', strtotime('+ 1 week', strtotime($cal_week)));
            $ts = strtotime($cal_week);
            $cr_week = date('Y-m-d', strtotime('+ 1 week', strtotime($cr_week)));
            $lm = date('Y-m', strtotime('+ 1 month', strtotime($cr_month)));
            $cdim = $this->days_in_month($lm, $year);
            $cr_month = date('Y-m-d', strtotime('+ 1 month', strtotime($cr_month)));
            if ($current_date > date('Y-m-d')) {
                $current_date = date('Y-m-d');
            }
            if ($cr_week > date('Y-m-d')) {
                $cr_week = date('Y-m-d');
            }
            if ($cr_month > date('Y-m-d')) {
                $cr_month = date('Y-m-d');
            }
        } elseif ($nav == -1) {
            $current_date = date('Y-m-d', strtotime('- 1 day', strtotime($current_date)));
            $cal_week = date('Y-m-d', strtotime('- 1 week', strtotime($cal_week)));
            $ts = strtotime($cal_week);
            $cr_week = date('Y-m-d', strtotime('- 1 week', strtotime($cr_week)));
            $lm = date('Y-m', strtotime('- 1 month', strtotime($cr_month)));
            $cdim = $this->days_in_month($lm, $year);
            $cr_month = date('Y-m-d', strtotime('- 1 month', strtotime($cr_month)));
        } else {
            $ts = strtotime(date('Y-m-d', strtotime('- 1 week', strtotime($cal_week))));
            $cr_week = date('Y-m-d', strtotime('- 1 week', strtotime($cr_week)));
            $lm = date('Y-m', strtotime('- 1 month', strtotime($cr_month)));
            $cdim = $this->days_in_month($lm, $year);
            $cr_month = date('Y-m-d', strtotime('- 1 month', strtotime($cr_month)));
        }

        // check Type
        if ($type == 'Weekly') {
            /* Show 7 days in current week */
            // loop from Monday till Sunday
            for ($i = 0; $i < 7; $i++, $ts += 86400) {
                $datetoprint[] = date("d(D)", $ts);
                $dinweek[] = date("Y-m-d", $ts);
            }
            $dwt = join(",", $datetoprint);

            /* Query */
            foreach ($dinweek as $weekly) {
                $erlday = date('Y-m-d', strtotime(date("$weekly  00:00:00")));
                $nighday = date('Y-m-d', strtotime(date("$weekly 24:00:00")));

                $dat = $this->_getstatusData($erlday,$nighday);
                $nws[] = $dat['n'];
                $qws[] = $dat['q'];
                $ttws[] = $dat['t'];
            }
            /* End */

            /* Data response */
            $nfc = join(",", $nws);
            $qr = join(",", $qws);
            $total = join(",", $ttws);
            $catd = $dwt;
            $mydate = $cr_week;
        } elseif ($type == 'Monthly') {
            for ($m = $cdim-1; $m >= 0; $m--) {
                $monthtoprint[] = date('d', strtotime('-' . $m . 'day', strtotime($lm . '-' . $cdim)));
                $monthlys[] = date('Y-m-d', strtotime('-' . $m . 'day', strtotime($lm . '-' . $cdim)));
            }
            $dmt = join(",", $monthtoprint);
            /* Query */
            foreach ($monthlys as $monthly) {
                $fday = date('Y-m-d 00:00:00', strtotime(date("$monthly ")));
                $lday = date('Y-m-d 23:59:59', strtotime(date("$monthly")));
                $dat = $this->_getstatusData($fday,$lday);
                $nms[] = $dat['n'];
                $qms[] = $dat['q'];
                $ttms[] = $dat['t'];
            }
            /* End */

            /* Data response */
            $nfc = join(",", $nms);
            $qr = join(",", $qms);
            $total = join(",", $ttms);
            $catd = $dmt;
            $mydate = $cr_month;
        } else {
            // Daily
            /* Show 24 hours in day */
           $dts = array();
            for ($h = 0; $h < 24; $h++) {
                $tth[] = date('H:i', strtotime('+ ' . $h . ' hour', strtotime($current_date)));
                $hours[] = date('H:i:s', strtotime('+ ' . $h . ' hour', strtotime($current_date)));
            }
            $dts = implode(",", $tth);
            /* End */
            /* Query data */
            foreach ($hours as $hour) {
                $fhour = date('Y-m-d H:i:s', strtotime(date("$current_date $hour")));
                $schour = date('Y-m-d H:i:s', strtotime('+ 1 hour', strtotime($fhour)));
                $dat = $this->_getstatusData($fhour,$schour);
                $nds[] = $dat['n'];
                $qds[] = $dat['q'];
                $tds[] = $dat['t'];
            }
            /* End */

            /* Data response */
            $nfc = join(",", $nds);
            $qr = join(",", $qds);
            $total = join(",", $tds);
            $catd = $dts;
            $mydate = $current_date;
        }
        echo json_encode(array($nfc, $qr, $total, $catd, $mydate));
        exit;
    }

    function days_in_month($month, $year)
    {
        //return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
        
        return date('d', strtotime('last day of ' . $month));
    }

    /**
     * for system view
     */
    public
    function system_getalldata()
    {
        $query_date = date('Y-m-d');

        $erlday = date('Y-m-d', strtotime(date('Y-m-d 00:00:00')));
        $nighday = date('Y-m-d', strtotime(date('Y-m-d 24:00:00')));
        // First day of the month.
        $fday = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $lday = date('Y-m-t', strtotime($query_date));
        /* Total datas */
        $ttdatas = $this->AccessLog->find('count');

        /* Total access in day */
        $accdaily = $this->AccessLog->find('count', array(
            'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday))
        ));
        /* Total access in month */
        $accmonthly = $this->AccessLog->find('count', array(
            'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday))
        ));
        /* Accumulated by day, month */
        $accumulate = $accdaily + $accmonthly;
        /* Show OS data */
        $iphone = $this->AccessLog->find('count', array(
            'conditions' => array('AccessLog.ua LIKE' => '%iPhone%')
        ));
        $android = $this->AccessLog->find('count', array(
            'conditions' => array('AccessLog.ua LIKE' => '%Android%')
        ));
        // Other data
        $other = $this->AccessLog->find('count', array(
            'conditions' => array(
                'NOT' => array(
                    'AccessLog.ua' => '',
                    'AccessLog.ua LIKE' => '%Android%',
                    'AND' => array('AccessLog.ua LIKE' => '%iPhone%')
                ),
            )
        ));

        /* Show NFC and QR */
        $nfc = $this->AccessLog->find('count', array(
            'conditions' => array('AccessLog.p_type' => 'N')
        ));
        $qr = $this->AccessLog->find('count', array(
            'conditions' => array('AccessLog.p_type' => 'Q')
        ));
        if (isset($this->params['requested'])) {
            return array(
                'daily' => $accdaily,
                'monthly' => $accmonthly,
                'total' => $ttdatas,
                'iphone' => $iphone,
                'android' => $android,
                'other' => $other,
                'nfc' => $nfc,
                'qr' => $qr);
        }
    }

    /**
     * for system view
     */
    public
    function system_contentStatus()
    {
        $query_date = date('Y-m-d');
        $erlday = date('Y-m-d', strtotime(date('Y-m-d 00:00:00')));
        $nighday = date('Y-m-d', strtotime(date('Y-m-d 24:00:00')));
        // First day of the month.
        $fday = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $lday = date('Y-m-t', strtotime($query_date));
        /* Query content status in day */
        $accdaily = $this->AccessLog->find('count', array(
            'conditions' => array(
                'AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday),
                "AccessLog.contents != ''"),
            'fields' => array('AccessLog.contents')
        ));
        /* Query content status in month */
        $accmonthly = $this->AccessLog->find('count', array(
            'conditions' => array(
                'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday), "AccessLog.contents != ''"),
            'fields' => array('AccessLog.contents')
        ));
        if (isset($this->params['requested'])) {
            return $datas = (array(
                'daily' => $accdaily,
                'monthly' => $accmonthly
            ));
        }
    }

    /**
     * for system view
     */
    public
    function system_plateStatus()
    {
        $query_date = date('Y-m-d');
        $erlday = date('Y-m-d', strtotime(date('Y-m-d 00:00:00')));
        $nighday = date('Y-m-d', strtotime(date('Y-m-d 24:00:00')));
        // First day of the month.
        $fday = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $lday = date('Y-m-t', strtotime($query_date));
        /* Query plate status in day */
        $accdaily = $this->AccessLog->find('count', array(
            'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday), "AccessLog.path != '' "),
            'fields' => array('AccessLog.path')
        ));
        /* Query plate status in month */
        $accmonthly = $this->AccessLog->find('count', array(
            'conditions' => array('AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday), array("AccessLog.path != '' ")),
            'fields' => array('AccessLog.path')
        ));
        /* Query platinum plate */
        $platinumplate = $this->AccessLog->platinum_query();
        if (isset($this->params['requested'])) {
            return $datas = array(
                'daily' => $accdaily,
                'monthly' => $accmonthly,
                'platinum' => $platinumplate
            );
        }
    }

    public
    function system_getDataInDetail()
    {
        $data = $this->getDataInDetail();
        return $data;
    }

    public
    function getDataInDetail()
    {
        $query_date = date('Y-m-d');
        $erlday = $this->AccessLog->convertUTC('Asia/Tokyo',date('Y-m-d 00:00:00'));
        $nighday = $this->AccessLog->convertUTC('Asia/Tokyo',date('Y-m-d 24:00:00'));
        // First day of the month.
        $fday = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $lday = date('Y-m-t', strtotime($query_date));
        if ($this->Session->read('Access.screen') == 'bookmark') { // Get data for bookmark detail screen
            $bookmarkID = $this->Session->read('Access.id');
            /* Total datas */
            $ttdatas = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'bookmark_id' => $bookmarkID
                )
            ));

            /* Total access in day */
            $accdaily = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday),
                    'bookmark_id' => $bookmarkID
                )
            ));
            /* Total access in month */
            $accmonthly = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday),
                    'bookmark_id' => $bookmarkID
                )
            ));
            /* Accumulated by day, month */
            $accumulate = $accdaily + $accmonthly;
            /* Show OS data */
            $iphone = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.ua LIKE' => '%iPhone%',
                    'bookmark_id' => $bookmarkID
                )
            ));
            $android = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.ua LIKE' => '%Android%',
                    'bookmark_id' => $bookmarkID
                )
            ));
            // Other data
            $other = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'NOT' => array(
                        'AccessLog.ua' => '',
                        'AccessLog.ua LIKE' => '%Android%',
                        'AND' => array('AccessLog.ua LIKE' => '%iPhone%')
                    ),
                    'bookmark_id' => $bookmarkID
                )
            ));
            /* Show NFC and QR */
            $nfc = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.p_type' => 'N',
                    'bookmark_id' => $bookmarkID
                )
            ));
            $qr = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.p_type' => 'Q',
                    'bookmark_id' => $bookmarkID
                )
            ));
        } elseif ($this->Session->read('Access.screen') == 'plate') { // Get data for plate detail screen
            $path = $this->Session->read('Access.tag');
            $path = explode('.', $path);

            /* Total datas */
            $ttdatas = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'p_head' => $path[0],
                    'p_lot' => $path[1],
                    'p_num' => $path[2]
                )
            ));

            /* Total access in day */
            $accdaily = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.created_at BETWEEN ? AND ?' => array($erlday, $nighday),
                    'p_head' => $path[0],
                    'p_lot' => $path[1],
                    'p_num' => $path[2]
                )
            ));
            /* Total access in month */
            $accmonthly = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday),
                    'p_head' => $path[0],
                    'p_lot' => $path[1],
                    'p_num' => $path[2]
                )
            ));
            /* Accumulated by day, month */
            $accumulate = $accdaily + $accmonthly;
            /* Show OS data */
            $iphone = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.ua LIKE' => '%iPhone%',
                    'p_head' => $path[0],
                    'p_lot' => $path[1],
                    'p_num' => $path[2]
                )
            ));
            $android = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.ua LIKE' => '%Android%',
                    'p_head' => $path[0],
                    'p_lot' => $path[1],
                    'p_num' => $path[2]
                )
            ));
            // Other data
            $other = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'NOT' => array(
                        'AccessLog.ua' => '',
                        'AccessLog.ua LIKE' => '%Android%',
                        'AND' => array('AccessLog.ua LIKE' => '%iPhone%')
                    ),
                    'p_head' => $path[0],
                    'p_lot' => $path[1],
                    'p_num' => $path[2]
                )
            ));
            /* Show NFC and QR */
            $nfc = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.p_type' => 'N',
                    'p_head' => $path[0],
                    'p_lot' => $path[1],
                    'p_num' => $path[2]
                )
            ));
            $qr = $this->AccessLog->find('count', array(
                'conditions' => array(
                    'AccessLog.p_type' => 'Q',
                    'p_head' => $path[0],
                    'p_lot' => $path[1],
                    'p_num' => $path[2]
                )
            ));
        }
        return array(
            'daily' => $accdaily,
            'monthly' => $accmonthly,
            'total' => $ttdatas,
            'iphone' => $iphone,
            'android' => $android,
            'other' => $other,
            'nfc' => $nfc,
            'qr' => $qr);
    }


    public
    function ContentsRanking()
    {
       $team_id = $this->getTeamid($this->Auth->user('authority'));
       $arg = array('offset'=>0,'limit'=>0,'direction'=>'DESC');
       $date_list_tmp = $this->AccessLog->getTotalContentsByTeamAndType($team_id, '', $arg,null, null, null);
        $this->set(array(
            'titles' => array('name','url','access'),
            'data_index' => array('name','contents','total'),
            'data' => $date_list_tmp,
            'title' => 'Contents'
        ));
       $this->render('ranking');
    }
    
    public
    function PlateRanking()
    {
       $team_id = $this->getTeamid($this->Auth->user('authority'));
       $arg = array('offset'=>0,'limit'=>0,'direction'=>'DESC');
       $this->AccessLog->loadActivatePalteList($team_id);
       $date_list_tmp = $this->AccessLog->getTotalTapByTeamAndType($team_id, null, $arg,null, null, null);
        $this->set(array(
            'titles' => array('name','plate','access'),
            'data_index' => array('name','tag','total'),
            'data' => $date_list_tmp,
            'title' => 'Plates'
        ));
       $this->render('ranking');
    }


}

?>
