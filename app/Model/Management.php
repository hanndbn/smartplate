<?php

App::uses('AppModel', 'Model');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');

/**
 * Model for management table
 *  
 * @package       app.Model
 * 
 */
class Management extends AppModel {

    public $useTable = 'management';
    public $hasMany = 'Team';
    public $recursive = -1;
    public $validate = array(
        'login_name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'A username is required',
                'allowEmpty' => false,
            ),
        ),
        'newlogin_name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'A username is required',
                'allowEmpty' => false,
            ),
        ),
        'password' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'A password is required',
                'allowEmpty' => false
            )
        ),
        'password_update' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'A password is required',
                'allowEmpty' => false
            )
        ),
        'cf_newpassword' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'A password is required',
                'allowEmpty' => false
            )
        )
    );

    /**
     * Action will be called before save record to database.
     *
     * @param array $options The record prepare to store.
     * @return bool Fallback to our parent true.
     */
    public function beforeSave($options = array()) {
        // hash our password
        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = Security::hash(
                            $this->data[$this->alias]['password'], 'sha1', true
            );
        }

        // if we get a new password, hash it
        if (isset($this->data[$this->alias]['password_update'])) {
            $this->data[$this->alias]['password'] = Security::hash(
                            $this->data[$this->alias]['password'], 'sha1', true
            );
        }

        // fallback to our parent
        return parent::beforeSave($options);
    }

    /**
     * Get list manager's projects undertaken
     * 
     * @param int $id manager id
     */
    public function getProjects($id, $autho = null) {
        $projects = array();
        $manager_ids = $this->find('all', array(
            'conditions' => array('parent_id' => $id),
            'fields' => array('id')
        ));
        if ($manager_ids) {
            $manager_ids = $this->removeArrayWrapper('Management', $manager_ids, 'id');
            $m_ids = array();
            foreach ($manager_ids as $manager_id) {
                $m_ids[] = $manager_id['id'];
            }
            array_push($m_ids, $id);
            if ($autho) {
                $sub_childrens = $this->find('all', array(
                    'conditions' => array('parent_id' => $m_ids),
                    'fields' => array('id')
                ));
                if ($sub_childrens) {
                    $sub_childrens = $this->removeArrayWrapper('Management', $sub_childrens, 'id');
                    $sc_ids = array();
                    foreach ($sub_childrens as $sub_children) {
                        $sc_ids[] = $sub_children['id'];
                    }
                    foreach ($m_ids as $m_id) {
                        array_push($sc_ids, $m_id);
                    }
                    $projects = $this->Team->find('list', array(
                        'conditions' => array('management_id' => $sc_ids),
                        'group' => 'name'
                    ));
                }
            } else {
                $projects = $this->Team->find('list', array(
                    'conditions' => array('management_id' => $m_ids),
                    'group' => 'name'
                ));
            }
        }

        return $projects;
    }

    /**
     * Get list admin's projects undertaken
     * 
     * @param int $id admin id
     */
    public function getAdminProjects($id) {
        $result = array();
        $childrens = $this->find('all', array(
            'conditions' => array('parent_id' => $id),
            'fields' => array('id')
        ));
        if ($childrens) {
            $childrens = $this->removeArrayWrapper('Management', $childrens, 'id');
            $m_ids = array();
            foreach ($childrens as $children) {
                $m_ids[] = $children['id'];
            }
            array_push($m_ids, $id);
            $sub_childrens = $this->find('all', array(
                'conditions' => array('parent_id' => $m_ids),
                'fields' => array('id')
            ));
            if ($sub_childrens) {
                $sub_childrens = $this->removeArrayWrapper('Management', $sub_childrens, 'id');
                $sc_ids = array();
                foreach ($sub_childrens as $sub_children) {
                    $sc_ids[] = $sub_children['id'];
                }
                foreach ($m_ids as $m_id) {
                    array_push($sc_ids, $m_id);
                }
                $projects = $this->Team->find('all', array(
                    'conditions' => array('management_id' => $sc_ids),
                    'fields' => array('id')
                ));
                if ($projects) {
                    $projects = $this->Team->removeArrayWrapper('Team', $projects, 'id');
                    $p_ids = array();
                    foreach ($projects as $project) {
                        $p_ids[] = $project['id'];
                    }
                    $result += $p_ids;
                }
            }
        }
        return $result;
    }

    /**
     * Return list children user id
     * @param int $userID parent user id
     * @return array list child user id
     */
    public function getChildID($userID) {
        $result = array();
        $userData = $this->findById($userID);
        if ($userData) {
            $childID = $this->find('all', array(
                'conditions' => array(
                    'parent_id' => $userData['Management']['id']
                ),
                'fields' => array('id', 'authority')
            ));
            if ($childID) {
                $childID = $this->removeArrayWrapper('Management', $childID);
                foreach ($childID as $val) {
                    if ($val['authority'] == 2) {
                        $editorIds = $this->getChildID($val['id']);
                        foreach ($editorIds as $editorId) {
                            $result[] = $editorId;
                        }
                    }
                    $result[] = $val['id'];
                }
            }
        }
        return $result;
    }

    /**
     * Get list management:team_id by management:id
     * @param array $arrayUserID management:id
     * @return array return team_id
     */
    public function getUserTeamID($arrayUserID){
        $teamID = array();
        foreach($arrayUserID as $userID){
            $teamid = $this->find('first', array(
                'conditions' => array('id' => $userID),
                'fields' => array('authority', 'team_id')
            ));
            if($teamid['Management']['authority'] == 3){
                $teamID[] = $teamid['Management']['team_id'];
            }
        }
        return $teamID;
    }

}

?>
