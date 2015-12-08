<?php

App::uses('AppController', 'Controller');


function encode_callback($matches) {
  return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UTF-16");
}

/**
 * PHP5.4からでないと対応していないUnicodeアンエスケープをPHP5.3でもできるようにしたラッパー関数
 * @param mixed   $value
 * @param int     $options
 * @param boolean $unescapee_unicode
 */
function json_xencode($value, $options = 0, $unescapee_unicode = true)
{
  $v = json_encode($value, $options);
  if ($unescapee_unicode) {
    $v = unicode_encode($v);
    // スラッシュのエスケープをアンエスケープする
    $v = preg_replace('/\\\\\//', '/', $v);
  }
  return $v;
}

/**
 * Unicodeエスケープされた文字列をUTF-8文字列に戻す。
 * 参考:http://d.hatena.ne.jp/iizukaw/20090422
 * @param unknown_type $str
 */
function unicode_encode($str)
{
  return preg_replace_callback("/\\\\u([0-9a-zA-Z]{4})/", "encode_callback", $str);
}


class APIController extends AppController {
    const SUCCESS = 0;
    //CakeのJsonViewとXmlViewを使用するので、RequestHandler必須。
    public $components = array('Session', 'RequestHandler');

    // JSONやXMLにして返す値を格納するための配列です。
    protected $result = array();

    protected $session;

    public static function who() {
        return __CLASS__;
    }

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('controller' => 'test', 'action' => 'test'));
        $this->Auth->allow(array('controller' => 'login', 'action' => 'index'));
        $this->Auth->allow(array('controller' => 'exclusiveloginapi', 'action' => 'index'));
        $this->Auth->allow(array('controller' => 'logout', 'action' => 'index'));
        $this->Auth->allow(array('controller' => 'registration', 'action' => 'index'));


        // Ajaxでないアクセスは禁止。直アクセスを許すとXSSとか起きかねないので。
       // if (!$this->request->is('ajax')) throw new BadRequestException('Ajax以外でのアクセスは許可していません。');

        // nosniffつけるべし。じゃないとIEでXSS起きる可能性あり。
        $this->response->header('X-Content-Type-Options', 'nosniff');
    }



    /**
     * checkBaseRequest
     * API共通必須項目チェック
     */
    protected function checkBaseRequest(){
        if( empty($this->request->query['i'] ) ){ throw new RuntimeException('not found device id', 500); }
        if( empty($this->request->query['l'] ) ){ throw new RuntimeException('not found locale', 500); }
        //if( empty($this->request->query['a'] ) ){ throw new RuntimeException('not found application type', 500); }
        if( empty($this->request->query['a'] ) ){ $this->request->query['a'] = ''; }

        if($this->request->query('direction') == 'ascend'){ $this->request->query['direction'] = 'asc';}
        if($this->request->query('direction') == 'descend'){ $this->request->query['direction'] = 'desc';}

        $class_name = static::who();
        if( $class_name !== 'LoginapiController'
              && $class_name !== 'RegistrationAPIController'
              && $class_name !== 'ResetPasswordAPIController') {
          if( empty($this->request->query['t'] ) ){ throw new RuntimeException('not found token', 500); }
        }
    }

    /**
     * index
     * AP main process
     */
    public function index() {
      $this->autoRender = false;

      try {

        $this->loadModel('Session');
        $this->checkBaseRequest();

        // check session data
        if( !empty( $this->request->query['t'] ) ){
          $tmp_session  = $this->Session->find( 'first', array(  'conditions' => array('token' => $this->request->query['t']) ) );
          if( empty($tmp_session['Session']) ){
            throw new RuntimeException('Login Required', 307);
          }
          $this->session = $tmp_session['Session'];
        }

        // do process
        if( method_exists($this, 'process') ){
          $this->process();
          $this->success();
        }
      }catch( exception $e ){
          $this->error( $e->getMessage(), $e->getCode() );
      }

      if( isset($this->result['no_encode']) ){
        $data = $this->result['no_encode'];
        echo '{"status":{"code":"0","message":""},"datas":'.$data.'}';
      }else{
        $json_str =  json_encode( $this->result);
        echo $json_str;
      }
    }

    // 成功系処理。$this->resultに値いれる
    protected function success() {
        $this->result['status']['code'] = self::SUCCESS;
        $this->result['status']['message'] = '';

        $this->response->statusCode(200);
    }

    // エラー系処理。$this->resultに値いれる
    protected function error($message = '', $code) {
        $this->result['status']['code'] = $code;
        $this->result['status']['message'] = $message;

        $this->response->statusCode(200);
    }

    // バリデーションエラー系処理。$this->resultに値いれる
    protected function validationError($modelName, $validationError = array()) {
        $this->result['status']['message'] = 'Validation Error';
        $this->result['status']['code'] = '422'; //エラーコードはプロジェクトごとに定義すべし
        $this->result['status']['error']['validation'][$modelName] = array();
        foreach ($validationError as $key => $value) {
            $this->result['status']['error']['validation'][$modelName][$key] = $value[0];
        }

        $this->response->statusCode(200);
    }

}
