<?php
App::uses('AppModel', 'Model');

/**
 * Model for user table
 *  
 * @package       app.Model
 * 
 */
class User extends AppModel 
{
  const password_salt = 'Paris-Roubaix';
  
    public $useTable = 'user';
    public $hasMany  = array(
        'Device' => array(  'className' => 'Device'),
        'Session' => array( 'className' => 'Session')
    );

    public $validate = array(
        'login_name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Login Id is required',
                'allowEmpty' => false
            )
        ),
        'name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Name is required',
                'allowEmpty' => false
            )
        ),
        'password' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Password is required',
                'allowEmpty' => false,
                'on' => 'create',
            )
        ),
        'mail' => array(
            'rule' => self::REGULAR_EXPRESSION_MAIL_ADDRESS,
            'message' => 'Mail is required',
            'allowEmpty' => true ),
            'comment' => array( 'nonEmpty' => array(    'rule' => array('notEmpty'),
                                                        'message' => 'Comment is required',
                                                        'allowEmpty'  => true )
                            ),
                    );

    static function CryptPassword($clear_password) {
        return sha1(self::password_salt . $clear_password);
    }

    static function isConvertedUser($user_id) {
        $data_file = TMP . 'data/smart_plate/save_datas/'.$user_id.'.ex';
        return file_exists($data_file) ? 1 : 0;
    }
    static function converted($user_id) {
        $data_file = TMP . 'data/smart_plate/save_datas/'.$user_id.'.ex';
        touch($data_file);
    }
   /**
   * ランダム文字列の取得
   * @param int $nLengthRequired: 文字数
   * @param bool $hasChar: 半角英字を含むか　false:数字のみ
   * @return String: ランダム文字列
   */
    static function getRandomString($nLengthRequired = 6,$hasChar=true){
        $sCharList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        if( ! $hasChar ){
          $sCharList = "0123456789";
        }
        mt_srand();
        $sRes = "";
        for($i = 0; $i < $nLengthRequired; $i++) {
            $sRes .= $sCharList{mt_rand(0, strlen($sCharList) - 1)};
        }
        return $sRes;
    }
    
    public function Login($login_name, $password) {
      
      //$this->loadModel('Session');
      
      $pass_data = self::CryptPassword($password);
      
      $this->unbindModel(
        array('hasMany' => array('Session'))
      );
      
      $account = $this->find( 'all', 
                              array(  'conditions' => array('login_name' => $login_name, 'password' => $pass_data),
                                      'fields' => array('password','status','power','application')
                              ));
      if (count($account))
      {
      }
      return $account;
    }
    
    public function UserByTeam($team_id) {
      
      $this->unbindModel(
        array('hasMany' => array('Session'))
      );      $this->unbindModel(
        array('hasMany' => array('Device'))
      );
      $data = $this->find( 'all', 
                              array(  'conditions' => array('team_id' => $team_id),
                                      'fields' => array('login_name','name','status','power')
                              ));
      $data = $this->removeArrayWrapper('User', $data);
      
      return $data;
    }
}

