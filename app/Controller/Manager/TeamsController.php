<?php

/**
 * Controller for Project
 * 
 * @package       app.Controller
 * 
 */
class TeamsController extends AppController {

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Label');
        $this->loadModel('Management');
        $this->loadModel('AccessLog');
        $this->loadModel('Bookmark');
        $this->loadModel('Tag');
        $this->loadModel('Device');
        $this->loadModel('User');
        $this->loadModel('Plan');
        $this->loadModel('Link');
        $this->Auth->deny();
    }

    /**
     * Display Project's label
     */
    public function label() {

        $input = $this->request->data;
        if ($this->request->is('post')) {
            $options = array(
                'order' => array('display_order' => 'asc'),
                'conditions' => array(
                    'type' => 'TeamModel',
                )
            );

            if (isset($input['label_status']) && $input['label_status'] != '') {
                $options['conditions']['AND'] = array('status' => $input['label_status']);
            }

            if (!empty($input['search']) && $input['search'] != '') {
                $options['conditions']['OR'] = array(
                    array('label LIKE' => '%' . str_replace('%', '\%', $input['search']) . '%'),
                );
            }

            $labels = $this->Label->find('all', $options);
            $labels = $this->Label->removeArrayWrapper('Label', $labels, 'id');
            // Search for parent label
            $labels += $this->Label->findAllParents($labels);
        } else {
            $labels = $this->Label->getLabelsArray('TeamModel');
        }

        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        $labelscount = $this->Label->query('
            SELECT label_id, count(*) AS total 
            FROM label_datas 
            WHERE label_id in (SELECT id FROM label WHERE type = \'TeamModel\') 
            GROUP BY label_id'
        );

        $count = array();

        foreach ($labelscount as $label) {
            $count[$label['label_datas']['label_id']] = array(
                'id' => $label['label_datas']['label_id'],
                'total' => $label[0]['total']
            );
        }

        $this->set(array(
            'labels' => $labels,
            'count' => $count,
            'type' => 'TeamModel',
        ));

        return $this->render('/Labels/index');
    }

    /**
     * Build a URL will all the search elements in it
     */
    function filter() {
        // the page we will redirect to
        $url['action'] = 'index';

        // build a URL will all the search elements in it
        // the resulting URL will be
        // example.com/cake/posts/index/Search.keywords:mykeyword/Search.tag_id:3
        foreach ($this->data as $k => $v) {
            foreach ($v as $kk => $vv) {
                $url[$k . '.' . $kk] = $vv;
            }
        }
        // redirect the user to the url
        $this->redirect($url, null, true);
    }

    /**
     * Show list Project information
     */
    public function index() {
        // Filter
        $conditions = array();
        //Transform POST into GET
        if (($this->request->is('post') || $this->request->is('put')) && isset($this->data['Filter'])) {
            $filter_url['controller'] = $this->request->params['controller'];
            $filter_url['action'] = $this->request->params['action'];
            // We need to overwrite the page every time we change the parameters
            $filter_url['page'] = 1;
            // for each filter we will add a GET parameter for the generated url

            if (isset($this->data['Filter'])) {
                foreach ($this->data['Filter'] as $name => $value) {
                    if ($value != trim('')) {
                        // You might want to sanitize the $value here
                        // or even do a urlencode to be sure
                        $filter_url[$name] = Utility_Str::escapehtml($value);
                    }
                }
            }
            // now that we have generated an url with GET parameters, 
            // we'll redirect to that page
            return $this->redirect($filter_url);
        } else {
            // Inspect all the named parameters to apply the filters
            $joins = array();
            foreach ($this->params['named'] as $param_name => $value) {
                $value = Utility_Str::returnhtml($value);
                // Don't apply the default named parameters used for pagination
                if (!in_array($param_name, array('page', 'sort', 'direction', 'limit'))) {
                    // You may use a switch here to make special filters
                    // like "between dates", "greater than", etc
                    if ($param_name == "name") {
                        $conditions += array(
                            array('Team.name LIKE' => '%' . str_replace('%', '\%', $value) . '%')
                        );
                    } elseif ($param_name == "manager") {
                        $filters_id = $this->Management->find('all', array(
                            'conditions' => array(
                                'name' => $value,
                                'parent_id' => $this->Auth->user('id')
                            ),
                            'fields' => 'team_id',
                            'group' => 'team_id'
                        ));
                        $filters_id = $this->Management->removeArrayWrapper('Management', $filters_id);
                        $f_ids = array();
                        foreach ($filters_id as $filter_id) {
                            $f_ids[] = $filter_id['team_id'];
                        }

                        $conditions += array('Team.id' => $f_ids);
                    } else {
                        $joins = array(
                            array(
                                'table' => 'label_datas',
                                'alias' => 'lbd',
                                'type' => 'INNER',
                                'conditions' => array('Team.id = lbd.target_id')
                            )
                        );
                        $conditions += array('lbd.label_id' => $value);
                    }
                    $this->request->data['Filter'][$param_name] = $value;
                }
            }
        }
        /*
        $manager_ids = $this->Management->find('all', array(
            'conditions' => array('parent_id' => $this->Auth->user('id')),
            'fields' => array('id')
        ));
        $manager_ids = $this->Management->removeArrayWrapper('Management', $manager_ids, 'id');
        $m_ids = array();
        foreach ($manager_ids as $manager_id) {
            $m_ids[] = $manager_id['id'];
        }
        array_push($m_ids, $this->Auth->user('id'));
        */
        $conditions += array('management_id' => $this->Auth->user('id'));
        $this->Team->recursive = 0;
        $this->paginate = array(
            'limit' => 20,
            'order' => array('id' => 'asc'),
            'joins' => $joins,
            'conditions' => $conditions
        );
        /* end filter */

        $teams = $this->paginate('Team');
        if (count($teams) > 0) {
            $query_date = date('Y-m-d H:i:s');
            // First day of the month.
            $fday = date('Y-m-01 00:00:00', strtotime($query_date));
            // Last day of the month.
            $lday = date('Y-m-t 23:59:59', strtotime($query_date));
            $concat = 'CONCAT(p_head, ".", p_lot, ".", p_num)';
            foreach ($teams as &$team) {
                $id = $team['Team']['id'];
                // Get Label label
                $team_labels = $this->Label->LabelData->find('all', array(
                    'conditions' => array(
                        'LabelData.target_id' => $id,
                        'Label.type' => 'TeamModel'
                    ),
                    'fields' => 'Label.label, Label.id',
                    'group' => 'Label.label'
                ));
                foreach ($team_labels as $team_label) {
                    $team_label['Label'] = $team_label['Label']['label'];
                    $team['Team']['label'][] = $team_label['Label'];
                }
                // Get total access plate running in current month
                $plates = $this->Tag->find('all', array(
                    'conditions' => array('team_id' => $id),
                    'fields' => array('tag')
                ));

                $team['Team']['plate_count'] = $team['Team']['plate_total'] = 0;
                if ($plates) {
                    $tag_series = array();
                    foreach ($plates as &$plate) {
                        $tag_series[] = $plate['Tag']['tag'];
                    }
                    $team['Team']['plate_count'] = $this->AccessLog->find('count', array(
                        'conditions' => array(
                            $concat => $tag_series,
                            'team_id' => $id,
                            'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday)
                        ),
                    ));
                    $team['Team']['plate_total'] = $this->AccessLog->find('count', array(
                        'conditions' => array('team_id' => $id)
                    ));
                }

                // Get total content running in current month
                $team['Team']['content_count'] = $team['Team']['content_total'] = 0;
                $a_content = $this->AccessLog->find('count', array(
                    'conditions' => array(
                        'team_id' => $id,
                        "contents != ''",
                        'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday)
                    )
                ));

                $c_total = $this->AccessLog->find('count', array(
                    'conditions' => array(
                        'team_id' => $id,
                        "contents != ''",
                    )
                ));
                $team['Team']['content_count'] = $a_content;
                $team['Team']['content_total'] = $c_total;

                //Get total access in current month
                $access = $this->AccessLog->find('count', array(
                    'conditions' => array(
                        'team_id' => $id,
                        'created_at BETWEEN ? AND ?' => array($fday, $lday)
                    ),
                ));
                $team['Team']['access_count'] = $access;

                // Get lastest access
                $last_access = $this->AccessLog->find('first', array(
                    'conditions' => array('team_id' => $id),
                    'order' => array('created_at' => 'desc'),
                    'fields' => 'created_at'
                ));
                $team['Team']['last_access'] = (isset($last_access['AccessLog'])) ? $last_access['AccessLog']['created_at'] : '';
            }
        }
        /* Add filler */
        //List Label
        $labels = $this->Label->find('all', array(
            'order' => array('label' => 'asc'),
            'conditions' => array('type' => 'TeamModel')
        ));
        $labels = $this->Label->removeArrayWrapper('Label', $labels, 'id');
        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));
        //List Management has authority = 3
        $list_manager = $this->Management->find('list', array(
            'conditions' => array(
                'authority' => 3,
//                'status' => 1,
                'parent_id' => $this->Auth->user('id')
            ),
            'group' => 'name'
        ));

        // Total project running in current month
        $query_date = date('Y-m-d H:i:s');
        $fday = date('Y-m-01 00:00:00', strtotime($query_date));
        $lday = date('Y-m-t H:i:s', strtotime($query_date));
        $teamID = $this->getTeamid($this->Auth->user('authority'));
        $num_project = $this->Team->find('count', array(
            'conditions' => array(
                'Team.cdate BETWEEN ? AND ?' => array($fday, $lday),
                'Team.id' => $teamID
            )
        ));

        $this->set(array(
            'teams' => $teams,
            'list_lb' => $labels,
            'list_manager' => $list_manager,
            'num_project' => $num_project
        ));
    }

    /**
     * Show project detail
     * 
     * @param int $id project id
     * @return array 
     */
    public function detail($id) {
        $this->_setModalLayout();
        if (!$id) {
            $this->Session->setFlash(__('プロジェクトのIDを入力してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        $team = $this->Team->findById($id);
        // Get Management name
        $management_id = $team['Team']['management_id'];
        $manager_name = $this->Management->find('first', array(
            'conditions' => array('id' => $management_id),
            'fields' => 'login_name, id'
        ));
        $m_name = $manager_name['Management']['login_name'];
        // Get Plate tag, Plate name
        $plates = $this->Tag->find('all', array(
            'conditions' => array('team_id' => $id),
            'fields' => array('tag', 'name', 'id')
        ));
        $plates = $this->Tag->removeArrayWrapper('Tag', $plates);
        // Get list Url and Subtype
        $list_url['url'] = array();
        foreach ($plates as $plate) {
            $plate_id = $plate['id'];
            $l_url = $this->Link->find('all', array(
                'conditions' => array('tag_id' => $plate_id),
                'fields' => array('sub_type', 'url')
            ));
            $list_url['url'] = $l_url;
        }
        $list_url = $this->Tag->removeArrayWrapper('Link', $list_url['url']);
        $this->set(array(
            'team' => $team,
            'manager_name' => $m_name,
            'list_url' => $list_url,
            'tags' => $plates
        ));
    }

    /**
     * Add new project record
     */
    public function add() {
        $this->_setModalLayout();
        //List Label
        $list_lb = $this->Label->find('all', array(
            'conditions' => array('type' => 'TeamModel')
        ));
        $list_lb = $this->Label->removeArrayWrapper('Label', $list_lb, 'id');
        $list_lb = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($list_lb));

        // List Plan
        $listPlan = $this->Plan->find('list', array(
            'fields' => array('id', 'type')
        ));

        $this->set(array(
            'list_lb' => $list_lb,
            'listPlan' => $listPlan
        ));

        if ($this->request->is('post') || $this->request->is('put')) {

            // Add project
            if (isset($this->request->data['Team'])) {
                $rq_data = $this->request->data['Team'];
                // Upload image
                if (is_uploaded_file($rq_data['splash']['tmp_name'])) {
                    if (($rq_data['splash']['type'] == 'image/jpg') || ($rq_data['splash']['type'] == 'image/jpeg') || ($rq_data['splash']['type'] == 'image/png') || ($rq_data['splash']['type'] == 'image/gif')) {
                        $imginfo = pathinfo($rq_data['splash']['name']);
                        $filename = $imginfo['filename'] . '_' . md5(time()) . '.' . $imginfo['extension'];
                        move_uploaded_file(
                                $rq_data['splash']['tmp_name'], WWW_ROOT . 'upload' . DS . 'splash' . DS . $filename
                        );
                        // store the filename in the array to be saved to the db
                        $rq_data['splash'] = $filename;
                    } else {
                        $this->Session->setFlash(__('有効なイメージを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
                        $this->redirect(array('action' => 'index'));
                    }
                } else {
                    $rq_data['splash'] = '';
                }
                $rq_data['management_id'] = $this->Auth->user('id');
                $dup = $this->Team->find('all', array('conditions' => array(
                        'name' => $rq_data['name']
                )));
                if (empty($dup)) {
                    $rq_data['cdate'] = date('Y-m-d H:i:s');
                    if ($this->Team->save($rq_data,false)) {
                        $new_team_id = $this->Team->getLastInsertId();
                        // Add Label            
                        if (isset($this->request->data['Label'])) {
                            $rq_label = $this->request->data['Label'];
                            $rq_label['type'] = 'TeamModel';
                            $rq_label['label'] = $rq_label['add_new_text'];
                            $rq_label['team_id'] = Configure::read('teamId');
                            $rq_label['cdate'] = date('Y-m-d H:i:s');
                            if (isset($rq_label['add_new_text'])) {
                                $existing = $this->Label->find('all', array('conditions' => array(
                                        'label' => $rq_label['add_new_text'],
                                        'team_id' => $rq_label['team_id'],
                                        'type' => $rq_label['type'],
                                )));
                            } else {
                                $existing = '';
                            }
                            if ($rq_label['new_label'] != null) {
                                if (isset($rq_label['add_new_text']) && $rq_label['add_new_text'] != null) {
                                    $rq_label['parent_id'] = $rq_label['new_label'];
                                    if (empty($existing)) {
                                        if ($this->Label->save($rq_label)) {
                                            // Get new ID record
                                            $new_id = $this->Label->getLastInsertId();
                                        } else {
                                            $this->Session->setFlash(__('Can not create new label'), 'alert-box', array('class' => 'alert-danger'));
                                            $this->redirect(array('action' => 'index'));
                                        }
                                    } else {
                                        $this->Session->setFlash(__('このラベルは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                                        return $this->redirect($this->referer());
                                    }
                                } else {
                                    $lb_id = $rq_label['new_label'];
                                }
                            } else {
                                if (isset($rq_label['add_new_text']) && $rq_label['add_new_text'] != null) {
                                    $rq_label['parent_id'] = 0;
                                    if (empty($existing)) {
                                        if ($this->Label->save($rq_label)) {
                                            // Get new ID record
                                            $new_id = $this->Label->getLastInsertId();
                                        } else {
                                            $this->Session->setFlash(__('Can not create new label'), 'alert-box', array('class' => 'alert-danger'));
                                            $this->redirect(array('action' => 'index'));
                                        }
                                    } else {
                                        $this->Session->setFlash(__('このラベルは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                                        return $this->redirect($this->referer());
                                    }
                                }
                            }
                        }
                        /* Save record to label_datas table */
                        $this->request->data['LabelDatas']['target_id'] = $new_team_id;
                        $this->request->data['LabelDatas']['cdate'] = date('Y-m-d H:i:s');
                        if ($rq_label['new_label'] != null) {
                            if ($rq_label['add_new_text'] != null) {
                                $this->request->data['LabelDatas']['label_id'] = $new_id;
                            } else {
                                $this->request->data['LabelDatas']['label_id'] = $lb_id;
                            }
                            $this->Label->LabelData->save($this->request->data['LabelDatas']);
                        } else {
                            if ($rq_label['add_new_text'] != null) {
                                $this->request->data['LabelDatas']['label_id'] = $new_id;
                                $this->Label->LabelData->save($this->request->data['LabelDatas']);
                            }
                        }
                        /* End save */
                        $this->Session->setFlash(__('プロジェクトのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                        return $this->redirect(array('action' => 'index'));
                    } else {
                        $this->Session->setFlash(__('新規プロジェクトを登録することはできません。'), 'alert-box', array('class' => 'alert-danger'));
                        return $this->redirect(array('action' => 'index'));
                    }
                } else {
                    $this->Session->setFlash(__('このプロジェクトは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                    return $this->redirect($this->referer());
                }
            }
        }
        $this->render('edit');
    }

    /**
     * Update project record
     * 
     * @param int $id project id
     */
    public function edit($id) {
        $this->_setModalLayout();

        if (!$id) {
            $this->Session->setFlash(__('プロジェクトのIDを入力してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        // List Plan
        $listPlan = $this->Plan->find('list', array(
            'fields' => array('id', 'type')
        ));

        //List Label
        $list_lb = $this->Label->find('all', array(
            'conditions' => array('type' => 'TeamModel')
        ));
        $list_lb = $this->Label->removeArrayWrapper('Label', $list_lb, 'id');
        $list_lb = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($list_lb));

        //Get current label
        $team_labels = $this->Label->LabelData->find('all', array(
            'conditions' => array(
                'LabelData.target_id' => $id,
                'Label.type' => 'TeamModel'
            ),
            'fields' => 'Label.label, Label.id',
            'group' => 'Label.label'
        ));
        $cr_label = array();
        foreach ($team_labels as $team_label) {
            $team_label['Label'] = $team_label['Label']['id'];
            $cr_label[] = $team_label['Label'];
        }
        $this->set(array(
            'listPlan' => $listPlan,
            'list_lb' => $list_lb,
            'cr_label' => $cr_label,
        ));
        $team = $this->Team->findById($id);
        if (!$team) {
            $this->Session->setFlash(__('Invalid Project ID Provided'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if (isset($this->request->data['Label'])) {
                // Add Label              
                $rq_label = $this->request->data['Label'];
                $rq_label['type'] = 'TeamModel';
                $rq_label['label'] = $rq_label['add_new_text'];
                $rq_label['cdate'] = date('Y-m-d H:i:s');
                if (!empty($rq_label['add_new_text'])) {
                    $existing = $this->Label->find('all', array('conditions' => array(
                            'label' => $rq_label['add_new_text'],
                            'team_id' => $rq_label['team_id'],
                            'type' => $rq_label['type'],
                    )));
                } else {
                    $existing = '';
                }
                if ( !empty($rq_label['new_label']) ) {
                    if ( !empty($rq_label['add_new_text']) ) {
                        $rq_label['parent_id'] = $rq_label['new_label'];
                        $rq_label['team_id'] = $this->Auth->user('team_id');
                        if (empty($existing)) {
                            if ($this->Label->save($rq_label)) {
                                // Get new ID record
                                $new_id = $this->Label->getLastInsertId();
                            } else {
                                $this->Session->setFlash(__('Can not create new label'), 'alert-box', array('class' => 'alert-danger'));
                                $this->redirect(array('action' => 'index'));
                            }
                        } else {
                            $this->Session->setFlash(__('このラベルは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                            return $this->redirect($this->referer());
                        }
                    } else {
                        if (isset($this->request->data['_label'])) {
                            if (in_array($rq_label['new_label'], $this->request->data['_label'])) {
                                $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                                $this->redirect(array('action' => 'index'));
                            }
                        }
                        $lb_id = $rq_label['new_label'];
                    }
                } else {
                    if (isset($rq_label['add_new_text']) && !empty($rq_label['add_new_text'])) {
                        $rq_label['parent_id'] = 0;
                        $rq_label['team_id'] = $this->Auth->user('team_id');
                        if (empty($existing)) {
                            if ($this->Label->save($rq_label)) {
                                // Get new ID record
                                $new_id = $this->Label->getLastInsertId();
                            } else {
                                $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                                $this->redirect(array('action' => 'index'));
                            }
                        } else {
                            $this->Session->setFlash(__('このラベルは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                            return $this->redirect($this->referer());
                        }
                    }
                }
            }

            // Update exited label
            if (isset($this->request->data['_label'])) {
                $own_labels = $this->request->data['_label'];
                if (count($own_labels) != count(array_unique($own_labels))) {
                    $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                    $this->redirect(array('action' => 'index'));
                } else {
                    foreach ($own_labels as $key => $value) {
                        $this->Label->LabelData->query("UPDATE label_datas SET label_id = {$value} WHERE target_id = {$id} AND label_id = {$key}");
                    }
                }
            }

            // Check duplicate label_datas
            $check_label_datas = $this->Label->LabelData->find('all', array(
                'conditions' => array('target_id' => $id),
                'fields' => array('label_id')
            ));
            $check_label_datas = $this->Bookmark->removeArrayWrapper('LabelData', $check_label_datas, 'label_id');
            $ids = array_keys($check_label_datas);

            if (isset($this->request->data['Team'])) {
                $this->Team->id = $id;
                $rq_data = $this->request->data['Team'];
                /* Upload image */
                if (is_uploaded_file($rq_data['splash']['tmp_name'])) {
                    if (($rq_data['splash']['type'] == 'image/jpg') || ($rq_data['splash']['type'] == 'image/jpeg') || ($rq_data['splash']['type'] == 'image/png') || ($rq_data['splash']['type'] == 'image/gif')) {
                        $imginfo = pathinfo($rq_data['splash']['name']);
                        $filename = $imginfo['filename'] . '_' . md5(time()) . '.' . $imginfo['extension'];
                        move_uploaded_file(
                                $rq_data['splash']['tmp_name'], WWW_ROOT . 'upload' . DS . 'splash' . DS . $filename
                        );
                        // store the filename in the array to be saved to the db 
                        $rq_data['splash'] = $filename;
                    } else {
                        $this->Session->setFlash(__('有効なイメージを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
                        $this->redirect(array('action' => 'index'));
                    }
                } else {
                    $rq_data['splash'] = $team['Team']['splash'];
                }
                $rq_data['cdate'] = date('Y-m-d H:i:s');
                if ($rq_data['name'] == $rq_data['new_name']) {
                    $dup = '';
                } else {
                    $dup = $this->Team->find('all', array('conditions' => array(
                            'name' => $rq_data['new_name']
                    )));
                }
                if (empty($dup)) {
                    $rq_data['name'] = $rq_data['new_name'];
                    if ($this->Team->save($rq_data)) {
                        /* Save record to label_datas table */
                        $this->request->data['LabelDatas']['target_id'] = $id;
                        $this->request->data['LabelDatas']['cdate'] = date('Y-m-d H:i:s');
                        if ($rq_label['new_label'] != null) {
                            if ($rq_label['add_new_text'] != null) {
                                $this->request->data['LabelDatas']['label_id'] = $new_id;
                            } else {
                                $this->request->data['LabelDatas']['label_id'] = $lb_id;
                            }
                            if (!in_array($this->request->data['LabelDatas']['label_id'], $ids)) {
                                $this->Label->LabelData->save($this->request->data['LabelDatas']);
                            }
                        } else {
                            if (isset($rq_label['add_new_text']) && $rq_label['add_new_text'] != null) {
                                $this->request->data['LabelDatas']['label_id'] = $new_id;
                                if (!in_array($this->request->data['LabelDatas']['label_id'], $ids)) {
                                    $this->Label->LabelData->save($this->request->data['LabelDatas']);
                                }
                            }
                        }
                        /* End save */
                        $this->Session->setFlash(__('プロジェクトのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                        $this->redirect(array('action' => 'index'));
                    } else {
                        $this->Session->setFlash(__('プロジェクトをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                        $this->redirect(array('action' => 'index'));
                    }
                } else {
                    $this->Session->setFlash(__('このプロジェクトは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                    return $this->redirect($this->referer());
                }
            }
        }
        if (!$this->request->data) {
            $this->request->data = $team;
        }
        $this->set('team', $team);
    }

    /**
     * Delete selected projects
     */
    public function delete() {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        $rq_data = $this->request->data;
        $ids = $rq_data['id'];
        foreach ($ids as $key => $value) {
            // Get list project's label
            $lb_id = $this->Label->find('all', array(
                'conditions' => array(
                    'Label.team_id' => $value
                ),
                'fields' => array('Label.id')
            ));
            $lb_id = $this->Label->removeArrayWrapper('Label', $lb_id);
            $lb_ids = array();
            foreach ($lb_id as $label) {
                $lb_ids[] = $label['id'];
            }
            // Get list project's bookmark
            $bm_id = $this->Bookmark->find('all', array(
                'conditions' => array('team_id' => $ids),
                'fields' => array('id')));
            $bm_id = $this->Label->removeArrayWrapper('Bookmark', $bm_id);
            $bm_ids = array();
            foreach ($bm_id as $bookmark_id) {
                $bm_ids[] = $bookmark_id['id'];
            }
            // Get list project's user
            $user = $this->User->find('all', array(
                'conditions' => array('team_id' => $ids),
                'fields' => array('id')
            ));
            $user = $this->User->removeArrayWrapper('User', $user);
            $users = array();
            foreach ($user as $_user) {
                $users[] = $_user['id'];
            }
            // Get list project's plate
            $tag = $this->Tag->find('all', array(
                'conditions' => array('team_id' => $ids),
                'fields' => array('id', 'tag')
            ));
            $tag = $this->Tag->removeArrayWrapper('Tag', $tag);
            $tag_ids = array();
            foreach ($tag as $_tag) {
                $tag_ids['id'] = $_tag['id'];
                $tag_ids['tag'] = $_tag['tag'];
            }
            // Delete Label
            if (!empty($lb_ids)) {
                $this->Label->LabelData->deleteAll(array('label_id' => $lb_ids), false);
                $this->Label->deleteAll(array('id' => $lb_ids), false);
            }

            // Delete Bookmark
            if (!empty($bm_ids)) {
                $this->Link->updateAll(array('bookmark_id' => null), array('bookmark_id' => $bm_ids));
                $this->AccessLog->updateAll(array('bookmark_id' => null), array('bookmark_id' => $bm_ids));
                $this->Bookmark->deleteAll(array('team_id' => $ids), false);
            }

            //Delete Plate
            if (!empty($tag_ids)) {
                $concat = 'CONCAT(p_head, ".", p_lot, ".", p_num)';
                $this->AccessLog->deleteAll(array($concat => $tag_ids['tag']), false);
                $this->Tag->Link->deleteAll(array('Link.tag_id' => $tag_ids['id']), false);
                $this->Tag->deleteAll(array('team_id' => $ids), false);
            }

            //Delete Access_log
            $this->AccessLog->deleteAll(array('team_id' => $ids), false);

            //Delete User
            $this->Link->deleteAll(array('user_id' => $users), false);
            $this->Device->deleteAll(array('user_id' => $users), false);
            $this->User->deleteAll(array('User.team_id' => $ids), false);

            //Update Management
            $this->Management->updateAll(array('team_id' => null), array('team_id' => $ids));
        }
        if ($this->Team->delete($ids)) {
            $this->Session->setFlash(__('プロジェクトが削除されました。'), 'alert-box', array('class' => 'alert-success'));
        } else {
            $this->Session->setFlash(__('プロジェクトを削除することはできません。'), 'alert-box', array('class' => 'alert-danger'));
        }
        echo json_encode(1);
        exit;
    }

    /**
     * Update data from ajax quick edit form
     * 
     * @return json 
     */
    public function quickEdit() {
        if ($this->request->is('post')) {
            $rq_data = $this->request->data;
            $type = $rq_data['type'];

            if ($type == 2) {
                $ids = $rq_data['id'];
                $input = $rq_data['input'];
                $now = date('Y-m-d H:i:s');
                if ($this->Team->updateAll(array('name' => "'$input'", 'cdate' => "'$now'"), array('Team.id' => $ids))) {
                    $this->Session->setFlash(__('プロジェクトのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                } else {
                    $this->Session->setFlash(__('プロジェクトをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                }
            } elseif ($type == 3) {
                $id = $rq_data['id'];
                $input = $rq_data['input'];
                $cr_data = $rq_data['cr_data'];
                $cr_date = date('Y-m-d H:i:s');
//                $this->Management->query("UPDATE management SET team_id = {$id}, update_date = '{$cr_date}' WHERE id = {$input}");

                if ($this->Management->updateAll(array('team_id' => $id, 'update_date' => "'{$cr_date}'"), array('id' => $input))) {
                    $this->Session->setFlash(__('プロジェクトのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                } else {
                    $this->Session->setFlash(__('プロジェクトをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                }
            } elseif ($type == 5) {
                $id = $rq_data['id'];
                $input = $rq_data['input'];
                if ($this->Team->save(array('id' => $id, 'code' => $input))) {
                    $this->Session->setFlash(__('プロジェクトのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                } else {
                    $this->Session->setFlash(__('プロジェクトをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                }
            } elseif ($type == 6) {
                $ids = $rq_data['ids'];
                foreach ($ids as $v_id) {
                    $visible = $this->Team->find('first', array(
                        'conditions' => array('id' => $v_id),
                        'fields' => array('valid')
                    ));
                    $status = ($visible['Team']['valid'] == 1) ? 0 : 1;

                    if ($this->Team->save(array('id' => $v_id, 'valid' => $status))) {
                        $this->Session->setFlash(__('プロジェクトのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                    } else {
                        $this->Session->setFlash(__('プロジェクトをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                    }
                }
            }

            echo json_encode(1);
            exit;
        }
    }

    /**
     * Update project's label from ajax quick edit label form
     * 
     */
    public function quickedit_label() {
        $this->autoRender = false;
        if (isset($_REQUEST['id'])) {
            $team_id = $_REQUEST ['id'];
            //List Label
            $labels = $this->Label->find('all', array(
                'order' => array('label' => 'asc'),
                'conditions' => array('type' => 'TeamModel')
            ));
            $labels = $this->Label->removeArrayWrapper('Label', $labels, 'id');
            $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));
            // List team's label id
            $team_labels = $this->Label->LabelData->find('all', array(
                'conditions' => array(
                    'LabelData.target_id' => $team_id,
                    'Label.type' => 'TeamModel'
                ),
                'order' => array('Label.label' => 'asc'),
                'fields' => array('Label.label', 'Label.id')
            ));
            $label_id = array();
            foreach ($team_labels as $team_label) {
                $label_id[$team_label['Label']['id']] = $team_label['Label']['id'];
            }
            $this->set(array(
                'team_id' => $team_id,
                'list_labels' => $labels,
                'label_id' => $label_id
            ));
            return $this->render('quickedit_label', 'ajax');
        }
        if (isset($this->request->data['_label'])) {
            $rq_label = $this->request->data['_label'];
            //print_r($rq_label); die;
            $team_id = $this->request->data['Team']['team_id'];
            if (count($rq_label) != count(array_unique($rq_label))) {
                $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                $this->redirect(array('action' => 'index'));
            } else {
                foreach ($rq_label as $key => $value) {
                    $this->Label->LabelData->query("UPDATE label_datas SET label_id = {$value} WHERE target_id = {$team_id} AND label_id = {$key}");
                }
                $this->Session->setFlash(__('プロジェクトのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                $this->redirect(array('action' => 'index'));
            }
        }
        $this->redirect(array('action' => 'index'));
    }

}

?>
