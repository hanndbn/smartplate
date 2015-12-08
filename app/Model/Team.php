<?php

App::uses('AppModel', 'Model');

/**
 * Model for team table
 *  
 * @package       app.Model
 * 
 */
class Team extends AppModel 
{
    public $useTable = 'team';
    public $belongsTo = array(
        'Management' => array('className' => 'Management',
            'foreignKey' => 'management_id')
    );
    public $hasMany  = array(
        'Order' => array(
            'className' => 'Order',
            'conditions' => array('Order.team_id = Team.id'),
        )
    );
    public $recursive = -1;
    public $validate = array(
        'name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Name is required',
                'allowEmpty' => false
            )
        ),
        'splash' => array(
            'nonEmpty' => array(
                //'rule' => array('notEmpty'),
                'message' => 'Images is required',
                'allowEmpty' => true,
                'on' => 'create',
            ),
           
        ),
    );
    
    public function splashImage($team_id)
    {
        $options['conditions'] = array( 'id' => $team_id);
        $options['fields'] = array('splash');
        
        $res = $this->find('first', $options);
        
        if( empty($res['Team']["splash"]))
          return NULL;
        else
          return Router::fullbaseUrl() . DS . 'upload' . DS . 'splash' . DS .$res['Team']["splash"];
    }

    public function ProjectListByManagementID($management_id)
    {
        $options['conditions'] = array( 'management_id' => $management_id);
        
        $res = $this->find('list', $options);
        
        return $res;
    }

    /**
     * Get list management:team_id by management:id
     * by george
     * @param array $arrayUserID management:id
     * @return array return team_id
     */
    public function getUserTeamID($arrayUserID){
        $teamID = array();
        foreach($arrayUserID as $userID){
            $teamids = $this->find('all', array(
                'conditions' => array('management_id' => $userID),
                'fields' => array('id')
            ));
            
            foreach ($teamids as $value) {
              $teamID[] = $value['Team']['id']; 
            }
        }
        return $teamID;
    }

}
?>
