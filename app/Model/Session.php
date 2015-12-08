<?php

App::uses('AppModel', 'Model');

/**
 * Model for Session table
 *  
 * @package       app.Model
 * 
 */
class Session extends AppModel 
{
  const salt = 'DuraAce';
  private static $token_counter = 0;
  
    public $useTable = 'session';
    public $belongsTo = array(
        'User' => array('className' => 'User',
            'foreignKey' => 'user_id')
    );
    public $recursive = -1;
    public $validate = array(
        'token' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Token is required',
                'allowEmpty' => false
            )
        ),  
        'team_id' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Project is required',
                'allowEmpty' => false
            )
        ),    
        'user_id' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'User is required',
                'allowEmpty' => false
            )
        ),    
        'uuid' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Unique id is required',
                'allowEmpty' => false
            )
        ),       
    );
	
  /** Tokenの生成
   * 
   */
	public function GenerateToken($salt = '') {
    if (!$salt) {
      $salt = 'SuperRecord';
    }
    $tc = self::$token_counter++;
    return sha1(sprintf('%s-%d-%d-%s', self::salt, mt_rand(), $tc, $salt));
  }
}
?>
