<?php
App::uses('APIController', 'Controller');
use UAParser\Parser;

class DeviceCountController extends APIController
{
    public function process()
    {
        if ($this->request->is('get')) {
            $this->loadModel('AccessLog');
            date_default_timezone_set('UTC');

            $type = $this->request->query('type');
            $_tag = $this->request->query('tag');
            $_cid = $this->request->query('content_id');
            $start = $this->request->query('start');
            $end = $this->request->query('end');
            $time_zone = $this->request->query('tz');
            $offset = $this->request->query('offset');
            $limit = $this->request->query('limit');
            $direction = $this->request->query('direction');

            // $timezone
            if (!isset($time_zone) && empty($time_zone)) {
                $time_zone = 'UTC';
            }

            // convert start time and end time to time zone
            $start_timezone = null;
            $end_timezone = null;
            $this->AccessLog->target_time_zone = $time_zone;
            if (isset($start) && !empty($start)) {
                $start = date('Y-m-d H:i:s', strtotime($start));
                $start_timezone = $this->AccessLog->convertUTC2TimeZone($time_zone, $start);
            }
            if (isset($end) && !empty($end)) {
                $end = date('Y-m-d H:i:s', strtotime($end));
                $end_timezone = $this->AccessLog->convertUTC2TimeZone($time_zone, $end);
            }

            // $type
            $typeArray = array("0", "1");
            if (!isset($type) || $type == '' || !in_array($type, $typeArray)) {
                $type = '0';
            }

            // get obj with id
            //custom variable
            $tag_obj = null;
            $bookmark_obj = null;
            $options = array();
            $bookmark_id = null;
            if ($type == '0' && !empty($_tag)) {
                $this->loadModel('Tag');
                $options['conditions'] = array('Tag.tag' => $_tag);
                $tag_obj = $this->Tag->find('first', $options);
                if(isset($tag_obj['Tag']['bookmark_id'])){
                    $bookmark_id = $tag_obj['Tag']['bookmark_id'];
                }
            } else if ($type == '1' && !empty($_cid)) {
                $this->loadModel('Bookmark');
                $options['conditions'] = array('Bookmark.id' => $_cid);
                $bookmark_obj = $this->Bookmark->find('first', $options);
                if(isset($bookmark_obj['Bookmark']['id'])){
                    $bookmark_id = $bookmark_obj['Bookmark']['id'];
                }
            }


            $directionArray = array("asc", "desc");
            if (!isset($direction) || empty($direction) || !in_array(strtolower($direction), $directionArray)) {
                $direction = 'ASC';
            }

            $sql = "SELECT ua, count(*) AS total FROM access_log WHERE 1 ";


            if(isset($bookmark_id)){
                $sql = $sql . " AND bookmark_id = '$bookmark_id'";
            }

            if (isset($start) && !empty($start)) {
                $sql = $sql . " AND created_at >= '$start_timezone'";
            }
            if (isset($end) && !empty($end)) {
                $sql = $sql . " AND created_at <= '$end_timezone'";
            }

            $sql= $sql." GROUP BY ua ";
            $sql= $sql." ORDER BY ua $direction";

            if (isset($limit) && !empty($limit)) {
                $sql = $sql . " LIMIT $limit";
                if (isset($offset) && !empty($offset)) {
                    $sql = $sql . " OFFSET $offset";
                }
            }

            $total_device = $this->AccessLog->query($sql);
            $data = array();
            $total_count = 0;
            $parser = Parser::create();
            $arrayDevice = array();
            foreach($total_device as $key => $value){
                $result = $parser->parse($value['access_log']['ua']);
                $device_name = $result->device->family;
                if(isset($arrayDevice[$device_name]) && $arrayDevice[$device_name] > 0 ){
                    $arrayDevice[$device_name] += $value['0']['total'];
                }else{
                    $arrayDevice[$device_name] = $value['0']['total'];
                }
            }

            foreach($arrayDevice as $key => $value){
                array_push($data, array('count' => $value, 'name' => $key));
                $total_count += $value;
            }

            $analytics = array(
                'data' => $data,
                'total_count' => $total_count,
                'total_number' => count($total_device)
            );
            $this->result['analytics'] = $analytics;
        }
    }
}