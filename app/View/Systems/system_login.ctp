<?php ?>
<div class="user-login modal-dialog modal-sm">
    <div class="modal-content">        
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('ログイン') ?></h4>
        </div>
        <div class="login-content">
            <div class="modal-body">
                <div id='form-login' class='users form mnglogin'>
                    <?php echo $this->Session->flash(); ?>
                    <div id="LoginMessage"><?php echo $this->Session->flash('auth'); ?></div>
                    <?php echo $this->Form->create('System', (array('controller' => 'systems', 'action' => 'login'))); ?>
                    <fieldset>
                        <?php
                        echo $this->Form->input('login_name', array('label' => __('ユーザー名')));
                        echo $this->Form->input('password', array('label' => __('パスワード')));
                        ?>
                    </fieldset>
                    <div class="clearfix">
                        <?php echo $this->Form->submit(__('Login'), array('class' => 'imgBtn wide')); ?>
                    </div>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    $('#ManagementLoginForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            dataType: 'json',
            type: 'post',
            //contentType: "application/json; charset=utf-8",
            data: $(this).serialize(),
            success: function(response) {
                if (response.ok == true) {
                    window.location = response.redirect;
                } else {
                    $('#LoginMessage').empty().html(response.message);
                }
            }
        });
    });
})    
</script>