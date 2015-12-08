<?php

App::uses('AppController', 'Controller');

/**
 * Controller for Label model
 * @package       app.Controller
 * 
 */
class LabelsController extends AppController {

    /**
     * perform logic that needs to happen before controller action
     */
    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->deny();
    }

    /**
     * Redirect to dashboard if action labels/index is perform
     */
    public function index() {
        $this->redirect(array('controller' => 'pages', 'action' => 'index'));
    }

    /**
     * Add new label record
     */
    public function add() {

        $type = '';
        if (isset($this->request->data['Label']['type'])) {
            $type = $this->request->data['Label']['type'];
        }
        if ($this->request->is('post') && trim($type) != '') {
            $redirect = $this->_getRedirect($this->request->data['Label']['type']);

            $existing = $this->Label->find('all', array('conditions' => array(
                    'label' => $this->request->data['Label']['label'],
                    'team_id' => $this->teamId,
                    'type' => $type,
            )));

            if (empty($existing)) {
                $this->Label->create();
                $this->request->data['Label'] += array(
                    'team_id' => $this->teamId,
                    'level' => 0,
                    'parent_id' => 0,
                    'status' => 1,
                    'cdate' => date('Y-m-d H:i:s')
                );

                if ($this->Label->save($this->request->data)) {
                    $this->Session->setFlash(__('ラベルのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                    return $this->redirect($redirect);
                }
                $this->Session->setFlash(__('新規ラベルを登録することはできません。'), 'alert-box', array('class' => 'alert-danger'));
            } else {
                $this->Session->setFlash(__('このラベルは既に存在します。'), 'alert-box', array('class' => 'alert-danger'));
                return $this->redirect($redirect);
            }
        }
    }

    /**
     * Update exited label record
     * 
     * @param int $id Label id
     */
    public function edit($id) {
        $this->_setModalLayout();

        $id = intval($id);

        if (!$id) {
            $this->Session->setFlash(__('ラベルのIDを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect($this->referer);
        }
        $label = $this->Label->findById($id);
        if (!$label) {
            $this->Session->setFlash(__('ラベルのIDは無効です。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect($this->referer);
        } else {
            if (!$this->request->data) {
                $this->request->data = $label;
            }
            $this->set('label', $label);
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            $redirect = $this->_getRedirect($label['Label']['type']);

            $this->Label->id = $id;
            $this->request->data['Label']['cdate'] = date('Y-m-d H:i:s');
            if ($this->Label->save($this->request->data)) {
                $this->Session->setFlash(__('ラベルのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
            } else {
                $this->Session->setFlash(__('ラベルをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));
            }

            $this->redirect($redirect);
        }
    }

    /**
     * Delete label record
     * 
     * @param int $id Label id
     */
    public function delete($id) {
        $this->_setModalLayout();

        $id = intval($id);

        if (!$id) {
            $this->Session->setFlash(__('ラベルのIDを提供してください。'), 'alert-box', array('class' => 'alert-danger'));
            $this->redirect($this->referer);
        } else {
            $this->set(array(
                'id' => $id,
                'type' => isset($this->params['url']['type']) ? $this->params['url']['type'] : ''
            ));
        }
        if ($this->request->is('post')) {
            $redirect = $this->_getRedirect($this->request->data['Label']['type']);

            if ($this->Label->delete($id)) {
                $this->Session->setFlash(__('ラベルが削除されました。'), 'alert-box', array('class' => 'alert-success'));
                return $this->redirect($redirect);
            }
        }
    }

    /**
     * Save the current display of labels
     * 
     * @return array
     */
    public function nestable() {
        if ($this->request->is('post')) {
            $type = $this->request->data['Label']['type'];

            $redirect = $this->_getRedirect($type);
            $serialize = $this->request->data['serialize'];

            $serialize = json_decode($serialize, true);

            $serializeArray = $this->Label->parseNestedJson($serialize);

            if ($serializeArray) {
                foreach ($serializeArray as $key => $label) {
                    $label['display_order'] = $key * 10;

                    $this->Label->save($label);
                }
            }

            /* if ($request->is('ajax'))
              {
              $labels = $this->Label->getLabelsArray($type);
              $labels = $this->Label->makeNestedLabels($this->Label->getLabelHierarchy($labels));

              $labelscount = $this->Label->query('
              SELECT label_id, count(*) AS total
              FROM label_datas
              WHERE label_id in (SELECT id FROM label WHERE type = ' . $type . ')
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
              'type' => $type,
              ));

              return $this->render('/Element/nested');
              } */
        }

        return $this->redirect($redirect);
    }

    /**
     * Get all labels from selected checkbox and find their children
     * 
     * @param array $selected selected label ids
     * @param array $hierarchy from jquery.nestable.js
     * @param int $run current process runtime
     * 
     * @return array
     */
    protected function _filterSelected(array &$selected, array $hierarchy, &$run = 0) {

        foreach ($hierarchy as $label) {
            if (!empty($label['children'])) {
                foreach ($label['children'] as $_label) {

                    if (in_array($_label['id'], $selected)) {

                        if ($run) {
                            if (($key = array_search($_label['id'], $selected)) !== false) {

                                unset($selected[$key]);
                            }
                        }

                        $run++;
                    }
                }

                $this->_filterSelected($selected, $label['children'], $run);
            }
        }

        return $selected;
    }

    /**
     * Update label's status by ajax
     */
    public function ajaxStatus() {
        if ($this->request->is('ajax')) {
            $this->autoRender = false;
            $this->response->type('json');
            $date = date('Y-m-d H:i:s');
            $input = $this->request->data;
//            print_r($input['id']); die;
            if (!empty($input['id']) && isset($input['hierarchy'])) {
                $hierarchyArray = json_decode($input['hierarchy'], true);

                $selected = $this->_filterSelected($input['id'], $hierarchyArray);

                $labels = $this->Label->find('all', array(
                    'conditions' => array('Label.id' => $selected)
                ));

                $labels = $this->Label->removeArrayWrapper('Label', $labels, 'id');
                $ids = array();
                foreach ($labels as $key => $label) {
                    $status = $label['status'] ? 0 : 1;

                    $children = $this->Label->findAllChildren(array($label['id'] => $label));

                    if ($children) {
                        $ids = array_keys($children);
                        $ids[] = $key;
                        $ids = array_unique($ids);
//                        print_r($ids);
                        if ($ids) {
                            $this->Label->updateAll(array('status' => $status, 'cdate' => "'{$date}'"), array('id' => $ids));
                        }
                    } else {
                        $this->Label->updateAll(array('status' => $status, 'cdate' => "'{$date}'"), array('id' => $key));
                    }
//                    print_r($ids);
                }
//                print_r($this->Label->getDataSource()->getLog(false, false));
//                die();
                $this->Session->setFlash(__('ラベルのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));

                return $this->response->body(json_encode(array(
                            'success' => 1,
                            'redirect' => 0
                )));
            }

            $this->Session->setFlash(__('ラベルをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));

            return $this->response->body(json_encode(array(
                        'success' => 0,
                        'redirect' => 0
            )));
        }

        return $this->redirect($this->_getRedirect());
    }

    /**
     * Update label name from ajax
     */
    public function ajaxLabel() {
        if ($this->request->is('ajax')) {
            $this->autoRender = false;
            $this->response->type('json');

            $input = $this->request->data;

            if (!empty($input['id']) && !empty($input['label'])) {
                if ($this->Label->save(array('id' => $input['id'][0], 'label' => $input['label'], 'cdate' => date('Y-m-d H:i:s')))) {
                    $this->Session->setFlash(__('ラベルのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));

                    return $this->response->body(json_encode(array(
                                'success' => 1,
                                'redirect' => 0
                    )));
                }
            }

            $this->Session->setFlash(__('ラベルをアップデートすることはできません。'), 'alert-box', array('class' => 'alert-danger'));

            return $this->response->body(json_encode(array(
                        'success' => 0,
                        'redirect' => 0
            )));
        }

        return $this->redirect($this->_getRedirect());
    }

    /**
     * Delete label name from ajax
     */
    public function ajaxDelete() {
        if ($this->request->is('ajax')) {
            $this->autoRender = false;
            $this->response->type('json');

            $input = $this->request->data;

            if (!empty($input['id'])) {
                $this->Label->LabelData->deleteAll(array('LabelData.label_id' => $input['id']), false);

                $this->Label->deleteAll(array('Label.id' => $input['id']), false);

                $this->Session->setFlash(__('ラベルが削除されました。'), 'alert-box', array('class' => 'alert-success'));

                return $this->response->body(json_encode(array(
                            'success' => 1,
                            'redirect' => 0
                )));
            }

            //$this->Session->setFlash(__('Unable to delete your label.'), 'alert-box', array('class' => 'alert-danger'));

            return $this->response->body(json_encode(array(
                        'success' => 0,
                        'redirect' => 0
            )));
        }
    }

    /**
     * Determine redirect url based on label's type 
     * 
     * @param string $type Label's type, if empty return to webroot
     */
    protected function _getRedirect($type = null) {
        switch ($type) {
            case 'BookmarkModel':
                $redirect = array('controller' => 'bookmarks', 'action' => 'label');
                break;

            case 'TagModel':
                $redirect = array('controller' => 'tags', 'action' => 'label');
                break;

            case 'UserModel':
                $redirect = array('controller' => 'apps', 'action' => 'label');
                break;

            case 'TeamModel':
                $redirect = array('controller' => 'teams', 'action' => 'label');
                break;

            default:
                $redirect = ('/');
                break;
        }

        return $redirect;
    }

}