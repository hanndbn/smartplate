<?php
App::uses('APIController', 'Controller');

use UAParser\Parser;

class DeviceCountController extends APIController {

    public function process() {

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
            $app = $this->request->query('a');

            // $timezone
            if (!isset($time_zone) && empty($time_zone)) {
                $time_zone = 'UTC';
            }

            // convert start time and end time to time zone
            $start_timezone = null;
            $end_timezone = null;
            $this->AccessLog->target_time_zone = $time_zone;
            if (isset($start) && !empty($start)) {
                $start_timezone = date('Y-m-d H:i:s', strtotime($start));
            }
            if (isset($end) && !empty($end)) {
                $end_timezone = date('Y-m-d H:i:s', strtotime($end));
            }

            // $type
            $typeArray = array("0", "1");
            if (!isset($type) || $type == '' || !in_array($type, $typeArray)) {
                $type = '0';
            }

            // offset
            if (!isset($offset)) {
                $offset = 0;
            }

            $directionArray = array("asc", "desc");
            if (!isset($direction) || empty($direction) || !in_array(strtolower($direction), $directionArray)) {
                $direction = '';
            } else {
                strtolower($direction);
            }
            $team_ids = $this->session['team_id'];
            $this->AccessLog->loadActivatePalteList($team_ids);

            // get obj with id
            //custom variable
            $condition = $this->AccessLog->getConditionWithType($team_ids, $type, $_cid, $_tag, $start_timezone, $end_timezone, $app);
            $total_device = $this->AccessLog->find('all', array('conditions' => $condition, 'fields' => array('ua')));
            $devices = $this->AccessLog->find('all', array('conditions' => $condition, 'fields' => array('ua', 'count(*) as total'), 'group' => array('ua'), ));
            $data = array();
            $total_count = 0;
            $parser = Parser::create();
            $arrayDevice = array();
            foreach ($devices as $key => $value) {
                $result = $parser->parse($value['AccessLog']['ua']);
                $device_name = $result->device->family;
                if (isset($arrayDevice[$device_name]) && $arrayDevice[$device_name] > 0) {
                    $arrayDevice[$device_name] += intval($value['0']['total']);
                } else {
                    $arrayDevice[$device_name] = intval($value['0']['total']);
                }
            }
            if ($direction == 'asc') {
                asort($arrayDevice);
            } else if ($direction == 'desc') {
                arsort($arrayDevice);
            }
            if (isset($limit)) {
                if ($limit > count($arrayDevice)) {
                    $limit = count($arrayDevice);
                }
                $arrayDeviceSlice = array_slice($arrayDevice, $offset, $limit);
            } else {
                $arrayDeviceSlice = array_slice($arrayDevice, $offset);
            }
            foreach ($arrayDeviceSlice as $key => $value) {
                array_push($data, array('count' => $value, 'name' => $key));
                $total_count += $value;
            }
            $analytics = array('data' => $data, 'total_count' => count($total_device), 'total_number' => count($arrayDevice));
            $this->result['analytics'] = $analytics;
        }
    }
}
