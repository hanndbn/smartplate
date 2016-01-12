<?php

App::uses('AppController', 'Controller');
App::uses('Image', 'Model');
App::uses('Tag', 'Model');

/**
 * Controller for Bookmark model
 *
 * @package       app.Controller
 *
 */
class BookmarksController extends AppController
{

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Master');
        $this->loadModel('Label');
        $this->loadModel('AccessLog');
        $this->loadModel('Link');
        $this->loadModel('Management');
		$this->loadModel ( 'BookmarkExtData' );
//        $this->Auth->deny();
    }

    /**
     * Display Bookmark's label
     */
    public function label()
    {
        $input = $this->request->data;
        if ($this->request->is('post')) {
            $options = array(
                'order' => array('display_order' => 'asc'),
                'conditions' => array(
                    'type' => 'BookmarkModel',
//                    'team_id' => Configure::read('teamId')
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
            $labels = $this->Label->getLabelsArray('BookmarkModel');
        }

        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        $labelscount = $this->Label->query('
            SELECT label_id, count(*) AS total 
            FROM label_datas 
            WHERE label_id in (SELECT id FROM label WHERE type = \'BookmarkModel\') 
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
            'type' => 'BookmarkModel',
        ));

        return $this->render('/Labels/index');
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

    public function system_index()
    {
        $this->index();
    }
    /**
     * Show list bookmark user information
     */
    public function index() {
       $this->loadModel('Tag');
        /* Add filter */
        //List Label
        $labels = $this->Label->getLabelsArray('BookmarkModel');
        $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

        //List Type
        $type = $this->Master->find('list', array(
            'fields' => array('id', 'bookmark_type')
        ));
        $this->set(array(
            'list_lb' => $labels,
            'type' => $type
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
            $this->Session->write('Access.filter', $filter_url);
            return $this->redirect($filter_url);
        } else {
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
                            array('Bookmark.name LIKE' => '%' . str_replace('%', '\%', $value) . '%')
                        );
                    }  elseif($param_name = "filter"){
                        if($value == 'filter'){
                            $flgFilter = true;
                        }
                        else if($value == 'filterFollowDate')
                            $flgFilterFollowDate = true;
                    } elseif ($param_name == "kind") {
                        $conditions += array('Bookmark.kind' => $value);
                    } else {
                        $joins = array(
                            array(
                                'table' => 'label_datas',
                                'alias' => 'lbd',
                                'type' => 'INNER',
                                'conditions' => array('Bookmark.id = lbd.target_id')
                            )
                        );
                        $conditions += array('lbd.label_id' => $value);
                    }
                    $this->request->data['Filter'][$param_name] = $value;
                    $need_clear_fileter = false;
                }
            }
            if( $need_clear_fileter ){
              if( $this->Session->read('Access.filter') == ''){
                $this->Session->write('Access.filter', '');
              }
            }
        }
        $this->Bookmark->recursive = 0;
        if($this->request->prefix != 'system'){
            if ($this->Auth->user('authority') == '2' || $this->Auth->user('authority') == '1') {
                $listUsers = $this->Management->getChildID($this->Auth->user('id'));
                $team_id = $this->Management->getUserTeamID($listUsers);
            } else {
                $team_id = $this->Auth->user('team_id');
            }
            $conditions += array('Bookmark.team_id' => $team_id);
        }
        $this->paginate = array(
            'limit' => 20,
            'order' => array('id' => 'desc'),
            'joins' => $joins,
            'conditions' => $conditions
        );
        /* end filter */
        if (isset($this->params['named']['act']) && $this->params['named']['act'] == 'export') {
            $bookmarksExport = $this->Bookmark->find('all', array(
                'order' => array('id' => 'asc'),
                'joins' => $joins,
                'conditions' => $conditions
            ));
            $this->export($bookmarksExport, $flgFilterFollowDate, $dateFrom, $dateTo);
        }
        $bookmark = $this->paginate('Bookmark');
        if (count($bookmark) > 0) {

            $query_date = date('Y-m-d H:i:s');
            // First day of the month.
            $fday = date('Y-m-01 H:i:s', strtotime($query_date));
            // Last day of the month.
            $lday = date('Y-m-t H:i:s', strtotime($query_date));
            foreach ($bookmark as &$bm_id) {
                $id = $bm_id['Bookmark']['id'];
                $kind = $bm_id['Bookmark']['kind'];
                /* Get bookmark's label */
                $type = 'BookmarkModel';
                $bm_id['lb_name'] = $this->Label->label_Query($id, $type);
                /* Get Bookmark's type */
                $bm_type = $this->Master->find('first', array(
                    'conditions' => array('Master.id' => "{$kind}"),
                    'fields' => array('Master.id, Master.bookmark_type')
                ));
                if (isset($bm_type['Master'])) {
                    $bm_id['Type'] = $bm_type['Master'];
                } else {
                    $bm_id['Type'] = array('id' => 0, 'bookmark_type' => "");
                }
                /* Get number link */
                /* edit by geo
                  $link = $this->Link->find('count', array(
                    'conditions' => array('Link.bookmark_id' => "{$id}"),
                    ));
                */
                  $link = $this->Tag->find('count', array(
                    'conditions' => array('Tag.bookmark_id' => "{$id}"),
                    ));
                $bm_id['Link'] = $link;
                $a_type = array();
                if($flgFilterFollowDate){
                    /* Get number access_log */
                    $a_type = $this->AccessLog->find('count', array(
                        'conditions' => array(
                            'AccessLog.bookmark_id' => "{$id}",
                            'AccessLog.created_at BETWEEN ? AND ?' => array($dateFrom, $dateTo)
                        ),
                        'fields' => array('AccessLog.p_type')
                    ));
                } else {
                    /* Get number access_log */
                    $a_type = $this->AccessLog->find('count', array(
                        'conditions' => array(
                            'AccessLog.bookmark_id' => "{$id}",
                            'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday)
                        ),
                        'fields' => array('AccessLog.p_type')
                    ));
                }
                $bm_id['Access'] = $a_type;
            };
        }
        $this->set('bookmarks', $bookmark);
        $this->render('index');

        // save to seesion
        $bookmarkSession = $this->Bookmark->find('all', array(
            'order' => array('id' => 'asc'),
            'joins' => $joins,
            'conditions' => $conditions
        ));
        $this->Session->write("Bookmark", $bookmarkSession);
    }

    public function export($bookmarks, $flgFilterFollowDate, $dateFrom, $dateTo)
    {

        $this->response->download("contents.csv");

        if (count($bookmarks) > 0) {

            $query_date = date('Y-m-d H:i:s');
            // First day of the month.
            $fday = date('Y-m-01 H:i:s', strtotime($query_date));
            // Last day of the month.
            $lday = date('Y-m-t H:i:s', strtotime($query_date));
            foreach ($bookmarks as &$bm_id) {
                $id = $bm_id['Bookmark']['id'];
                $kind = $bm_id['Bookmark']['kind'];
                /* Get bookmark's label */
                $type = 'BookmarkModel';
                $bm_id['Bookmark']['lb_name'] = implode(', ', $this->Label->label_Query($id, $type));

                /* Get Bookmark's type */
                $bm_type = $this->Master->find('first', array(
                    'conditions' => array('Master.id' => "{$kind}"),
                    'fields' => array('Master.id, Master.bookmark_type')
                ));
                if (isset($bm_type['Master'])) {
                    $bm_id['Bookmark']['Type'] = $bm_type['Master'];
                } else {
                    $bm_id['Bookmark']['Type'] = array('id' => 0, 'bookmark_type' => "");
                }
                $bm_id['Bookmark']['Type'] = $bm_id['Bookmark']['Type']['bookmark_type'];
                /* Get number link */
                $link = $this->Link->find('count', array(
                    'conditions' => array('Link.bookmark_id' => "{$id}")
                ));

                $bm_id['Bookmark']['Link'] = $link;

                $a_type = array();
                if($flgFilterFollowDate){
                    /* Get number access_log */
                    $a_type = $this->AccessLog->find('count', array(
                        'conditions' => array(
                            'AccessLog.bookmark_id' => "{$id}",
                            'AccessLog.created_at BETWEEN ? AND ?' => array($dateFrom, $dateTo)
                        ),
                        'fields' => array('AccessLog.p_type')
                    ));
                }else {
                    /* Get number access_log */
                    $a_type = $this->AccessLog->find('count', array(
                        'conditions' => array(
                            'AccessLog.bookmark_id' => "{$id}",
                            'AccessLog.created_at BETWEEN ? AND ?' => array($fday, $lday)
                        ),
                        'fields' => array('AccessLog.p_type')
                    ));
                }
                $bm_id['Bookmark']['Access'] = $a_type;
            };
        }


        $datas = $this->Bookmark->removeArrayWrapper('Bookmark', $bookmarks);
        $_serialize = 'datas';
        $_bom = false;
        $_header = array('ID', '種別', 'ラベル', '名前', 'バーコード', '更新日時', '配信プレート数', '当月アクセス数');
        $_extract = array('id', 'Type', 'lb_name', 'name', 'code', 'cdate', 'Link', 'Access');
        $this->viewClass = 'CsvView.Csv';
        $this->set(compact('datas', '_serialize', '_bom', '_header', '_extract'));
    }

    /**
     * Add new bookmark record
     */
    public function add()
    {
        $this->_setModalLayout();

        //List Label
        $list_lb = $this->Label->getLabelsArray('BookmarkModel');
        $list_lb = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($list_lb));

        /* Get list sample type */
        $sp_type = $this->Master->find('list', array(
            'fields' => array('id', 'bookmark_type')
        ));
        /* Get list icon */
        $imgPath = IMAGES . 'icon' . DS . '*.png';
        $listIcon = $this->getListIcon($imgPath);
        $this->set(array(
            'list_lb' => $list_lb,
            'sp_type' => $sp_type,
            'listIcon' => $listIcon
        ));

        if ($this->request->is('post') || $this->request->is('put')) {
            // Add Label
            if (isset($this->request->data['Label'])) {
                $rq_label = $this->request->data['Label'];
                $rq_label['type'] = 'BookmarkModel';
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
            if (isset($this->request->data['Bookmark'])) {
                $rq_data = $this->request->data['Bookmark'];

                if(!empty($rq_data['icon'])){
                    $rq_data['image'] = $rq_data['icon'];
                } else {
                    // Upload image
                    if (is_uploaded_file($rq_data['image']['tmp_name'])) {
                        $ImageModel = new Image();
                        $ImageModel->target_folder = 'bookmark';
                        $rq_data['image'] = $ImageModel->saveImage($this->Auth->user('team_id'), $rq_data['image']);
                    } else {
                        $rq_data['image'] = '';
                    }
                }
                unset($rq_data["icon"]);
                $rq_data = array_filter($rq_data);
                $rq_data['team_id'] = $this->Auth->user('team_id');
                $rq_data['cdate'] = date('Y-m-d H:i:s');
                if ($this->Bookmark->save($rq_data)) {
                    $now = date('Y-m-d H:i:s');
                    // Get last ID record
                    $new_bm_id = $this->Bookmark->getLastInsertId();
					// Update bookmark ext data
					if (isset ( $this->request->data ['BookmarkExtData'] )) {

                        //set title
                        if(isset($this->request->data ['BookmarkExtData']['title'])){
                            $title = $this->request->data ['BookmarkExtData']['title'];
                                $this->BookmarkExtData->create();
                                $this->BookmarkExtData->save(array(
                                    'bookmark_id' => $new_bm_id,
                                    'kind' => BookmarkExtData::EXT_TITLE,
                                    'ext_data' => $title
                                ));
                        }

                        /* Upload image */
                        if(isset($this->request->data['BookmarkExtData']['title_header_image']['tmp_name'])) {
                            if (is_uploaded_file($this->request->data['BookmarkExtData']['title_header_image']['tmp_name'])) {
                                $ImageModel = new Image();
                                $ImageModel->target_folder = 'bookmark';
                                $this->request->data['BookmarkExtData']['title_header_image'] = $ImageModel->saveImage('tiles', $this->request->data['BookmarkExtData']['title_header_image']);
                            } else {
                                if (empty($this->request->data['BookmarkExtData']['image_header_deleted']) && isset($bookmarkExtData['title_header_image'])) {
                                    $this->request->data['BookmarkExtData']['title_header_image'] = $bookmarkExtData['title_header_image'];
                                } else {
                                    $this->request->data['BookmarkExtData']['title_header_image'] = "";
                                }
                            }
                            $this->BookmarkExtData->create();
                            $this->BookmarkExtData->save(array(
                                'bookmark_id' => $new_bm_id,
                                'kind' => BookmarkExtData::EXT_TITLE_HEADER_IMAGE,
                                'ext_data' => $this->request->data ['BookmarkExtData']['title_header_image']
                            ));
                        }
					}
                    /* Add new record to links table */
                    if (isset($this->request->data['Link'])) {
                        $link = $this->request->data['Link'];
                        $udate = date('Y-m-d H:i:s');
                        $redirectType = $link['type'];
                        $clearLink = $link['clear'];
                        // Insert new record
                        if ($clearLink == 0) {
                            switch ($redirectType) {
                                case 'normal':
                                    if (isset($link['url'])) {
                                        $url = $link['url'];
                                        $this->Link->save(
                                            array(
                                                'bookmark_id' => $new_bm_id,
                                                'tag_id' => '',
                                                'type' => 0,
                                                'sub_type' => 0,
                                                'url' => "{$url}",
                                                'user_id' => $this->Auth->user('id'),
                                                'cdate' => "$udate"
                                            ));
                                    }
                                    break;
                                case 'os':
                                    if (isset($link['OS'])) {
                                        foreach ($link['OS'] as $key => $url) {
                                            $this->Link->save(
                                                array(
                                                    'bookmark_id' => $new_bm_id,
                                                    'tag_id' => '',
                                                    'type' => 1,
                                                    'sub_type' => $key,
                                                    'url' => "{$url}",
                                                    'user_id' => $this->Auth->user('id'),
                                                    'cdate' => "$udate"
                                                ));
                                        }
                                    }
                                    break;
                                case 'button':
                                    if (isset($link['Btn'])) {
                                        foreach ($link['Btn'] as $key => $val) {
                                            if (empty($val['url'])) continue;
                                            $icon = 0;
                                            $image = '';
                                            if(!empty($val['icon'])){
                                                $icon = pathinfo($val['icon'], PATHINFO_FILENAME);
                                                if (empty($icon)) $icon = 0;
                                            } elseif(!empty($val['image']['tmp_name'])) {
                                                /* Upload image */
                                                if (is_uploaded_file($val['image']['tmp_name'])) {
                                                    $ImageModel = new Image();
                                                    $ImageModel->target_folder = 'bookmark';
                                                    $image = $ImageModel->saveImage($this->Auth->user('team_id'), $val['image']);
                                                }
                                            }
                                            $this->Link->save(
                                                array(
                                                    'bookmark_id' => $new_bm_id,
                                                    'tag_id' => '',
                                                    'type' => 2,
                                                    'sub_type' => $key,
                                                    'url' => $val['url'],
                                                    'link_text' => $val['title'],
                                                    'icon' => $icon,
                                                    'user_id' => $this->Auth->user('id'),
                                                    'cdate' => "$udate",
                                                    'image' => $image
                                                ));
                                        }
                                    }
                                    break;
                                case 'random':
                                case 'rotate':
                                    if ($redirectType == 'random') {
                                        $type = Bookmark::TYPE_RANDOM;
                                    } else {
                                        $type = Bookmark::TYPE_ROTATE;
                                    }
                                    if (isset($link[$redirectType])) {
                                        foreach ($link[$redirectType] as $key => $val) {
                                            if (!empty($val['url']) || (!empty($val['start_date']) && !empty($val['end_date']))) {
                                                $this->Link->save(
                                                    array(
                                                        'bookmark_id' => $new_bm_id,
                                                        'tag_id' => 0,
                                                        'type' => $type,
                                                        'sub_type' => $key,
                                                        'url' => $val['url'],
                                                        'user_id' => $this->Auth->user('id'),
                                                        'cdate' => "$udate",
                                                        'start_date' => $val['start_date'],
                                                        'end_date' => $val['end_date']
                                                    ));
                                            }
                                        }
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                    /* Save record to label_datas table */
                    $this->request->data['LabelDatas']['target_id'] = $new_bm_id;
                    $this->request->data['LabelDatas']['cdate'] = $now;
                    if ($rq_label['new_label'] != null) {
                        if (isset($rq_label['add_new_text']) && $rq_label['add_new_text'] != null) {
                            $this->request->data['LabelDatas']['label_id'] = $new_id;
                        } else {
                            $this->request->data['LabelDatas']['label_id'] = $lb_id;
                        }
                        $this->Label->LabelData->save($this->request->data['LabelDatas']);
                    } else {
                        if (isset($rq_label['add_new_text']) && $rq_label['add_new_text'] != null) {
                            $this->request->data['LabelDatas']['label_id'] = $new_id;
                            $this->Label->LabelData->save($this->request->data['LabelDatas']);
                        }
                    }
                    /* End save */
                    $this->Session->setFlash(__('コンテンツのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                    $this->redirect(array('action' => 'index'));
                } else {
                    $this->log("validationErrors=" . var_export($this->Bookmark->validationErrors, true));
                    $this->Session->setFlash(__('新規コンテンツを登録することはできません。'), 'alert-box', array('class' => 'alert-danger'));
                    $this->redirect(array('action' => 'index'));
                }
            }
        }
        $this->render('edit');
    }

    private function getListIcon($imgPath)
    {
        $listIcon = array();
        foreach (glob($imgPath) as $filename) {
            $imgName = basename($filename);
            if (preg_match('/^[0-9]*\.(png)$/i', $imgName)) {
                $ind = basename($imgName,'.png');
                $ind = str_pad($ind, 5, "0", STR_PAD_LEFT);
                $listIcon[$ind] = $imgName;
            }
        }
        ksort($listIcon);
        return $listIcon;
    }

    /**
     * Update bookmark record
     *
     * @param int $id user id
     */
    public function edit($id)
    {
        $this->_setModalLayout();
        if (!$id) {
            $this->Session->setFlash(__('コンテンツのIDを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }


        // get data from table BookmarkExtData
        $bookmarkExtDataDto = $this->BookmarkExtData->find ( "all", array (
            'fields' => array (
                'ext_data', 'kind'
            ),
            'conditions' => array (
                'bookmark_id' => "{$id}",
            )
        ) );
        $bookmarkExtData = null;
        foreach($bookmarkExtDataDto as $key => $value){
            if($value['BookmarkExtData']['kind'] == BookmarkExtData::EXT_TITLE){
                $bookmarkExtData['title'] = $value['BookmarkExtData']['ext_data'];
            }
            if($value['BookmarkExtData']['kind'] == BookmarkExtData::EXT_TITLE_HEADER_IMAGE){
                $bookmarkExtData['title_header_image'] = $value['BookmarkExtData']['ext_data'];
            }
        }

        /* Get link type */
        $linkType0 = $this->Link->find('first', array(
            'conditions' => array(
                'bookmark_id' => $id,
                'type' => 0
            ),
            'fields' => array('url', 'type', 'sub_type')
        ));
        $linkType1_data = $this->Link->find('all', array(
            'conditions' => array(
                'bookmark_id' => $id,
                'type' => 1
            ),
            'fields' => array('url', 'type', 'sub_type')
        ));
        $linkType1 = array();
        foreach ($linkType1_data as $key => $value) {
            $linkType1[$value['Link']['sub_type']]['Link']['url'] = $value['Link']['url'];
        }
        $linkType2 = $this->Link->find('all', array(
            'conditions' => array(
                'bookmark_id' => $id,
                'type' => 2
            ),
            'fields' => array('url', 'type', 'sub_type', 'link_text', 'icon', 'image')
        ));
        $linkType3 = $this->Link->find('all', array(
            'conditions' => array(
                'bookmark_id' => $id,
                'type' => Bookmark::TYPE_RANDOM
            ),
            'fields' => array('url', 'type', 'sub_type')
        ));
        $linkType4 = $this->Link->find('all', array(
            'conditions' => array(
                'bookmark_id' => $id,
                'type' => Bookmark::TYPE_ROTATE
            ),
            'fields' => array('url', 'type', 'sub_type', 'start_date', 'end_date')
        ));
        //List Label
        $list_lb = $this->Label->getLabelsArray('BookmarkModel');
        $list_lb = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($list_lb));

        /* Get bookmark's label ids */
        $type = 'BookmarkModel';
        $label = $this->Label->label_id_Query($id, $type);

        /* Get current bookmark's type */
        $type = $this->Bookmark->find('first', array(
            'conditions' => array('Bookmark.id' => "{$id}"),
            'fields' => array('kind')
        ));
        $cr_type = $type['Bookmark']['kind'];

        $bm_type = $this->Master->find('all', array(
            'conditions' => array('Master.id' => "{$cr_type}"),
        ));
        /* Get list sample type */
        $sp_type = $this->Master->find('list', array(
            'fields' => array('id', 'bookmark_type')
        ));

        /* Get current Link type */
        $crLinkType = $this->Link->find("first", array(
            'conditions' => array(
                'bookmark_id' => $id
            ),
        ));
        $linkType = ($crLinkType) ? $crLinkType['Link']['type'] : 0;

        /* Get list icon */
        $imgPath = IMAGES . 'icon' . DS . '*.png';
        $listIcon = $this->getListIcon($imgPath);
        $this->set(array(
            'listIcon' => $listIcon,
            'list_lb' => $list_lb,
            'linkType0' => $linkType0,
            'linkType1' => $linkType1,
            'linkType2' => $linkType2,
            'linkType3' => $linkType3,
            'linkType4' => $linkType4,
            'cr_label' => $label,
            'cr_type' => $cr_type,
            'sp_type' => $sp_type,
            'linkType' => $linkType
        ));


        $bookmark = $this->Bookmark->findById($id);
        if (!$bookmark) {
            $this->Session->setFlash(__('コンテンツのIDは無効です。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->Bookmark->id = $id;
            // Update link url
            if (isset($this->request->data['Link'])) {
                $link = $this->request->data['Link'];
                $udate = date('Y-m-d H:i:s');
                $redirectType = $link['type'];
                $clearLink = $link['clear'];
                // Delete old links record
                $this->Link->deleteAll(array('bookmark_id' => $id), false);
                // Insert new record
                if ($clearLink == 0) {
                    switch ($redirectType) {
                        case 'normal':
                            if (isset($link['url'])) {
                                $url = $link['url'];
                                $this->Link->save(
                                    array(
                                        'bookmark_id' => $id,
                                        'tag_id' => 0,
                                        'type' => 0,
                                        'sub_type' => 0,
                                        'url' => "$url",
                                        'user_id' => $this->Auth->user('id'),
                                        'cdate' => "$udate"
                                    ));
                            }
                            break;
                        case 'os':
                            if (isset($link['OS'])) {
                                foreach ($link['OS'] as $key => $url) {
                                    $this->Link->save(
                                        array(
                                            'bookmark_id' => $id,
                                            'tag_id' => 0,
                                            'type' => 1,
                                            'sub_type' => $key,
                                            'url' => "$url",
                                            'user_id' => $this->Auth->user('id'),
                                            'cdate' => "$udate"
                                        ));
                                }
                            }
                            break;
                        case 'button':
                            if (isset($link['Btn'])) {
                                foreach ($link['Btn'] as $key => $val) {
                                    if (empty($val['url'])) continue;
                                    $icon = 0;
                                    $image = '';
                                    if(!empty($val['icon'])){
                                        $icon = pathinfo($val['icon'], PATHINFO_FILENAME);
                                        if (empty($icon)) $icon = 0;
                                    } elseif(!empty($val['image']['tmp_name'])) {
                                        /* Upload image */
                                        if (is_uploaded_file($val['image']['tmp_name'])) {
                                            $ImageModel = new Image();
                                            $ImageModel->target_folder = 'bookmark';
                                            $image = $ImageModel->saveImage($this->Auth->user('team_id'), $val['image']);
                                        }
                                    }
                                    $this->Link->save(
                                        array(
                                            'bookmark_id' => $id,
                                            'tag_id' => 0,
                                            'type' => 2,
                                            'sub_type' => $key,
                                            'url' => $val['url'],
                                            'link_text' => $val['title'],
                                            'icon' => $icon,
                                            'user_id' => $this->Auth->user('id'),
                                            'cdate' => "$udate",
                                            'image' => $image
                                        ));
                                }
                            }
                            break;
                        case 'random':
                        case 'rotate':
                            if ($redirectType == 'random') {
                                $type = Bookmark::TYPE_RANDOM;
                            } else {
                                $type = Bookmark::TYPE_ROTATE;
                            }
                            if (isset($link[$redirectType])) {
                                foreach ($link[$redirectType] as $key => $val) {
                                    if (!empty($val['url']) || (!empty($val['start_date']) && !empty($val['end_date']))) {
                                        $this->Link->save(
                                            array(
                                                'bookmark_id' => $id,
                                                'tag_id' => 0,
                                                'type' => $type,
                                                'sub_type' => $key,
                                                'url' => $val['url'],
                                                'user_id' => $this->Auth->user('id'),
                                                'cdate' => "$udate",
                                                'start_date' => $val['start_date'],
                                                'end_date' => $val['end_date']
                                            ));
                                    }
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
            // Add Label
            if (isset($this->request->data['Label'])) {
                $rq_label = $this->request->data['Label'];
                $rq_label['type'] = 'BookmarkModel';
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
                        if (isset($this->request->data['_label'])) {
                            if (in_array($rq_label['new_label'], $this->request->data['_label'])) {
                                $this->Session->setFlash(__('ラベルが重複しています。もう一度やり直してください。'), 'alert-box', array('class' => 'alert-danger'));
                                $this->redirect(array('action' => 'index'));
                            }
                        }
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
                            return $this->redirect(array('action' => 'index'));
                        }
                    }
                }
                if ($rq_label['clearLabel'] == 1) {
                    $this->Label->LabelData->deleteAll(array('label_id' => $this->request->data['_label'], 'target_id' => $id), false);
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
                      if( $value >= 0){
                        $this->Label->LabelData->query("UPDATE label_datas SET label_id = {$value} WHERE target_id = {$id} AND label_id = {$key}");
                      } else {
                        $this->Label->LabelData->query("DELETE FROM label_datas WHERE target_id = {$id} AND label_id = {$key}");
                      }
                    }
                }
            }

			// Update bookmark ext data
			if (isset ( $this->request->data ['BookmarkExtData'] )) {
                if(isset($this->request->data ['BookmarkExtData']['title'])){
                    $title = $this->request->data ['BookmarkExtData']['title'];
                    if(isset($bookmarkExtData['title'])){
                        $this->BookmarkExtData->updateAll(array('ext_data' => "'$title'"), array('bookmark_id' => $id, 'BookmarkExtData.kind' => BookmarkExtData::EXT_TITLE));
                    } else{
                        $this->BookmarkExtData->create();
                        $this->BookmarkExtData->save(array(
                            'bookmark_id' => $id,
                            'kind' => BookmarkExtData::EXT_TITLE,
                            'ext_data' => $title
                        ));
                    }
                }
                /* Upload image */
                if(isset($this->request->data['BookmarkExtData']['title_header_image']['tmp_name'])) {
                    if (is_uploaded_file($this->request->data['BookmarkExtData']['title_header_image']['tmp_name'])) {
                        $ImageModel = new Image();
                        $ImageModel->target_folder = 'bookmark';
                        $this->request->data['BookmarkExtData']['title_header_image'] = $ImageModel->saveImage('tiles', $this->request->data['BookmarkExtData']['title_header_image']);
                    } else {
                        if (empty($this->request->data['BookmarkExtData']['image_header_deleted']) && isset($bookmarkExtData['title_header_image'])) {
                            $this->request->data['BookmarkExtData']['title_header_image'] = $bookmarkExtData['title_header_image'];
                        } else {
                            $this->request->data['BookmarkExtData']['title_header_image'] = "";
                        }
                    }
                    if(isset($bookmarkExtData['title_header_image'])){
                        $title_header_image = str_replace('\\', '\\\\', $this->request->data ['BookmarkExtData']['title_header_image']);
                        $this->BookmarkExtData->updateAll(array('ext_data' => "'$title_header_image'"), array('bookmark_id' => $id, 'BookmarkExtData.kind' => BookmarkExtData::EXT_TITLE_HEADER_IMAGE));
                    } else{
                        $this->BookmarkExtData->create();
                        $this->BookmarkExtData->save(array(
                            'bookmark_id' => $id,
                            'kind' => BookmarkExtData::EXT_TITLE_HEADER_IMAGE,
                            'ext_data' => $this->request->data ['BookmarkExtData']['title_header_image']
                        ));
                    }
                }


//                if(isset($this->request->data ['BookmarkExtData']['title_header_image'])){
//                    $title_header_image = $this->request->data ['BookmarkExtData']['title_header_image'];
//                    $this->BookmarkExtData->updateAll(array('ext_data' => "'title_header_image'"), array('bookmark_id' => $id, 'BookmarkExtData.kind' => BookmarkExtData::EXT_TITLE_HEADER_IMAGE));
//                }
			}
            if (isset($this->request->data['Bookmark'])) {
                if(!empty($this->request->data['Bookmark']['icon'])){
                    $this->request->data['Bookmark']['image'] = $this->request->data['Bookmark']['icon'];
                } else {
                    /* Upload image */
                    if (is_uploaded_file($this->request->data['Bookmark']['image']['tmp_name'])) {
                        $ImageModel = new Image();
                        $ImageModel->target_folder = 'bookmark';
                        $this->request->data['Bookmark']['image'] = $ImageModel->saveImage($this->Auth->user('team_id'), $this->request->data['Bookmark']['image']);
                    } else {
                        if (empty($this->request->data['Bookmark']['image_deleted'])) {
                            $this->request->data['Bookmark']['image'] = $bookmark['Bookmark']['image'];
                        } else {
                            $this->request->data['Bookmark']['image'] = "";
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
                $rqBoomarkData = $this->request->data['Bookmark'];
                $rqBoomarkData['team_id'] = $this->Auth->user('team_id');
                $rqBoomarkData['cdate'] = date('Y-m-d H:i:s');
                // Save bookmark record
                if ($this->Bookmark->save($rqBoomarkData)) {
                    /* Save record to label_datas table */
                    $this->request->data['LabelDatas']['target_id'] = $id;
                    if ($rq_label['new_label'] != null) {
                        if (isset($rq_label['add_new_text']) && $rq_label['add_new_text'] != null) {
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
                            $this->request->data['LabelDatas']['cdate'] = date('Y-m-d H:i:s');
                            if (!in_array($this->request->data['LabelDatas']['label_id'], $ids)) {
                                $this->Label->LabelData->save($this->request->data['LabelDatas']);
                            }
                        }
                    }
                    /* End save */
                    $this->Session->setFlash(__('コンテンツのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                } else {
                    $this->Session->setFlash(__('コンテンツをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
                }
            }
           $filter_url = $this->Session->read('Access.filter');
           if( empty($filter_url) ){
              $this->redirect(array('action' => 'index'));
           }else{
              $this->redirect($filter_url);
           }
           
        }


        if (!$this->request->data) {
            $this->request->data = $bookmark;
        }

        $this->set ('bookmarkExtData', $bookmarkExtData);

        $this->set('bookmark', $bookmark);
    }

    /**
     * Show bookmark detail
     *
     * @param int $id user id
     * @return array
     */
    public function detail($id)
    {
        if (!$id) {
            $this->Session->setFlash(__('コンテンツのIDを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        if (!is_numeric($id)) {
            $this->Session->setFlash(__('数値以外のIDです'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }

        /* Get bookmark's label */
        $type = 'BookmarkModel';
        $label['lb_name'] = $this->Label->label_Query($id, $type);
        $bookmark = $this->Bookmark->findById($id);
        if (!$bookmark) {
            $this->Session->setFlash(__('Invalid Bookmark ID Provided'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect(array('action' => 'index'));
        }
        //Write session to display in Top screen
        $this->Session->write('Access.id', $bookmark['Bookmark']['id']);
        $this->Session->write('Access.screen', 'bookmark');
        /* Get Bookmark's type */
        $kind = $bookmark['Bookmark']['kind'];
        $bm_type = $this->Master->find('first', array(
            'conditions' => array('Master.id' => "{$kind}"),
            'fields' => array('Master.bookmark_type')
        ));
        $bookmark['Type'] = ($bm_type) ? $bm_type['Master'] : '';
        /* Get Link url */
        $linkUrls = $this->Link->find('all', array(
            'conditions' => array(
                'bookmark_id' => $id,
                'not' => array(
                    'url' => ''
                )
            ),
        ));
        $linkUrls = $this->Link->removeArrayWrapper('Link', $linkUrls);
        /* Get plates */
        $this->loadModel('Tag');
        $plates = $this->Tag->find('all', array(
            'conditions' => array( 'bookmark_id' => $id ),
            'fields' => array('tag','name'),
            'limit' => 51
        ));
        $plates = $this->Tag->removeArrayWrapper('Tag', $plates);
        if( count($plates) > 50 ){
          $plates[50]['tag'] = '...';
        }
        if (isset($this->params['requested'])) {
            return array(
                'bookmark' => $bookmark,
                'label' => $label,
                'linkUrls' => $linkUrls,
                'plates' => $plates
            );
        } else {
            $this->_setModalLayout();
            $this->set(array(
                'bookmark' => $bookmark,
                'label' => $label,
                'linkUrls' => $linkUrls,
                'plates' => $plates
            ));
        }
    }

    /**
     * Delete bookmarks record
     */
    public function delete()
    {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        $this->loadModel('Tag');
        $this->loadModel('BookmarkExtData');
        $rq_data = $this->request->data;
        $ids = $rq_data['id'];
        foreach ($ids as $key => $value) {
            $lb_id = $this->Label->LabelData->find('all', array(
                'conditions' => array(
                    'Label.type' => 'BookmarkModel',
                    'LabelData.target_id' => $value
                ),
                'fields' => array('Label.id')
            ));
            $lb_id = $this->Label->removeArrayWrapper('Label', $lb_id, 'id');
            foreach ($lb_id as $label) {
                $this->Label->LabelData->deleteAll(array('target_id' => $value, 'label_id' => $label), false);
            }
            $this->AccessLog->updateAll(array('bookmark_id' => null), array('bookmark_id' => $ids));
        }
        $this->Tag->updateAll(array('bookmark_id' => 0), array('bookmark_id' => $ids));
        $this->Link->deleteAll(array('bookmark_id' => $ids), false);
        $this->BookmarkExtData->deleteAll(array('bookmark_id' => $ids), false);
        if ($this->Bookmark->delete($ids)) {
            $this->Session->setFlash(__('コンテンツが削除されました。'), 'alert-box', array('class' => 'alert-success'));
        } else {
            $this->Session->setFlash(__('コンテンツを削除することはできません。'), 'alert-box', array('class' => 'alert-danger'));
        }
        echo json_encode(1);
        exit;
    }

    public function system_quickEdit()
    {
        $this->quickEdit();
    }
    /**
     * Update data from ajax form
     *
     * @return json
     */
    public function quickEdit()
    {
        if ($this->request->is('post')) {
            $rq_data = $this->request->data;
            $type = $rq_data['type'];
            $input = isset($rq_data['input']) ? $rq_data['input'] : '';
            $now = date('Y-m-d H:i:s');
            $ids = array();
            $selectall = $rq_data['selectall'];
            if(isset($selectall) && $selectall == '1') {
                $bookmarkData = $this->Session->read("Bookmark");
                if (isset($bookmarkData)) {
                    foreach ($bookmarkData as $key => $value) {
                        array_push($ids, $value['Bookmark']['id']);
                    }
                }
            } else{
                $ids = $rq_data['ids'];
            }
            if ($type == 2) {
                $this->Bookmark->updateAll(array('name' => "'$input'", 'cdate' => "'$now'"), array('id' => $ids));
            } elseif ($type == 3) {
                $this->Bookmark->updateAll(array('kind' => $input, 'cdate' => "'$now'"), array('id' => $ids));
            } elseif ($type == 5) {
                $this->Bookmark->updateAll(array('code' => "'$input'", 'cdate' => "'$now'"), array('id' => $ids));
            } elseif ($type == 6) {
                foreach ($ids as $v_id) {
                    $visible = $this->Bookmark->find('first', array(
                        'conditions' => array('id' => $v_id),
                        'fields' => array('visible')
                    ));
                    if ($visible['Bookmark']['visible'] == 1) {
                        $status = 0;
                    } else {
                        $status = 1;
                    }
                    $this->Bookmark->save(array('id' => $v_id, 'visible' => $status, 'cdate' => date('Y-m-d H:i:s')));
                }
            }

             $this->Session->setFlash(__('コンテンツのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));

            echo json_encode(1);
            exit;
        }
    }

    public function system_quickedit_label()
    {
        $this->quickedit_label();
    }
    /**
     * Update bookmark's label from ajax form
     *
     */
    public function quickedit_label()
    {
        $this->_setModalLayout();
        if (isset($_REQUEST['id']) || isset($_REQUEST['selectall'])) {
            $target_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            $selectall = isset($_REQUEST['selectall']) ? $_REQUEST['selectall'] : '0';
            //Get List Label Hierachi
            $labels = $this->Label->getLabelsArray('BookmarkModel');
            $listLabelHierachy = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

            $this->set(array(
                'labels' => $listLabelHierachy,
                'target_id' => $target_id,
                'selectall' => $selectall
            ));
            return $this->render('quickedit_label', 'ajax');
        }
        if (isset($this->request->data['Bookmark'])) {
            $rq_label = $this->request->data['Bookmark'];
            $label_ids = explode(',', $rq_label['label_id']);
            $target_ids = array();
            $old_label = $this->Label->label_id_Query($target_ids, 'BookmarkModel');

            $selectall = $rq_label['selectall'];
            if($selectall == '1') {
                $bookmarkData = $this->Session->read("Bookmark");
                if (isset($bookmarkData)) {
                    foreach ($this->Session->read("Bookmark") as $key => $value) {
                        array_push($target_ids, $value['Bookmark']['id']);
                    }
                }
            } else {
                $target_ids = explode(',', $rq_label['target_id']);
            }

            // Delete old records
            $this->Label->LabelData->deleteAll(array('target_id' => $target_ids, 'label_id' => $old_label), false);

            // Insert new records
            foreach($label_ids as $label_id){
                foreach($target_ids as $target_id){
                    $this->Label->LabelData->save(array('label_id' => $label_id, 'target_id' => $target_id, 'cdate' => date('Y-m-d H:i:s')));
                }
            }
            $this->Session->setFlash(__('コンテンツのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
            $this->redirect(array('action' => 'index'));
        }

        $this->redirect(array('action' => 'index'));
    }

    public function importCSV()
    {
        $this->autoRender = false;
        if (isset($this->request->data['CSV'])) {
            $rq_data = $this->request->data['CSV'];

            // Upload csv
            if (is_uploaded_file($rq_data['csv']['tmp_name'])) {
                $imginfo = pathinfo($rq_data['csv']['name']);
                if ($imginfo['extension'] == 'csv') {
                    $filename = $imginfo['filename'] . '_' . time() . '.' . $imginfo['extension'];
                    move_uploaded_file(
                        $rq_data['csv']['tmp_name'], WWW_ROOT . 'upload' . DS . 'csv' . DS . $filename
                    );
                    $messages = $this->Bookmark->import($filename);
                    if (count($messages['messages']) > 0)
                        $this->Session->setFlash($messages['messages'], 'alert-box', array('class' => 'alert-danger'));
                } else {
                    $this->Session->setFlash(__('Please upload csv file.'), 'alert-box', array('class' => 'alert-danger'));
                    $this->redirect(array('action' => 'index'));
                }
            }
        }
        $this->redirect(array('action' => 'index'));
    }

}

?>
