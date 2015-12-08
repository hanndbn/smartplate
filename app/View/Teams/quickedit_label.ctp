<div class="modal-dialog" >
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __("ラベル編集") ?></h4>
        </div>
        <div class="modal-body">
            <?php
            echo $this->Form->create('Team', array('action' => 'quickedit_label'));
            ?>
            <div class="row quick-height">
                <div class="col-sm-3">
                    <span><?php echo __('ラベルを選択'); ?>:</span>
                </div>
                <div class="col-sm-8">
                    <?php foreach ($label_id as $id) { ?>               
                        <div class="dropdown m-b-sm _strip">
                            <a id="Label_dropdowm_<?php echo $id ?>" role="button" data-toggle="dropdown" class="btn form-control" data-target="#">
                                <?php echo __('ラベルを選択') ?> 
                            </a>
                            <span class="caret"></span>
                            <?php echo $this->Label->renderEditDropdownLabels($list_labels, $id); ?>
                        </div>
                    <?php } ?>
                </div>
                <?php echo $this->Form->input('team_id', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'value' => $team_id)); ?>

            </div>
            <div class = "modal-footer">
                <button type ="submit" class="btn btn-default"><?php echo __('OK') ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel') ?></button>           
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.label_edit').each(function() {
            var $this = $(this),
                    value = $this.attr('data-value');
            if (value)
            {
                $('#Label_dropdowm_' + value).html($(".label_edit[data-id=" + value + "]").html());
            }
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