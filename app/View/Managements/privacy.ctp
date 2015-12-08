<div id="main" class="list p-b-lg">
    <h2><?php echo __('Privacy policy and terms of use') ?></h2>
    <div id="content" class="p-sm">
        <div class="entry entry-black-txt">
            <div>
             <?php echo $this->element('Terms-of-Use-Cloud-ja'); ?>
             <?php echo $this->element('PrivacyPolicy-Cloud-ja'); ?>

        </div>
        <?php if( $this->viewPath === 'Policy' ) { ?>
          
        <?php } else { ?>
        <div id="agreeTerms">
            <?php echo $this->Form->checkbox('Privacy', array('class' => 'check', 'value' => 1, 'hiddenField' => false)); ?><span><?php echo __('利用規約に同意') ?></span><br/>
            <?php echo $this->Form->checkbox('Terms', array('class' => 'check', 'value' => 1, 'hiddenField' => false)); ?><span><?php echo __('プライバシーポリシーに同意') ?></span>
            <div class="submit">
                <input id="cancelBtn" class="imgBtn wide" type="button" value="<?php echo __('Cancel') ?>">
                <input id="termBtn" class="imgBtn wide" type="button" value="<?php echo __('OK') ?>">
            </div>
        </div>
        <?php }?>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var buildUrl = function(base, key, value) {
            var sep = (base.indexOf('?') > -1) ? '&' : '?';
            return base + sep + key + '=' + value;
        }
        var now = new Date().getTime();
        var url = buildUrl('<?php echo $this->Html->url(array('controller' => 'managements', 'action' => 'privacyPolicy'), true); ?>', '_t', now);
        var status = <?php echo isset($data['Management']['agree_flag'] ) ? $data['Management']['agree_flag']  : 0 ?>;
        // Show default status
        switch (status) {
            case 1:
                $('#Privacy').prop('checked', true);
                break;
            case 2:
                $('#Terms').prop('checked', true);
                break;
            case 3:
                $('#Privacy').prop('checked', true);
                $('#Terms').prop('checked', true);
                break;
            default:
                $('#Privacy').prop('checked', false);
                $('#Terms').prop('checked', false);
                break;
        }
        
        $('#cancelBtn').click(function() {
            location.replace('./logout');
        });
        $('#termBtn').click(function() {
            var checked = $('.check:checked');
            if (checked.length < 1)
                status = 0;
            if (checked.length == 1 && $('#Privacy').prop('checked'))
                status = 1;
            if (checked.length == 1 && $('#Terms').prop('checked'))
                status = 2;
            if (checked.length == 2)
                status = 3;
            $.ajax({
                type: 'POST',
                url: url,
                data: {status: status},
                success: function(rs) {
                  if(status == 3 ){
                      location.replace('./');
                  }else{
                    alert('<?php echo __("利用規約とプライバシポリシーに同意されておりません"); ?>');
                    location.replace('./logout');
                  }
                }
            });
        });
    });
</script>
