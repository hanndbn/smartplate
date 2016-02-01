<?php
App::uses('AppController', 'Controller');
App::uses('Image', 'Model');


class ChangeImagesController extends AppController
{
    public function quickedit_img()
    {

        $this->_setModalLayout();
        if (isset($_REQUEST['strtable']) && (isset($_REQUEST['id']) || isset($_REQUEST['selectall']))) {
            $target_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            $selectall = isset($_REQUEST['selectall']) ? $_REQUEST['selectall'] : '0';
            $strTable =  $_REQUEST['strtable'];
            $this->set(array(
                'target_id' => $target_id,
                'selectall' => $selectall,
                'strtable' => $strTable));
            return $this->render('quickedit_img', 'ajax');
        }
        $ids = array();
        if (isset($this->request->data['ChangeImages'])) {
            $strTable = $this->request->data['ChangeImages']['strtable'];
            $this->loadModel($strTable);
            $selectall = $this->request->data['ChangeImages']['selectall'];
            if($selectall == '1') {
                $data = $this->Session->read($strTable);
                if (isset($data)) {
                    foreach ($this->Session->read($strTable) as $key => $value) {
                        array_push($ids, $value[$strTable]['id']);
                    }
                }
            } else {
                $ids = explode(',', $this->request->data['ChangeImages']['target_id']);
            }
            if(isset($this->request->data['ChangeImages']['icon']['tmp_name'])) {
                if (is_uploaded_file($this->request->data['ChangeImages']['icon']['tmp_name'])) {
                    $ImageModel = new Image();
                    if($strTable == 'Tag'){
                        $ImageModel->target_folder = 'plate';
                    }
                    $linkIcon = $ImageModel->saveImage($this->Auth->user('team_id'), $this->request->data['ChangeImages']['icon']);
                } else {
                    $linkIcon = "";
                }
                if(!empty($linkIcon)) {
                    $linkIcon = str_replace('\\', '\\\\', $linkIcon);
                    $this->$strTable->updateAll(array('icon' => "'$linkIcon'"), array('id' => $ids));
                    $this->Session->setFlash(__('プレートのアップデートに成功しました。'), 'alert-box', array('class' => 'alert-success'));
                }
            }
        }
        $controller = 'pages';
        if($strTable == 'Tag'){
            $controller = 'tags';
        }
        $this->redirect(array('controller' => $controller, 'action' => 'index'));
    }
}