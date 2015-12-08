<?php

/**
 * Model for label table
 *
 * @package       app.Model
 */
App::uses('AppModel', 'Model');

class Label extends AppModel {
  
    const MODEL_BOOKMARK  = 'BookmarkModel';
    const MODEL_TAG       = 'TagModel';
    const MODEL_USER      = 'UserModel';

    public $useTable = 'label';
    public $hasMany = array(
        'LabelData' => array(
            'className' => 'LabelData',
            'conditions' => array('LabelData.label_id = Label.id'),
        )
    );
    public $recursive = -1;
    public $validate = array(
        'label' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Label name is required',
                'allowEmpty' => false
            )
        ),
    );

    /**
     * Checks that the provided array is a label
     * by checking that it contains id and parent_id keys
     *
     * @param array $label
     *
     * @return boolean
     */
    protected static function _isLabel($label) {
        return (
                !empty($label) &&
                is_array($label) &&
                array_key_exists('id', $label) &&
                array_key_exists('parent_id', $label)
                );
    }

    /**
     * Checks that the provided array is a label array
     * by checking that the first element is a label
     *
     * @param mixed $labels
     *
     * @return boolean
     */
    protected static function _isLabelsArray($labels) {
        if (is_array($labels)) {
            if (count($labels) == 0) {
                return true;
            } else {
                return (self::_isLabel(reset($labels)));
            }
        } else {
            return false;
        }
    }

    /**
     * Checks that the provided array is a label hierarchy
     * by checking that the first child of the first element has a id key
     *
     * @param mixed $labelHierarchy
     *
     * @return boolean
     */
    protected static function _isLabelHierarchy($labelHierarchy) {
        if (is_array($labelHierarchy)) {
            if (count($labelHierarchy) == 0) {
                return true;
            }

            $firstChild = reset($labelHierarchy);
            if (is_array($firstChild)) {
                return (self::_isLabelsArray(reset($firstChild)));
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get All labels from db and remove Label wrapper
     * 
     * @param string $type Label type
     * @param int $team_id Targetr team id
     * @param int $parent_id target parent id
     * 
     * @return array
     */
    public function getLabelsArrayByTeam($team_id,$parent_id,$type = null) {
        $options = array(
            'order' => array('display_order' => 'asc'),
        );
        if ($type) {
            $options['conditions'] = array(
                'type' => $type,
                'team_id' => $team_id,
                'parent_id' => $parent_id,
            );
        }
        $options['fields'] = array('id','label');
        
        $labels = array();
        $res = $this->find('all', $options);
       
        return $this->removeArrayWrapper('Label', $res);
    }
    
    /**
     * Get All labels from db and remove Label wrapper
     * 
     * @param string $type Label type
     * 
     * @return array
     */
    public function getLabelsArray($type = null) {
        $options = array(
            'order' => array('display_order' => 'asc'),
        );
        if ($type) {
            $options['conditions'] = array(
                'type' => $type,
            );
			$backEnd = Configure::read('backEnd');
        	if (empty($backEnd)) {
                $user = $this->getCurrentUser();
                if($user['authority'] != 3){
                    $managementModel = ClassRegistry::init('Management');
                    $listUsers = $managementModel->getChildID($user['id']);
                    $teamID = $managementModel->getUserTeamID($listUsers);
                }else{
                    $teamID = $user['team_id'];
                }
                $options['conditions'] += array(
                    'Label.team_id' => $teamID,
                );
            }
        }
        // ? $options['group'] = array('id');
        $labels = $this->find('all', $options);

        return $this->removeArrayWrapper('Label', $labels, 'id');
    }

    /**
     * Gets an array representing the node hierarchy that can be traversed recursively
     * Format: item[parent_id][id] = label
     *
     * @param array|null Label list from getLabelsArray()
     *
     * @return array Label hierarchy
     */
    public function getLabelHierarchy($labels = null) {
        if (!$this->_isLabelsArray($labels)) {
            $labels = $this->getLabelsArray();
        }

        $labelHierarchy = array();

        foreach ($labels AS $label) {
            if (!$label['parent_id']) {
                $label['parent_id'] = 0;
            }

            $labelHierarchy[$label['parent_id']][$label['id']] = $label;
        }

        return $labelHierarchy;
    }

    /**
     * Builds nested labels, based on the parent_id and level information in the database. 
     *   
     * @param array|null $labelHierarchy - will be fetched automatically when NULL is provided
     * @param integer $parentNodeId
     * @param integer $level
     * 
     * @return array
     */
    public function makeNestedLabels($labelHierarchy, $parentNodeId = 0, $level = 0) {
        $labels = array();

        /* if ($level == 0 && !$this->_isLabelHierarchy($labelHierarchy))
          {
          $labelHierarchy = $this->getLabelHierarchy();
          } */

        if (empty($labelHierarchy[$parentNodeId])) {
            return array();
        }

        foreach ($labelHierarchy[$parentNodeId] AS $i => $label) {
            $labels[$label['id']] = $label;
            $labels[$label['id']]['level'] = $level;

            $childLabels = $this->makeNestedLabels($labelHierarchy, $label['id'], $level + 1);

            $labels[$label['id']]['children'] = $childLabels;
        }

        return $labels;
    }

    /**
     * Find all parent by list labels
     * 
     * @param array $labels List labels
     * 
     * @return arrays
     */
    public function findAllParents(array $labels) {

        $output = array();

        if ($labels) {
            $parentIds = array();

            foreach ($labels as $label) {
                $parentIds[] = $label['parent_id'];
            }

            $parents = $this->find('all', array(
                'order' => array('display_order' => 'asc'),
                'conditions' => array('id' => $parentIds, 'team_id' => Configure::read('teamId'))
            ));

            if ($parents) {
                $parents = $this->removeArrayWrapper('Label', $parents, 'id');
                $output += $parents;
                $output += $this->findAllParents($parents);
            }
        }

        return $output;
    }

    /**
     * Find all children by list labels
     * 
     * @param array $labels List labels
     * 
     * @return arrays
     */
    public function findAllChildren(array $labels) {

        $output = array();

        if ($labels) {
            $ids = array();

            foreach ($labels as $label) {
                $ids[] = $label['id'];
            }

            $children = $this->find('all', array(
                'order' => array('display_order' => 'asc'),
                'conditions' => array('parent_id' => $ids)
            ));

            if ($children) {
                $children = $this->removeArrayWrapper('Label', $children, 'id');
                $output += $children;
                $output += $this->findAllChildren($children);
            }
        }
//print_r($output);
        return $output;
    }

    public function parseNestedJson($jsonArray, $parentId = 0, $level = 0) {
        $return = array();

        foreach ($jsonArray as $subArray) {
            $returnSubSubArray = array();

            if (isset($subArray['children'])) {
                $returnSubSubArray = $this->parseNestedJson($subArray['children'], $subArray['id'], $level + 1);
            }

            $return[] = array(
                'id' => $subArray['id'],
                'parent_id' => $parentId,
                'level' => $level
            );

            $return = array_merge($return, $returnSubSubArray);
        }

        return $return;
    }

    /**
     * Call logic before perform action delete 
     */
    public function beforeDelete($cascade = true) {
        /* $children = $this->find('all', array(
          'conditions' => array('parent_id' => $this->id, 'team_id' => Configure::read('teamId'))
          )); */

        $label = array(
            $this->id => array(
                'id' => $this->id
            )
        );

        $children = $this->findAllChildren($label);

        $ids = array_keys($children);
        $ids[] = $this->id;

        $this->LabelData->deleteAll(array('LabelData.label_id' => $ids), false);

        $this->deleteAll(array('parent_id' => $ids), false);

        return true;
    }

    /**
     * Call logic before perform action Save 
     */
    public function beforeSave($options = array()) {
        $data = $this->data['Label'];

        if (!empty($data) && isset($data['label'])) {
            if (!$this->id && !isset($this->data[$this->alias][$this->primaryKey])) {
                $labels = $this->find('all', array(
                    'conditions' => array(
                        'label' => $data['label'],
                        'type' => $data['type'],
                        'team_id' => $data['team_id'],
                        'parent_id' => $data['parent_id']
                    )
                ));
            } else {
                $label = $this->findById($this->id);

                $labels = $this->find('all', array(
                    'conditions' => array(
                        'label' => $data['label'],
                        'type' => $label['Label']['type'],
                        'team_id' => $label['Label']['team_id'],
                        'parent_id' => $data['parent_id'],
                        'NOT' => array('id' => $this->id)
                    )
                ));
            }

            if (!empty($labels)) {
                return false;
            }
        }

        return true;
    }

    /**
     * return list label name of bookmark
     * 
     * @param int $id  bookmark id
     * @return array 
     */
    public function label_Query($id, $type) {
        $options = array(
            'conditions' => array(
                'Label.type' => $type,
                'LabelData.target_id' => $id
            ),
            'order' => array('LabelData.cdate' => 'desc'),
            'fields' => 'Label.label',
            'group' => 'Label.label'
        );
		$backEnd = Configure::read('backEnd');
        if (empty($backEnd)) {
            $user = $this->getCurrentUser();
            if($user['authority'] != 3){
                $managementModel = ClassRegistry::init('Management');
                $listUsers = $managementModel->getChildID($user['id']);
                $teamID = $managementModel->getUserTeamID($listUsers);
                $options['conditions'] += array(
                    'Label.team_id' => $teamID,
                );
            }
        }
        $bm_label = $this->LabelData->find('all', $options);

        $labels = array();
        foreach ($bm_label as $label) {
            $labels[] = $label['Label']['label'];
        }

        return $labels;
    }

    /**
     * return list label id of bookmark
     * 
     * @param int $id  bookmark id
     * @return array 
     */
    public function label_id_Query($id, $type) {
        $options = array(
            'conditions' => array(
                'Label.type' => $type,
                'LabelData.target_id' => $id
            ),
            'order' => array('LabelData.cdate' => 'desc'),
            'fields' => 'Label.id',
            'group' => 'Label.label'
        );
		$backEnd = Configure::read('backEnd');
        if (empty($backEnd)) {
            $user = $this->getCurrentUser();
            if($user['authority'] != 3){
                $managementModel = ClassRegistry::init('Management');
                $listUsers = $managementModel->getChildID($user['id']);
                $teamID = $managementModel->getUserTeamID($listUsers);
                $options['conditions'] += array(
                    'Label.team_id' => $teamID,
                );
            }
        }
        $bm_label = $this->LabelData->find('all', $options);

        $labels = array();
        foreach ($bm_label as $label) {
            $labels[] = $label['Label']['id'];
        }
        return $labels;
    }
    
    public function InsertLabel( $team_id, $label, $type, $target_id, $parent_id = 0 ) {
      $data = array();
      $this->create();
      $label_id = 0;
      if ($this->save( array('team_id' => $team_id, 'type' => $type, 'label'=>$label, 'parent_id' => $parent_id, 'status' => 1, 'display_order' => 0 ) )) {
          $label_id = $this->getLastInsertId();
          if( !empty($target_id) ) {
            $data['LabelData']['LabelData']['target_id'] = $target_id;
            $data['LabelData']['LabelData']['label_id'] = $label_id;
            $label_data = new LabelData();
            $label_data->create();
            $label_data->save($data['LabelData']);
          }
      }

      return $label_id;
    }
    
    /**
     * check label exist
     * 
     * @param string $type Label type
     * @param int $team_id Targetr team id
     * @param int $parent_id target parent id
     * @param string $label_text
     * 
     * @return int
     */
    public function hasLabel($team_id,$parent_id,$type,$label_text) {
        $options['conditions'] = array(
            'type' => $type,
            'team_id' => $team_id,
            'parent_id' => $parent_id,
            'label' => $label_text
        );
        $options['fields'] = array('id');
        
        $labels = array();
        $res = $this->find('count', $options);
       
        return $res;
    }
    
    /**
     * check label exist by ID
     * 
     * @param string $type Label type
     * @param int $team_id Targetr team id
     * @param int $label_id
     * 
     * @return int
     */
    public function hasLabelByID($team_id,$type,$label_id) {
        $options['conditions'] = array(
            'type' => $type,
            'team_id' => $team_id,
            'id' => $label_id
        );
        $options['fields'] = array('id');
        
        $labels = array();
        $res = $this->find('count', $options);
       
        return $res;
    }
     
     /**
     * delete label data
     * 
     * @param string $type Label type
     * @param int $team_id Targetr team id
     * @param int $target_id 
     * 
     * @return int
     */
    public function deleteLabelDataByTargetID($team_id,$type,$target_id) {
        
       $res = $this->query(
            "DELETE FROM label_datas 
                WHERE label_id in ( SELECT id FROM label WHERE type = '$type' AND team_id = $team_id )
                AND target_id = $target_id"
        );
       
        return $res;
    }
    
     /**
     * get label by target id
     * 
     * @param string $type Label type
     * @param int $team_id Targetr team id
     * @param int $target_id 
     * 
     * @return array
     */
    public function getLabelByTargetID($team_id,$type,$target_id) {
        
       $all_labels = $this->query(
            "SELECT label.id,label.label,label.parent_id FROM label 
                LEFT JOIN label_datas on label.id = label_datas.label_id 
                WHERE label.team_id = $team_id 
                AND label.type = '$type'
                GROUP BY label.id"
        );
       
       $all_labels = $this->removeArrayWrapper('label', $all_labels);
       
       $target_labels = $this->query(
            "SELECT label.id,label.label,label.parent_id FROM label 
                JOIN label_datas on label.id = label_datas.label_id 
                WHERE label_datas.target_id = $target_id
                AND label.team_id = $team_id 
                AND label.type = '$type'"
        );
       
       $target_labels = $this->removeArrayWrapper('label', $target_labels);
       
       $res_data = array();
       $tmp_data = array();
       
       foreach ($all_labels as $value) {
         $tmp_data[$value['id']] = array( 'id'=>$value['id'], 'parent_id'=>$value['parent_id'],'label'=>$value['label'] );
       }
       
       foreach ($target_labels as $index => $value) {
         $data = array('id'=>$value['id'],'label'=>array($value['label']));
         if( !empty($value['parent_id']) ){
           $parent_id = $value['parent_id'];
           while($parent_id) {
            array_unshift($data['label'], $tmp_data[$parent_id]['label']);
            $parent_id = $tmp_data[$parent_id]['parent_id'];
           }
         }
         $res_data[] = $data;
       }
       
        return $res_data;
    }
}

