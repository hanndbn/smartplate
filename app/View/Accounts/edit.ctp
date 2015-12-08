<?php
/* Check action */
$action = $this->action;
$session = $this->Session->read('Auth.User');
$authority = $session['authority'];
?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php
                if ($action == 'edit') {
                    echo __('アカウント編集');
                } else {
                    echo __('アカウント登録');
                }
                ?>
            </h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list modal-edit p-sm">                
                <?php
                echo $this->Form->create('Management', array('class' => 'form-horizontal'));
                ?>
                <div class="row">
                    <div class="form-group">
                        <?php if ($action == 'edit') { ?>
                            <div class="control-label col-sm-3">
                                <span><?php echo __('ID') ?>:</span>
                            </div>
                            <div class="detail-id-content col-sm-5">
                                <div class="checkbox"><?php echo $user['Management']['id']; ?></div>
                            </div>   
                            <div class="col-sm-3 text-right m-r-sm">
                                <div class="checkbox">
                                    <?php echo $this->Form->input('Management.status', array('div' => false, 'class' => 'm-n', 'type' => 'checkbox', 'label' => FALSE)); ?>
                                    <span class="m-l-md"><?php echo __('有効') ?></span>                                    
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="col-sm-11 text-right m-r-sm">
                                <div class="checkbox">
                                    <?php echo $this->Form->input('Management.status', array('div' => false, 'class' => 'm-n', 'type' => 'checkbox', 'checked' => true, 'label' => FALSE)); ?>
                                    <span class="m-l-md"><?php echo __('有効') ?></span>                                    
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <div class="control-label col-sm-3">
                            <span><?php echo __('種別') ?>:</span>
                        </div>
                        <div class="col-sm-6">
                            <?php
                            if ($this->request->prefix != 'system') {
                                if ($authority == 2) {
                                    ?>
                                    <div class="checkbox"><?php echo __('Editor') ?></div>
                                    <?php echo $this->Form->input('Management.authority', array('div' => false, 'type' => 'hidden', 'value' => 3)); ?>
                                <?php } else { ?>
                                    <div class="checkbox"><?php echo __('Manager') ?></div>
                                    <?php echo $this->Form->input('Management.authority', array('div' => false, 'type' => 'hidden', 'value' => 2)); ?>
                                    <?php
                                }
                            } else {
                                ?>
                                <div class="checkbox"><?php echo __('Admin') ?></div>
                                <?php echo $this->Form->input('Management.authority', array('div' => false, 'type' => 'hidden', 'value' => 1)); ?>
                            <?php }
                            ?>
                        </div>
                    </div>
                    <?php if ($authority == 2) { ?>
                        <div class="form-group">
                            <div class="control-label col-sm-3">
                                <span><?php echo __('プロジェクト') ?>:</span>
                            </div>
                            <div class="col-sm-6">
                                    <?php 
                                    if($action == 'edit'){
                                        echo $this->Form->input("Management.team_id", array('label' => false, 'options' => $list_project, 'div' => false, 'default' => $user['Management']['team_id']));
                                    }else{
                                        echo $this->Form->input("Management.team_id", array('label' => false, 'options' => $list_project, 'div' => false));
                                    }
                                    ?>          
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <div class="control-label col-sm-3">
                            <span><?php echo __('ログインID') ?>:</span>
                        </div>
                        <div class="col-sm-6">
                            <?php
                            if ($action == 'edit' || $action == 'system_edit') {
                                echo $this->Form->input('Management.newlogin_name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'data-toggle' => 'checklengh', 'value' => $user['Management']['login_name']));
                                echo $this->Form->input('Management.login_name', array('div' => false, 'type' => 'hidden'));
                            } else {
                                echo $this->Form->input("Management.login_name", array('label' => false, 'div' => false, 'type' => 'text', 'maxlength' => '24', 'data-toggle' => 'checklengh'));
                            }
                            ?>                          
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="control-label col-sm-3">
                            <span><?php echo __('パスワード') ?>:</span>
                        </div>
                        <div class="col-sm-6">
                            <?php
                            if ($action == 'edit' || $action == 'system_edit') {
                                echo $this->Form->input("Management._password", array('id' => 'tempPassword', 'label' => false, 'div' => false, 'type' => 'password', 'maxlength' => '16', 'data-toggle' => 'checklengh'));
                            } else {
                                echo $this->Form->input("Management.password", array('id' => 'tempPassword', 'label' => false, 'div' => false, 'type' => 'password', 'maxlength' => '16', 'data-toggle' => 'checklengh'));
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="control-label col-sm-3">
                            <span><?php echo __('管理名') ?>:</span>
                        </div>
                        <div class="col-sm-6">
                            <?php echo $this->Form->input("Management.name", array('label' => false, 'div' => false, 'type' => 'text', 'maxlength' => '64', 'data-toggle' => 'checklengh')); ?>
                        </div>
                    </div>                   
                    <div class="form-group">
                        <div class="control-label col-sm-3">
                            <span><?php echo __('コメント') ?>:</span>
                        </div>
                        <div class="col-sm-8">
                            <?php echo $this->Form->input('Management.memo', array('div' => false, 'type' => 'textarea', 'cols' => false, 'label' => FALSE, 'maxlength' => '255', 'data-toggle' => 'checklengh')); ?>
                        </div>
                    </div>             
                </div>
                <div class = "modal-footer">
                    <button id ="submit_button" type = "submit" class = "btn btn-default"><?php echo __('OK') ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel') ?></button>           
                </div>
            </div>
        </div>
    </div>

</div><!-- /.modal-content -->

