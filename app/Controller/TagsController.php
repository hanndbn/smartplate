<?php

App::uses('AppController', 'Controller');
App::uses('Image', 'Model');
App::uses('Link', 'Model');

/**
 * Controller for Tag model
 *
 * @package       app.Controller
 */
class TagsController extends AppController
{

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Label');
        $this->loadModel('Link');
        $this->loadModel('Order');
        $this->loadModel('AccessLog');
        $this->loadModel('Management');
        $this->loadModel('Team');
        $this->Auth->deny();
    }

    /**
     * Build a URL will all the search elements in it
     */
    function filter()
    {
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

    public function ajaxBookmarkContent()
    {
        if (isset($_POST['id'])) {
            $bookmarkID = $_POST['id'];
            $bookmarkContents = $this->Link->find('all', array(
                'conditions' => array(
                    'bookmark_id' => $bookmarkID
                ),
                'fields' => array('type', 'sub_type', 'url', 'icon', 'link_text')
            ));
            $bookmarkContents = $this->Link->removeArrayWrapper('Link', $bookmarkContents);
            echo json_encode($bookmarkContents);
            exit;
        }
    }

    /**
     * Check multibyte japanese characters
     */
    public function ajaxCheckCharacterSize()
    {
        if ($this->request->is('post')) {
            $text = $this->request->data['character'];
            $length = $this->request->data['maxlength'];
            $result = mb_strwidth($text, 'UTF-8');
            echo json_encode(array('result' => $result, 'maxlength' => $length));
            exit;
        }
    }

    /**
     * For system view
     * Show list plate information
     */
    public function system_index()
    {
        $this->index();
        $this->render('index');
    }

    /**
     * Show list plate information
     */
    public function index()
    {
        $this->loadModel('User');

        /* Add filter */
        //List Label
        $labels = $this->Label->getLabelsArray('TagModel');
        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        // List user name
        $this->User->recursive = -1;
        $list_user = $this->User->find('list', array(
            'conditions' => array(
                'team_id' => $this->Auth->user('team_id')
            ),
            'fields' => array('User.id', 'User.name')
        ));
        // flag filter NFC QR follow date
        $flgFilterFollowDate = false;
        $flgFilter = false;
        $dateFrom = null;
        $dateTo = null;

        // Filter
        $conditions = array();
        //Transform POST into GET
        if (($this->request->is('post') || $this->request->is('put')) && isset($this->data['Filter'])) {
            $filter_url['controller'] = $this->request->params['controller'];
            $filter_url['action'] = $this->request->params['action'];
            // We need to overwrite the page every time we change the parameters
            $filter_url['page'] = 1;

            if(empty($this->data['Filter']['from']) || empty($this->data['Filter']['to'])){
                $flgFilterFollowDate = false;
            } else {
                $flgFilterFollowDate = true;
            }

            // for each filter we will add a GET parameter for the generated url
            if (isset($this->data['Filter'])) {
                foreach ($this->data['Filter'] as $name => $value) {
                    if ($value != trim('')) {
                        if(($flgFilterFollowDate && ($name == "from" || $name == "to")) || (!($name == "from") && !($name == "to"))) {
                            // You might want to sanitize the $value here
                            // or even do a urlencode to be sure
                            $filter_url[$name] = Utility_Str::escapehtml($value);
                        }
                    }
                }
            }

            // now that we have generated an url with GET parameters,
            // we'll redirect to that page
            $this->Session->write('Access.filter.tag', $filter_url);
            return $this->redirect($filter_url);
        }
        else {
            // Inspect all the named parameters to apply the filters
            $joins = array();
            $need_clear_fileter = true;
            foreach ($this->params['named'] as $param_name => $value) {

                $value = Utility_Str::returnhtml($value);
                // Don't apply the default named parameters used for pagination
                if (!in_array($param_name, array('page', 'sort', 'direction', 'limit', 'act'))) {
                    // You may use a switch here to make special filters
                    // like "between dates", "greater than", etc
                    if($param_name == 'from'){
                        $dateFrom = date('Y-m-d', strtotime($value));
                    } elseif($param_name == 'to'){
                        $dateTo = date('Y-m-d', strtotime($value));
                    } elseif ($param_name == "name") {
                        $conditions += array(
                            array('OR' => array(
                                array('Tag.tag LIKE' => '%' . str_replace('%', '\%', $value) . '%'),
                                array('Tag.name LIKE' => '%' . str_replace('%', '\%', $value) . '%')
                            )
                            )
                        );
                    } else if($param_name = "filter"){
                        if($value == 'filter'){
                            $flgFilter = true;
                        }
                        else if($value == 'filterFollowDate')
                            $flgFilterFollowDate = true;
                    }
                    elseif ($param_name == "user") {
                        $joins = array(
                            array(
                                'table' => 'label_datas',
                                'alias' => 'lbd',
                                'type' => 'LEFT',
                                'conditions' => array('Tag.id = lbd.target_id')
                            ),
                            array(
                                'table' => 'links',
                                'alias' => 'link',
                                'type' => 'LEFT',
                                'conditions' => array('link.tag_id = Tag.id')
                            ),
                            array(
                                'table' => 'user',
                                'alias' => 'user',
                                'type' => 'LEFT',
                                'conditions' => array('user.id = link.user_id')
                            )
                        );
                        $conditions += array('user.id' => $value);
                    } else {
                        $joins = array(
                            array(
                                'table' => 'label_datas',
                                'alias' => 'lbd',
                                'type' => 'LEFT',
                                'conditions' => array('Tag.id = lbd.target_id')
                            ),
                            array(
                                'table' => 'label',
                                'alias' => 'lb',
                                'type' => 'LEFT',
                                'conditions' => array('lb.id = lbd.label_id')
                            )
                        );
                        $conditions += array('lbd.label_id' => $value);
                    }
                    $this->request->data['Filter'][$param_name] = $value;
                    $need_clear_fileter = false;
                }
            }
            if( $need_clear_fileter ){
              $this->Session->write('Access.filter.tag', '');
            }
        }
        $this->Tag->recursive = 0;
        if ($this->request->prefix != 'system') {
            if ($this->Auth->user('authority') == '2' || $this->Auth->user('authority') == '1') {
                $listUsers = $this->Management->getChildID($this->Auth->user('id'));
                // edit by geo
                array_unshift($listUsers,$this->Auth->user('id'));
                $team_id = $this->Team->getUserTeamID($listUsers);
                $conditions += array( 'OR' => array('Tag.team_id' => $team_id, 'Tag.management_id' => $listUsers));
            } else {
                $team_id = $this->Auth->user('team_id');
                $conditions += array('Tag.team_id' => $team_id); // edit by geo
            }
        }

        $this->paginate = array(
            'limit' => '20',
            'order' => array('id' => 'asc'),
            'joins' => $joins,
            'conditions' => $conditions,
            'group' => 'Tag.id'
        );

        $tagsSession = $this->Tag->find('all', array(
            'order' => array('id' => 'asc'),
            'joins' => $joins,
            'conditions' => $conditions,
            'group' => 'Tag.id'
        ));

        /* end filter */
        if (isset($this->params['named']['act']) && $this->params['named']['act'] == 'export') {
            $tagsExport = $this->Tag->find('all', array(
                'order' => array('id' => 'asc'),
                'joins' => $joins,
                'conditions' => $conditions,
                'group' => 'Tag.id'
            ));
            $this->export($tagsExport);
        }

        $tags = $this->paginate('Tag');
        if (count($tags) > 0) {
            $query_date = date('Y-m-d H:i:s');
            // First day of the month.
            $fday = date('Y-m-01 H:i:s', strtotime($query_date));
            // Last day of the month.
            $lday = date('Y-m-t H:i:s', strtotime($query_date));
            foreach ($tags as &$tag) {
                $id = $tag['Tag']['id'];
                $tag_series = $tag['Tag']['tag'];
                /* Get tag's label */
                $type = 'TagModel';
                $tag['lb_name'] = $this->Label->label_Query($id, $type);

                /* Get link by bookmark */
                $links = $this->Link->find('all', array(
                    'conditions' => array('Link.tag_id' => "{$id}"),
                    'fields' => array('Link.bookmark_id'),
                    'group' => array('Link.bookmark_id')
                ));
                $tag['link_bm'] = array();
                foreach ($links as $link) {
                    $link = $link['Link']['bookmark_id'];
                    $tag['link_bm'][] = $link;
                }

                /* Get link update */
                $tag['Link_update'] = '';
                $link_update = $this->Link->find('first', array(
                    'conditions' => array('Link.tag_id' => "{$id}"),
                    'fields' => array('Link.udate'),
                    'order' => array('Link.udate' => 'desc')
                ));

                $tag['Link_update'] = $link_update;

                /* Get access_log create_at */
                $concat = 'CONCAT(p_head, ".", p_lot, ".", p_num)';
                $a_type = $this->AccessLog->find('first', array(
                    'fields' => array('AccessLog.created_at'),
                    'conditions' => array($concat => $tag_series),
                    'order' => array('AccessLog.created_at' => 'desc'),
                ));

                $tag['Access_cdate'] = isset($a_type['AccessLog']['created_at']) ? $a_type['AccessLog']['created_at'] : '';

                /* Get total access_log in current month */
                $a_total = $this->AccessLog->find('count', array(
                    'conditions' => array(
                        $concat => $tag_series,
                        'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday)
                    ),
                ));
                $tag['Access_total'] = isset($a_total) ? $a_total : 0;

                if($flgFilterFollowDate){

                    /* Get Tag type */
                    //Get NFC
                    $tag_nfc = $this->AccessLog->find('count', array(
                        'fields' => array('AccessLog.p_type', 'AccessLog.created_at'),
                        'conditions' => array(
                            $concat => $tag_series,
                            'AccessLog.p_type' => 'N',
                            'AccessLog.created_at BETWEEN ? AND ?' => array($dateFrom, $dateTo))
                    ));
                    $tag['nfc'] = $tag_nfc;
                    //Get QR
                    $tag_qr = $this->AccessLog->find('count', array(
                        'fields' => array('AccessLog.p_type', 'AccessLog.created_at'),
                        'conditions' => array(
                            $concat => $tag_series,
                            'AccessLog.p_type' => 'Q',
                            'AccessLog.created_at BETWEEN ? AND ?' => array($dateFrom, $dateTo))
                    ));
                    $tag['qr'] = $tag_qr;
                } else {
                    /* Get Tag type */
                    //Get NFC
                    $tag_nfc = $this->AccessLog->find('count', array(
                        'fields' => array('AccessLog.p_type'),
                        'conditions' => array(
                            $concat => $tag_series,
                            'AccessLog.p_type' => 'N')
                    ));
                    $tag['nfc'] = $tag_nfc;
                    //Get QR
                    $tag_qr = $this->AccessLog->find('count', array(
                        'fields' => array('AccessLog.p_type'),
                        'conditions' => array(
                            $concat => $tag_series,
                            'AccessLog.p_type' => 'Q')
                    ));
                    $tag['qr'] = $tag_qr;
                }
                /* Get user.name */

                $link_user_ids = $this->Link->find('all', array(
                    'conditions' => array('tag_id' => "{$id}"),
                    'fields' => array('user_id')
                ));

                $joins = array(
                    array(
                        'table' => 'user',
                        'alias' => 'user',
                        'type' => 'INNER',
                        'conditions' => array('Link.user_id = user.id')
                    )
                );
                $tag['user_name'] = '';
                foreach ($link_user_ids as &$link_user_id) {
                    $link_user_id = $this->Link->find('first', array(
                        'joins' => $joins,
                        'conditions' => array(
                            'Link.tag_id' => $id,
                            'Link.user_id' => $link_user_id['Link']['user_id']
                        ),
                        'fields' => array('user.name')
                    ));
                    $tag['user_name'][] = $link_user_id['user']['name'];
                }
            };
        }

        // Get list admin user
        $adminUser = $this->Management->find('list', array(
            'conditions' => array(
                'authority' => 1
            ),
            'fields' => array('id', 'login_name')
        ));
        if( $this->Auth->user('authority') == 1){
          // Get list management user
          $listProject = $this->Management->find('list', array(
              'conditions' => array(
                  'parent_id' => $this->Auth->user('id')
              ),
              'fields' => array('id', 'login_name')
          ));
        }else{
          // Get list Project
          $listProject = $this->Team->find('list', array(
              'conditions' => array(
                  'management_id' => $this->Auth->user('id')
              ),
              'fields' => array('id', 'name')
          ));
        }

        $this->set(array(
            'tags' => $tags,
            'list_lb' => $labels,
            'list_user' => $list_user,
            'adminUser' => $adminUser,
            'listProject' => $listProject
        ));

        $this->Session->write("Tag", $tagsSession);
    }

    public function export($tags)
    {
        $this->response->download("Tag.csv");
        if (count($tags) > 0) {
            $query_date = date('Y-m-d H:i:s');
            // First day of the month.
            $fday = date('Y-m-01 H:i:s', strtotime($query_date));
            // Last day of the month.
            $lday = date('Y-m-t H:i:s', strtotime($query_date));
            foreach ($tags as &$tag) {
                $id = $tag['Tag']['id'];
                $tag_series = $tag['Tag']['tag'];
                /* Get tag's label */
                $type = 'TagModel';
                $tag['Tag']['lb_name'] = implode(', ', $this->Label->label_Query($id, $type));
                /* Get link update */
                $tag['Tag']['Link_update'] = '';
                $link_update = $this->Link->find('first', array(
                    'conditions' => array('Link.tag_id' => "{$id}"),
                    'fields' => array('Link.udate'),
                    'order' => array('Link.udate' => 'desc')
                ));

                $tag['Tag']['Link_update'] = (isset($link_update['Link']['udate'])) ? $link_update['Link']['udate'] : '';

                /* Get access_log create_at */
                $concat = 'CONCAT(p_head, ".", p_lot, ".", p_num)';
                $a_type = $this->AccessLog->find('first', array(
                    'fields' => array('AccessLog.created_at'),
                    'conditions' => array($concat => $tag_series),
                    'order' => array('AccessLog.created_at' => 'desc'),
                ));

                $tag['Tag']['Access_cdate'] = isset($a_type['AccessLog']['created_at']) ? $a_type['AccessLog']['created_at'] : '';

                /* Get total access_log in current month */
                $a_total = $this->AccessLog->find('count', array(
                    'conditions' => array(
                        $concat => $tag_series,
                        'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday)
                    ),
                ));
                $tag['Tag']['Access_total'] = isset($a_total) ? $a_total : 0;

                /* Get Tag type */
                //Get NFC
                $tag_nfc = $this->AccessLog->find('count', array(
                    'fields' => array('AccessLog.p_type'),
                    'conditions' => array(
                        $concat => $tag_series,
                        'AccessLog.p_type' => 'N')
                ));
                $tag['Tag']['nfc'] = $tag_nfc;
                //Get QR
                $tag_qr = $this->AccessLog->find('count', array(
                    'fields' => array('AccessLog.p_type'),
                    'conditions' => array(
                        $concat => $tag_series,
                        'AccessLog.p_type' => 'Q')
                ));
                $tag['Tag']['qr'] = $tag_qr;

                /* Get user.name */

                $link_user_ids = $this->Link->find('all', array(
                    'conditions' => array('tag_id' => "{$id}"),
                    'fields' => array('user_id')
                ));

                $joins = array(
                    array(
                        'table' => 'user',
                        'alias' => 'user',
                        'type' => 'INNER',
                        'conditions' => array('Link.user_id = user.id')
                    )
                );
                $tag['Tag']['user_name'] = '';
                foreach ($link_user_ids as &$link_user_id) {
                    $link_user_id = $this->Link->find('first', array(
                        'joins' => $joins,
                        'conditions' => array(
                            'Link.tag_id' => $id,
                            'Link.user_id' => $link_user_id['Link']['user_id']
                        ),
                        'fields' => array('user.name')
                    ));
                    $tag['Tag']['user_name'][] = (isset($link_user_id['user']['name'])) ? $link_user_id['user']['name'] : '';
                }
            };
        }


        $datas = $this->Tag->removeArrayWrapper('Tag', $tags);

        $_serialize = 'datas';
        $_bom = true;
        $_header = array('ID', 'ラベル', '名前', '配信コンテンツID', '最新アクセス', '当月アクセス数', 'NFC', 'QR', '最新設定日', '設定アカウント');
        $_extract = array('tag', 'lb_name', 'name', 'bookmark_id', 'Access_cdate', 'Access_total', 'nfc', 'qr', 'Link_update', 'user_name');
        $this->viewClass = 'CsvView.Csv';
        $this->set(compact('datas', '_serialize', '_bom', '_header', '_extract'));
    }

    /**
     * For system_view
     * Update selected plate record
     */
    public function system_edit($id)
    {
        $this->edit($id);
        $this->render('edit');
    }

    /**
     * Update selected plate record
     */
    public function edit($id)
    {
        $this->_setModalLayout();
        if (!$id) {
            $this->Session->setFlash(__('プレートのIDを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('controller' => 'tags', 'action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('controller' => 'tags', 'action' => 'index'));
        }
        $tags = $this->Tag->findById($id);
        if (!$tags) {
            $this->Session->setFlash(__('プレートのIDは無効です。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('controller' => 'tags', 'action' => 'index'));
        }
        // Get Bookmark Content
        $bookmarkID = $tags['Tag']['bookmark_id'];
        $bookmarkContents = array();
        if ($bookmarkID) {
            $bookmarkContents = $this->Link->find('all', array(
                'conditions' => array(
                    'bookmark_id' => $bookmarkID
                ),
                'fields' => array('type', 'sub_type', 'url', 'icon', 'link_text')
            ));
            $bookmarkContents = $this->Link->removeArrayWrapper('Link', $bookmarkContents);
        }
        // Get list Url and Type
        $list_url = $this->Link->find('all', array(
            'conditions' => array('tag_id' => $id),
            'fields' => array('url', 'sub_type', 'cdate')
        ));
        //Get List Label Hierachi
        $labels = $this->Label->getLabelsArray('TagModel');
        $list_lb = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        // Get Plate's label
        $type = 'TagModel';
        $label = $this->Label->label_id_Query($id, $type);
        $this->set(array(
            'id' => $id,
            'bookmarkContents' => $bookmarkContents,
            'list_lb' => $list_lb,
            'cr_label' => $label,
            'tag' => $tags,
            'list_url' => $list_url
        ));

        if ($this->request->is('post') || $this->request->is('put')) {
            $this->Tag->id = $id;

            // Add Label
            if (isset($this->request->data['Label'])) {
                $rq_label = $this->request->data['Label'];
                $rq_label['type'] = 'TagModel';
                $rq_label['team_id'] = Configure::read('teamId');
                $rq_label['cdate'] = date('Y-m-d H:i:s');
                $existing = $this->Label->find('all', array('conditions' => array(
                        'label' => $rq_label['add_new_text'],
                        'team_id' => $rq_label['team_id'],
                        'type' => $rq_label['type'],
                )));
                if ($rq_label['new_label'] != null) {
                    if ($rq_label['add_new_text'] != null) {
                        $rq_label['parent_id'] = $rq_label['new_label'];
                        $rq_label['label'] = $rq_label['add_new_text'];
                        if (empty($existing)) {
                            if ($this->Label->save($rq_label)) {
                                // Get new ID record
                                $new_id = $this->Label->getLastInsertId();
                            } else {
                                $this->Session->setFlash(__('Can not create new label'), 'alert-box', array('class' => 'alert-danger'));
                                $this->redirect(array('controller' => 'tags', 'action' => 'index'));
                            }
                        } else {
                            $this->Session->setFlash(__('このラベルは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                            return $this->redirect($this->referer());
                        }
                    } else {
                        if (isset($this->request->data['_label'])) {
                            if (in_array($rq_label['new_label'], $this->request->data['_label'])) {
                                $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                                $this->redirect(array('controller' => 'tags', 'action' => 'index'));
                            }
                        }
                        $lb_id = $rq_label['new_label'];
                    }
                } else {
                    if ($rq_label['add_new_text'] != null) {
                        $rq_label['parent_id'] = 0;
                        $rq_label['label'] = $rq_label['add_new_text'];
                        if (empty($existing)) {
                            if ($this->Label->save($rq_label)) {
                                // Get new ID record
                                $new_id = $this->Label->getLastInsertId();
                            } else {
                                $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                                $this->redirect(array('controller' => 'tags', 'action' => 'index'));
                            }
                        } else {
                            $this->Session->setFlash(__('このラベルは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                            return $this->redirect($this->referer());
                        }
                    }
                }
                if ($rq_label['clear'] == 1) {
                    $this->Label->LabelData->deleteAll(array('label_id' => $this->request->data['_label'], 'target_id' => $id), false);
                }
            }

            // Update exited label
            if (isset($this->request->data['_label'])) {
                $own_labels = $this->request->data['_label'];
                if (count($own_labels) != count(array_unique($own_labels))) {
                    $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                    $this->redirect(array('controller' => 'tags', 'action' => 'index'));
                } else {
                    foreach ($own_labels as $key => $value) {
                      if( $value >= 0){
                        $this->Label->LabelData->query("UPDATE label_datas SET label_id = {$value} WHERE target_id = {$id} AND label_id = {$key}");
                      } else {
                        $this->Label->LabelData->query("DELETE FROM label_datas WHERE target_id = {$id} AND label_id = {$key}");
                      }
                    }
                }
            }
            // Check duplicate label_datas
            $check_label_datas = $this->Label->LabelData->find('all', array(
                'conditions' => array('target_id' => $id),
                'fields' => array('label_id')
            ));

            $ids = array();

            foreach ($check_label_datas as $label_datas_id) {
                $lb_data_id = $label_datas_id['LabelData']['label_id'];
                $ids[] = $lb_data_id;
            }

            // Save plate record
            if (isset($this->request->data['Tag'])) {
                $rq_data = $this->request->data['Tag'];
                /* Upload image */
                if (is_uploaded_file($rq_data['icon']['tmp_name'])) {
                    $ImageModel = new Image();
                    $ImageModel->target_folder = 'plate';
                    $rq_data['icon'] = $ImageModel->saveImage($this->Auth->user('team_id'), $rq_data['icon']);
                } else {
                    $rq_data['icon'] = $tags['Tag']['icon'];
                }
                $rq_data['cdate'] = date('Y-m-d H:i:s');
                $dateTime = date('Y-m-d H:i:s');
                if ($this->Tag->save($rq_data)) {
                    /* Update bookmark record in links table */
                    $this->Link->updateAll(array('udate' => "'$dateTime'"), array('bookmark_id' => $rq_data['bookmark_id']));
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
                        if ($rq_label['add_new_text'] != null) {
                            $this->request->data['LabelDatas']['label_id'] = $new_id;
                            if (!in_array($this->request->data['LabelDatas']['label_id'], $ids)) {
                                $this->Label->LabelData->save($this->request->data['LabelDatas']);
                            }
                        }
                    }
                }
				// clear links data by tag. geo 
                if( $bookmarkID != $rq_data['bookmark_id'] ){
                    $this->Link->DeleteByTagID($tags['Tag']['id']);
                }
                /* End save */
                $this->Session->setFlash(__('プレートのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                 $filter_url = $this->Session->read('Access.filter.tag');
                 if( empty($filter_url) ){
                    $this->redirect(array('controller' => 'tags', 'action' => 'index'));
                 }else{
                    $this->redirect($filter_url);
                 }
            } else {
                $this->Session->setFlash(__('プレートをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
            }
        }

        if (!$this->request->data) {
            $this->request->data = $tags;
        }
    }

    /**
     * For system view
     * Display selected plate's detail
     */
    public function system_detail($id)
    {
        $this->detail($id);
        $this->render('detail');
    }

    /**
     * Display selected plate's detail
     */
    public function detail($id)
    {
        if (!$id) {
            $this->Session->setFlash(__('プレートのIDを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('controller' => 'tags', 'action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('controller' => 'tags', 'action' => 'index'));
        }
        // Get Team name
        $this->loadModel('Team');
        $tags = $this->Tag->findById($id);
        if (!$tags) {
            $this->Session->setFlash(__('プレートのIDは無効です。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect($this->referer());
        }
        //Write session to display in Top screen
        $this->Session->write('Access.tag', $tags['Tag']['tag']);
        $this->Session->write('Access.screen', 'plate');
        $team_id = $tags['Tag']['team_id'];
        $team_name = $this->Team->find('first', array(
            'conditions' => array('Team.id' => $team_id),
            'fields' => array('Team.name')
        ));
        // Get Bookmark Content
        $bookmarkID = $tags['Tag']['bookmark_id'];
        $bookmarkContents = array();
        if ($bookmarkID) {
            $bookmarkContents = $this->Link->find('all', array(
                'conditions' => array(
                    'bookmark_id' => $bookmarkID
                ),
                'fields' => array('type', 'sub_type', 'url')
            ));
            $bookmarkContents = $this->Link->removeArrayWrapper('Link', $bookmarkContents);
        }
        // Get list Url and Type
        $list_url = $this->Link->find('all', array(
            'conditions' => array('tag_id' => $id),
            'fields' => array('url', 'sub_type')
        ));
        // Get Plate's label
        $type = 'TagModel';
        $label = $this->Label->label_Query($id, $type);
        $label = join(', ', $label);

        if (isset($this->params['requested'])) {
            return array(
                'tags' => $tags,
                'team_name' => $team_name,
                'bookmarkContents' => $bookmarkContents,
                'list_url' => $list_url,
                'labels' => $label
            );
        } else {
            $this->_setModalLayout();
            $this->set(array(
                'tags' => $tags,
                'team_name' => $team_name,
                'bookmarkContents' => $bookmarkContents,
                'list_url' => $list_url,
                'labels' => $label
            ));
        }
    }

    /**
     * For system_view
     * Delete plate
     *
     */
    public function system_delete()
    {
        $this->delete();
    }

    /**
     * Delete plate
     *
     */
    public function delete()
    {
        echo json_encode(1);
        exit;
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        $rq_data = $this->request->data;
        $ids = $rq_data['id'];
        $concat = 'CONCAT(p_head, ".", p_lot, ".", p_num)';
        foreach ($ids as $key => $value) {
            // Delete Access Log
            $tag_series = $this->Tag->find('first', array(
                'conditions' => array('id' => $value),
                'fields' => array('tag')
            ));
            $this->AccessLog->deleteAll(array($concat => $tag_series['Tag']['tag']), false);
            $lb_id = $this->Label->LabelData->find('all', array(
                'conditions' => array(
                    'Label.type' => 'TagModel',
                    'LabelData.target_id' => $value
                ),
                'fields' => array('Label.id')
            ));
            $lb_id = $this->Label->removeArrayWrapper('Label', $lb_id, 'id');
            foreach ($lb_id as $label) {
                $this->Label->LabelData->deleteAll(array('target_id' => $value, 'label_id' => $label), false);
            }
        }

        if ($this->Tag->delete($ids)) {
            $this->Session->setFlash(__('プレートが削除されました。'), 'alert-box', array('class' => 'alert-success'));
        } else {
            $this->Session->setFlash(__('プレートを削除することはできません。'), 'alert-box', array('class' => 'alert-danger'));
        }
        echo json_encode(1);
        exit;
    }

    /**
     * For system view
     * Update plate record by ajax
     */
    public function system_quickEdit()
    {
        $this->quickEdit();
    }

    /**
     * Update plate record by ajax
     */
    public function quickEdit()
    {
        if ($this->request->is('post')) {
            $rq_data = $this->request->data;
            $type = $rq_data['type'];
            $input = (isset($rq_data['input'])) ? $rq_data['input'] : '';
            $selectall = $rq_data['selectall'];
            $ids = array();
            if(isset($selectall) && $selectall == '1') {
                $tagData = $this->Session->read("tag");
                if (isset($tagData)) {
                    foreach ($this->Session->read("tag") as $key => $value) {
                        array_push($ids, $value['Tag']['id']);
                    }
                }
            } else{
                $ids = $rq_data['id'];
            }
            if ($type == 2) {
                $now = date('Y-m-d H:i:s');
                $this->Tag->updateAll(array('name' => "'$input'", 'cdate' => "'$now'"), array('id' => $ids));
            } elseif ($type == 3) {
                $this->Tag->save(array('id' => $ids, 'kind' => $input));
            } elseif ($type == 5) {
                $this->Tag->save(array('id' => $ids, 'code' => $input));
            } elseif ($type == 6) {
                foreach ($ids as $v_id) {
                    $available = $this->Tag->find('first', array(
                        'conditions' => array('id' => $v_id),
                        'fields' => array('available')
                    ));
                    if ($available['Tag']['available'] == 1) {
                        $status = 0;
                    } else {
                        $status = 1;
                    }
                    $this->Tag->save(array('id' => $v_id, 'available' => $status));
                }
            } elseif ($type == 8) {
                $system = $rq_data['system'];
                $authority = $this->Auth->user('authority');
                foreach ($ids as $v_id) {
                    $tag = $this->Tag->findById($v_id);
                    $orderID = $tag['Tag']['order_status_id'];
                    $this->Link->DeleteByTagID($tag['Tag']['id']);
                    if ($system == 1) {
                        $this->Tag->save(array('id' => $v_id, 'management_id' => $input, 'team_id' => 0, 'bookmark_id' => 0));
                        //Update order_status
                        if( !empty($orderID))
                          $this->Order->save(array('id' => $orderID, 'status' => 5));
                    } elseif ( $authority == 1 ){
                        $this->Tag->save(array('id' => $v_id, 'management_id' => $input, 'team_id' => 0, 'bookmark_id' => 0));
                        //Update order_status
                        if( !empty($orderID))
                          $this->Order->save(array('id' => $orderID, 'status' => 5));
                    } elseif ( $authority == 2 ){
                        $this->Tag->save(array('id' => $v_id, 'team_id' => $input, 'bookmark_id' => 0));
                        //Update order_status
                        if( !empty($orderID))
                          $this->Order->save(array('id' => $orderID, 'status' => 99));
                    }
                }
            }

            $this->Session->setFlash(__('プレートのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));

            echo json_encode(1);
            exit;
        }
    }

    /**
     * For system view
     * Update plate's label record by ajax
     */
    public function system_quickedit_label()
    {
        $this->quickedit_label();
    }

    /**
     * Update plate's label record by ajax
     */
    public function quickedit_label()
    {
        $this->_setModalLayout();
        if (isset($_REQUEST['id']) || isset($_REQUEST['selectall'])) {
            $target_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            $selectall = isset($_REQUEST['selectall']) ? $_REQUEST['selectall'] : '0';
            //Get List Label Hierachi
            $labels = $this->Label->getLabelsArray('TagModel');
            $listLabelHierachy = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

            $this->set(array(
                'labels' => $listLabelHierachy,
                'target_id' => $target_id,
                'selectall' => $selectall
            ));
            return $this->render('quickedit_label', 'ajax');
        }
        if (isset($this->request->data['Tag'])) {
            $rq_label = $this->request->data['Tag'];
            $label_ids = explode(',', $rq_label['label_id']);
            $target_ids = array();
            $selectall = $this->request->data['Tag']['selectall'];
            if($selectall == '1') {
                $tagData = $this->Session->read("tag");
                if (isset($tagData)) {
                    foreach ($this->Session->read("tag") as $key => $value) {
                        array_push($target_ids, $value['Tag']['id']);
                    }
                }
            } else {
                $target_ids = explode(',', $this->request->data['Tag']['target_id']);
            }

            $old_label = $this->Label->label_id_Query($target_ids, 'TagModel');
            // Delete old records
            $this->Label->LabelData->deleteAll(array('target_id' => $target_ids, 'label_id' => $old_label), false);

            // Insert new records
            foreach ($label_ids as $label_id) {
                foreach ($target_ids as $target_id) {
                    $this->Label->LabelData->save(array('label_id' => $label_id, 'target_id' => $target_id, 'cdate' => date('Y-m-d H:i:s')));
                }
            }
            $this->Session->setFlash(__('プレートのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
        }

        $this->redirect(array('controller' => 'tags', 'action' => 'index'));
    }

    /**
     * For system_view
     * Update plate's bookmark record by ajax
     */
    public function system_quickedit_link_bm()
    {
        $this->quickedit_link_bm();
    }

    /**
     * Update plate's bookmark record by ajax
     */
    public function quickedit_link_bm()
    {
        $this->_setModalLayout();
        if (isset($_REQUEST['id']) || isset($_REQUEST['selectall'])) {
            $this->loadModel('Bookmark');
            $this->loadModel('Master');

            $tag_ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            $selectall = isset($_REQUEST['selectall']) ? $_REQUEST['selectall'] : '0';
            // List all bookmark content
            $teamID = $this->getTeamid($this->Auth->user('authority'));
            $condition = array();
            if ($this->request->prefix != 'system') {
                $condition += array(
                    'team_id' => $teamID
                );
            }
            $list_bms = $this->Bookmark->find('all', array(
                'conditions' => $condition,
                'order' => array('id' => 'asc'),
                'fields' => array('id', 'name', 'kind')
            ));
            $list_bms = $this->Bookmark->removeArrayWrapper('Bookmark', $list_bms);
            foreach ($list_bms as &$list_bm) {
                $type = 'BookmarkModel';
                $list_bm['label_id'] = implode(',', $this->Label->label_id_Query($list_bm['id'], $type));
                $link = $this->Link->find('all', array(
                    'conditions' => array('bookmark_id' => $list_bm['id']),
                    'fields' => 'url'
                ));
                $link = $this->Link->removeArrayWrapper('Link', $link);
                $list_bm['Link:url'] = ($link) ? $link : '';
                $linkH = '';
                if ($link) {
                    foreach ($link as $linkHidden) {
                        $linkH .= $linkHidden['url'] . ',';
                    }
                }

                $list_bm['Link:hidden'] = $linkH;
            }

            //List Label
            $labels = $this->Label->getLabelsArray('BookmarkModel');
            $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

            //List Type
            $type = $this->Master->find('list', array(
                'fields' => array('id', 'bookmark_type')
            ));
            $status = (isset($_REQUEST['edit'])) ? 1 : 0;
            $this->set(array(
                'status' => $status,
                'tag_ids' => $tag_ids,
                'list_bms' => $list_bms,
                'list_lb' => $labels,
                'type' => $type,
                'selectall' => $selectall
            ));

            return $this->render('quickedit_link_bm', 'ajax');
        }

        if (isset($this->request->data['Tag'])) {
            $ids = array();
            $selectall = $this->request->data['Tag']['selectall'];
            if($selectall == '1') {
                $tagData = $this->Session->read("tag");
                if (isset($tagData)) {
                    foreach ($this->Session->read("tag") as $key => $value) {
                        array_push($ids, $value['Tag']['id']);
                    }
                }
            } else {
                $ids = explode(',', $this->request->data['Tag']['target_id']);
            }
            $rq_bm = $this->request->data['Tag']['bm_id'];
            $now = date('Y-m-d H:i:s');
            // Update tag table
            if ($this->Tag->updateAll(array('bookmark_id' => $rq_bm, 'cdate' => "'$now'"), array('id' => $ids)))
                $this->Session->setFlash(__('プレートのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
            //Update links table
            $this->Link->updateAll(array('udate' => "'$now'"), array('bookmark_id' => $rq_bm));
        }
        $this->redirect(array('controller' => 'tags', 'action' => 'index'));
    }

    /**
     * For system view
     * Display Plate's label
     */
    public function system_label()
    {
        $this->label();
    }

    // Export CSV

    /**
     * Display Plate's label
     */
    public function label()
    {

        $input = $this->request->data;

        if ($this->request->is('post')) {
            $options = array(
                'order' => array('display_order' => 'asc'),
                'conditions' => array('type' => 'TagModel')
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
            $labels = $this->Label->getLabelsArray('TagModel');
        }

        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        $labelscount = $this->Label->query('
            SELECT label_id, count(*) AS total
            FROM label_datas
            WHERE label_id in (SELECT id FROM label WHERE type = \'TagModel\')
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
            'type' => 'TagModel',
        ));

        return $this->render('/Labels/index');
    }
}
?>
