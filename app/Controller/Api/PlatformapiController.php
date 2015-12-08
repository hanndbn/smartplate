<?php
App::uses ( 'APIController', 'Controller' );
class PlatformAPIController extends APIController {
	// ...
	public function process() {
		if ($this->request->is ( 'get' )) {
			date_default_timezone_set ( 'UTC' );

			$this->loadModel ( 'AccessLog' );

			$app = $this->request->query ( 'a' );
			$type = $this->request->query ( 'type' );
			$_tag = $this->request->query ( 'tag' );
			$_cid = $this->request->query ( 'content_id' );
			$start = $this->request->query ( 'start' );
			$end = $this->request->query ( 'end' );
			$time_zone = $this->request->query ( 'tz' );


            if (is_null ( $type )) {
                throw new Exception ( "not fount type", 1 );
            }

            if ($start) {
                $start = date ( 'Y-m-d H:i:s', strtotime ( $start ) );
            }
            if ($end) {
                $end = date ( 'Y-m-d H:i:s', strtotime ( $end ) );
            }

            $team_ids = $this->session ['team_id'];


            $this->AccessLog->loadActivatePalteList ( $this->session ['team_id'] );
            $this->AccessLog->filterAppUserPalteList($this->session['user_id']);
            
            $nums = $this->AccessLog->getUACount ( $team_ids, $type, $_cid, $_tag, $time_zone, $start, $end, $app );
			$this->result = $nums;
		}
	}
}

?>

