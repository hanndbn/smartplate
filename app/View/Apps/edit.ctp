<?php
/* Check action */
$action = $this->action;
$session = $this->Session->read('Auth.User');
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php
                if ($action == 'edit') {
                    echo __('ユーザー編集');
                } else {
                    echo __('ユーザー登録');
                }
                ?>
            </h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list">                
                <?php
                echo $this->Form->create('User', array('class' => 'form-horizontal'));
                ?>
                <div class="row">
                    <div class="left-block col-sm-6">
                        <div class="form-group">
                            <?php if ($action == 'edit') { ?>
                                <div class="control-label col-sm-4">
                                    <span><?php echo __('ID') ?></span>
                                </div>
                                <div class="detail-id-content col-sm-8">
                                    <div class="checkbox">
                                        <span><?php echo $user['User']['id']; ?></span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('ログインID') ?>:</span>
                            </div>
                            <div class="detail-name-content col-sm-8">
                                <?php echo $this->Form->input('User.login_name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '16', 'data-toggle' => 'checklengh')); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('パスワード') ?>:</span>
                            </div>
                            <div class="detail-url-content col-sm-8">
                                <?php
                                echo $this->Form->input('User.password', array('div' => false, 'type' => 'password', 'label' => FALSE, 'value' => '', 'maxlength' => '12', 'data-toggle' => 'checklengh'));
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('使用者名') ?>:</span>
                            </div>
                            <div class="detail-name-content col-sm-8">
                                <?php echo $this->Form->input('User.name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '64', 'data-toggle' => 'checklengh')); ?>
                            </div>
                        </div>                        
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('コメント') ?>:</span>
                            </div>
                            <div class="detail-url-content col-sm-8">
                                <?php echo $this->Form->input('User.comment', array('div' => false, 'type' => 'textarea', 'label' => FALSE, 'maxlength' => '512', 'data-toggle' => 'checklengh')); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4"></div>
                            <div class="detail-visible-content col-sm-8">
                                <div class="checkbox">
                                    <?php
                                      if ($action == 'edit') { 
                                        echo $this->Form->input('User.status', array('div' => false, 'type' => 'checkbox', 'label' => FALSE, 'class' => 'm-n'));
                                      } else { 
                                        echo $this->Form->input('User.status', array('div' => false, 'type' => 'checkbox', 'checked' => true, 'label' => FALSE, 'class' => 'm-n'));
                                      }
                                    ?>
                                    <span class="m-l-md"><?php echo __('有効') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4"></div>
                            <div class="detail-visible-content col-sm-8">
                                <div class="checkbox">
                                    <?php echo $this->Form->input('User.power', array('div' => false, 'type' => 'checkbox', 'label' => FALSE, 'class' => 'm-n')); ?>
                                    <span class="m-l-md"><?php echo __('アプリを管理モードで使用する') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="right-block col-sm-6" style="padding-left: 0px;">
                        <div class="control-label-title">
                            <span><?php echo __('ラベル') ?>:</span>
                        </div>
                        <div class="control-label-content">
                            <div class="form-group">
                                <div class="control-label-list col-sm-6">
                                    <div class="dropdown _strip">
                                        <a id="List_label" role="button" data-toggle="dropdown" class="btn form-control" data-target="#">
                                            <?php echo __('ラベルを選択') ?> 
                                        </a>
                                        <span class="caret"></span>
                                        <?php echo $this->Label->renderDropdownLabels($labels); ?>
                                    </div>
                                </div>
                                <?php echo $this->Form->input("Label.new_label", array('label' => false, 'type' => 'hidden')); ?>
                                <div class="control-label-text col-sm-6" style="padding-left: 0px;">
                                    <?php
                                    if ($session['authority'] == 3) {
                                        echo $this->Form->input('Label.add_new_text', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '64'));
                                    } else {
                                        echo $this->Form->input('Label.add_new_text', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'maxlength' => '64'));
                                    }
                                    ?>
                                </div>
                            </div>
                                    <?php if ($action == 'edit') { ?>
                                <div class="form-group">
                                    <div class="control-label-own col-sm-6">
                                                <?php foreach ($currentLabels as $lbId) { ?>
                                            <div class="dropdown m-b-sm _strip">
                                                <a id="Label_dropdowm_<?php echo $lbId ?>" role="button" data-toggle="dropdown" class="btn form-control" data-target="#">
                                                <?php echo __('ラベルを選択') ?> 
                                                </a>
                                            <?php echo $this->Label->renderEditDropdownLabels($labels, $lbId); ?>
                                            </div>                                   
                                <?php } ?>
                                    </div>
                                </div>
<?php } ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-default"><?php echo __('OK'); ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel') ?></button>           
                </div>
            </div>
        </div>
    </div>

</div>
<script type="text/javascript">
    $(document).ready(function() {

<?php if ($action == 'edit') { ?>
            $('.label_edit').each(function() {
                var $this = $(this),
                        value = $this.attr('data-value');

                if (value)
                {
                    $('#Label_dropdowm_' + value).html($(".label_edit[data-id=" + value + "]").html());
                }
            });
<?php } ?>

        $('.label_name').click(function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            $('input#LabelNewLabel').val(id);
            $('#List_label').empty().html($(this).html());
        });

        $('.label_edit').click(function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var value = $(this).attr('data-value');
            var selector = $("input#InputLabel_" + value).val(id);
            $('#Label_dropdowm_' + value).empty().html($(this).html());
        });
    });
</script>