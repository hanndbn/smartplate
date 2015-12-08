<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __("ラベル編集") ?></h4>
        </div>
        <div class="modal-body">
            <?php
            echo $this->Form->create('Bookmark', array('action' => 'quickedit_label', 'class' => 'fixedDropdownWidth'));
            ?>
            <div class="quick-height">
                <div class="labelArea col-sm-10">
                    <div class="dropdown">
                        <a role="button" data-toggle="dropdown" class="btnLabel btn form-control" data-target="#"
                           data-value>
                            <?php echo __('ラベルを選択') ?>
                        </a>
                        <span class="caret"></span>
                        <?php echo $this->Label->renderDropdownLabels($labels, 'label_edit'); ?>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="btn" id="addLabel" style="background: transparent;">
                        <i class="fa fa-2x fa-plus"></i>
                    </div>
                </div>
                <?php
                echo $this->Form->input('label_id', array('div' => false, 'id' => 'hiddenID', 'type' => 'hidden', 'label' => FALSE));
                echo $this->Form->input('target_id', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'value' => $target_id));
                ?>
            </div>
            <div class="modal-footer">
                <button id="submitBTN" type="submit" class="btn btn-default"><?php echo __('OK') ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel') ?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    // Return an unique array
    function unique(list) {
        var result = [];
        $.each(list, function (i, e) {
            if ($.inArray(e, result) == -1) result.push(e);
        });
        return result;
    }
    $(document).ready(function () {
        // Get label selection id
        $(document).on("click", ".label_name", function (e) {
            e.preventDefault();
            var $this = $(this),
                target = $this.closest('div.dropdown').find('.btnLabel'),
                id = $this.attr('data-id'),
                name = $this.html();
            if ($this.hasClass('label_edit')) {
                target.attr('data-value', id);
                target.empty().html(name);
            }
        });

        $('#addLabel').on('click', function () {
            var clone = $('.labelArea .dropdown:first-child').clone(),
                target = clone.find('.btnLabel'),
                defaultHtml = "<?php echo __('ラベルを選択') ?>";
            target.empty().html(defaultHtml).removeAttr('data-value');
            clone.appendTo('.labelArea');
        });

        $('#submitBTN').click(function (e) {
            e.preventDefault();
            var ids = [];
            $.each($(".labelArea .dropdown .btnLabel"), function () {
                var $this = $(this),
                    id = $this.attr('data-value');
                if (id)
                    ids.push(id);
            });
            ids = unique(ids);
            $('#hiddenID').val(ids);

            if (ids != '')
                $('form').submit();
        });
    });
</script>