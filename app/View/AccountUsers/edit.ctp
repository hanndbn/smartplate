<?php $action = $this->action; ?>
<div id="main" class="list">
    <h2><?php echo ($action == 'edit') ? 'アカウント編集' : __('登録情報'); ?></h2>
    <?php echo $this->Session->flash() ?>
    <?php echo $this->Form->create('AccountUser', array('class' => 'form-horizontal')); ?>
    <div class="form-body">
        <div class="form-group">
            <?php if ($action == 'edit') { ?>
                <div class="control-label col-sm-2">
                    <span><?php echo __('ID') ?>：</span>
                </div>
                <div class="detail-id-content col-sm-4">
                    <div class="checkbox"><?php echo $user['id']; ?></div>
                </div>                 
            <?php }?>
        </div>
        <div class="form-group">
            <label for="family_name" class="col-sm-2 control-label"><?php echo __('氏') ?></label>
            <div class="col-sm-5">
                <?php echo $this->Form->input('AccountUser.family_name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '45', 'id' => 'family_name', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="given_name" class="col-sm-2 control-label"><?php echo __('名') ?></label>
            <div class="col-sm-5">
                <?php echo $this->Form->input('AccountUser.given_name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '45', 'id' => 'given_name', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="mail" class="col-sm-2 control-label"><?php echo __('メール') ?></label>
            <div class="col-sm-5">
                <?php echo $this->Form->input('AccountUser.mail', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '64', 'id' => 'mail', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="tel" class="col-sm-2 control-label"><?php echo __('Tel') ?></label>
            <div class="col-sm-5">
                <?php echo $this->Form->input('AccountUser.tel', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '20', 'id' => 'tel', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="company" class="col-sm-2 control-label"><?php echo __('会社名') ?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->input('AccountUser.company', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'id' => 'company', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="department" class="col-sm-2 control-label"><?php echo __('部署') ?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->input('AccountUser.department', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '160', 'id' => 'department', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="zip_code" class="col-sm-2 control-label"><?php echo __('郵便番号') ?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->input('AccountUser.zip_code', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '20', 'id' => 'zip_code', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="country" class="col-sm-2 control-label"><?php echo __('国') ?></label>
            <div class="col-sm-9">
                <?php
                if ($action == 'edit') {
                    echo $this->element('country', array('country' => $user['country'], 'name' => 'data[AccountUser][country]'));
                } else {
                    echo $this->element('country', array('country' => '', 'name' => 'data[AccountUser][country]'));
                }
                ?>
            </div>
        </div>

        <div class="form-group">
            <label for="region" class="col-sm-2 control-label"><?php echo __('地域') ?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->input('AccountUser.region', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'id' => 'region', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="city" class="col-sm-2 control-label"><?php echo __('都市') ?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->input('AccountUser.city', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'id' => 'city', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="col-sm-2 control-label"><?php echo __('住所') ?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->input('AccountUser.address', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'id' => 'address', 'data-toggle' => 'checklengh')); ?>
            </div>
        </div>
    </div>
    <div class="footer-btn col-sm-7 m-b-lg">
        <input id="resetBtn" type="button" class="imgBtn wide hightlight-btn pull-right" value="Reset"/>
        <input id="submit_btn" type="submit" class="imgBtn wide hightlight-btn m-r-sm pull-right" value="OK"/>                
    </div>
    <?php echo $this->Form->end(); ?>
</div>

<script type="text/javascript">
// Check email format
    function validateEmail(sEmail) {
        var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        if (filter.test(sEmail)) {
            return true;
        }
        else {
            return false;
        }
    }
    $(document).ready(function() {
        $('#resetBtn').click(function() {
            $('form').trigger('reset');
        });

        $('#submit_btn').click(function(e) {
            var sEmail = $('#mail').val();
            var sPhone = $('#tel').val();
            var sZCode = $('#zip_code').val();
            if (!validateEmail(sEmail)) {
                alert('<?php echo __('有効なメールではありません。') ?>');
                $('#mail').addClass('invalid');
                e.preventDefault();
            }
            if ($.trim(sPhone) !== '' && $.isNumeric(sPhone) === false) {
                alert('<?php echo __('有効な電話番号ではありません。') ?>');
                $('#tel').addClass('invalid');
                e.preventDefault();
            }
            if ($.trim(sZCode) !== '' && $.isNumeric(sZCode) === false) {
                alert('<?php echo __('有効な郵便番号ではありません。') ?>');
                $('#zip_code').addClass('invalid');
                e.preventDefault();
            }
        });
        $('#mail').blur(function() {
            var sEmail = $('#mail').val();
            if (validateEmail(sEmail)) {
                $('#mail').removeClass('invalid');
            }
        });
        $('#tel').blur(function(e) {
            var sPhone = $('#tel').val();
            var sZCode = $('#zip_code').val();
            if ($.trim(sPhone) === '' && $.isNumeric(sPhone) !== false) {
                $('#tel').removeClass('invalid');
            }
            if ($.trim(sPhone) === '' && $.isNumeric(sPhone) !== false) {
                $('#zip_code').removeClass('invalid');
            }
        });
    });
</script>