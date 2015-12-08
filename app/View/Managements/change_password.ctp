<?php ?>
<div id="main" class="list">
    <h2><?php echo __('ログインパスワード変更') ?></h2>
    <div class="table-hover changetable">
        <?php echo $this->Form->create('Management', (array('controller' => 'managements', 'action' => 'changePassword'))); ?>      
        <table class="table-striped text-left">           
            <div id="Message" class="alert m-n">                
                <?php echo $this->Session->flash('auth'); ?>
            </div>
            <div class="m-t-sm m-b-sm"><span class="asterisk">※</span> <?php echo __('必須') ?></div>
            <tbody>                
                <tr>
                    <th class="typeB"><?php echo __('今までのパスワード') ?> <span class="asterisk">※</span></th>
                    <td><?php echo $this->Form->input('password', array('type' => 'password', 'label' => false, 'div' => false, 'maxlength' => '16', 'data-toggle' => 'checklengh')); ?></td>
                </tr>
                <tr>
                    <th class="typeB"><?php echo __('新しいパスワード') ?> <span class="asterisk">※</span></th>
                    <td><?php echo $this->Form->input('password_update', array('type' => 'password', 'label' => false, 'div' => false, 'maxlength' => '16', 'data-toggle' => 'checklengh')); ?></td>
                </tr>
                <tr>
                    <th class="typeB"><?php echo __('新しいパスワード再入力') ?> <span class="asterisk">※</span></th>
                    <td><?php echo $this->Form->input('cf_newpassword', array('type' => 'password', 'label' => false, 'div' => false, 'maxlength' => '16', 'data-toggle' => 'checklengh')); ?></td>
                </tr>               
            </tbody>
        </table>
        <?php echo $this->Form->submit(__('変更'), array('id' => 'passSubmit', 'class' => 'imgBtn wide m-n', 'div' => false)); ?>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
<script type="text/javascript">
    $(function() {
        $('#ManagementChangePasswordForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                dataType: 'json',
                type: 'post',
                data: $(this).serialize(),
                success: function(response) {
                    $('#Message').html(response.message);
                    switch (response.susscess) {
                        case '1':
                            $('#ManagementPassword').addClass('invalid');
                            $('#Message').removeClass('alert-success');
                            $('#Message').addClass('alert-danger');
                            break;
                        case '2':
                            $('#ManagementPasswordUpdate').addClass('invalid');
                            $('#ManagementCfNewpassword').addClass('invalid');
                            $('#ManagementPassword').removeClass('invalid');
                            $('#Message').removeClass('alert-success');
                            $('#Message').addClass('alert-danger');
                            break;
                        case '3':
                            $('#Message').removeClass('alert-danger');
                            $('#Message').addClass('alert-success');
                            window.setTimeout(function() {
                                location.reload();
                            }, 1000);
                            break;
                        default :
                            $('#ManagementPassword').removeClass('invalid');
                            $('#ManagementPasswordUpdate').removeClass('invalid');
                            $('#ManagementCfNewpassword').removeClass('invalid');
                            $('#Message').removeClass('alert-danger');
                            $('#Message').removeClass('alert-success');
                            break;
                    }

                }
            });
        });


    })
</script>