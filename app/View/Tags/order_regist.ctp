<?php ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('プレート申請'); ?></h4>
        </div>
        <div class="modal-body">
            <?php echo $this->Form->create('Order', array('class' => 'form-horizontal')); ?>
            <div class="main-modal">   
                <div class="borderwrap p-md">
                    <div class="form-group">
                        <div class="wrapper control-label col-sm-4"><span><?php echo __('申請パッケージ数：') ?></span></div>
                        <div class="wrapper col-sm-4">
                            <?php echo $this->Form->input('count', array('div' => false, 'type' => 'text', 'label' => FALSE)); ?>
                        </div>
                        <div class="wrapper col-sm-4">
                            <div class="checkbox"><?php echo __('パッケージ'); ?></div>
                        </div>
                    </div>  
                    <div class="form-group">
                        <div class="wrapper control-label col-sm-4"></div>
                        <div class="wrapper col-sm-8">
                            <div class="checkbox">
                                <?php echo __('1パッケージでプレート50枚になります'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 text-right">
                            <div class="checkbox">
                                <?php echo $this->Form->input('type', array('div' => false, 'class' => 'm-n', 'type' => 'checkbox', 'label' => FALSE)); ?>
                            </div>
                        </div>
                        <div class="wrapper col-sm-8">
                            <div class="checkbox">
                                <?php echo __('プレートIDのみ取得'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class = "modal-footer">
                <button id="submit_button" type = "submit" class = "btn btn-default"><?php echo __('OK') ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel') ?></button>           
            </div>
        </div>
    </div>
</div><!-- /.modal-content -->

<script type="text/javascript">

    $(document).ready(function() {

        $('#submit_button').click(function (e){
            var $val = $('#OrderCount').val();
            console.log($val);
            if($.trim($val) !== '' && $.isNumeric($val) === false){
                alert('Please enter a number');               
//                $("#submit_button").attr("disabled", "disabled");
                $('#OrderCount').addClass('invalid');
                e.preventDefault();
            }else if($val > 10000 || $val <= 0){
                alert('Please select a value that is in 1 ~ 10000');
//                $("#submit_button").attr("disabled", "disabled");
                $('#OrderCount').addClass('invalid');
                e.preventDefault();
            }else{
//                $("#submit_button").removeAttr("disabled");
            }
        });
    });
</script>