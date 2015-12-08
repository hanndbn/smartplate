<div class="modal-dialog">
    <div class="modal-content">        
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">プレート発行</h4>
        </div>
        <?php echo $this->Form->create('Order', array('class' => 'form-horizontal'), array('controller' => 'orders', 'action' => 'releasePlate')); ?>

        <div class="modal-body">
            <div id="msg"></div>
            <div class="form-group">
                <div class="col-sm-4"><span><?php echo __('識別子') ?>:</span></div>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('Order.alias', array('div' => false, 'type' => 'text', 'label' => FALSE)); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4"><span><?php echo __('Lot番号') ?>:</span></div>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('Order.lotNumber', array('div' => false, 'type' => 'text', 'label' => FALSE)); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4"><span><?php echo __('追加Lot数') ?>:</span></div>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('Order.addLotNumber', array('div' => false, 'type' => 'text', 'label' => FALSE)); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4"><span><?php echo __('1Lot当たりのID数') ?>:</span></div>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('Order.IDPer1Lot', array('div' => false, 'type' => 'text', 'label' => FALSE, 'value' => 50)); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4 checkbox"><span><?php echo __('管理ID') ?>:</span></div>
                <div class="col-sm-8">
                    <select name="data[Order][userID]" autocomplete="off" id="OrderUserID" class="col-sm-4">
                        <?php
                        //echo "<option value='0' data-name=''>未設定</option>";
                        foreach ($listUserID as $adminID => $name) {
                            echo "<option value='{$adminID}' data-name='{$name}'>{$adminID}:{$name}</option>";
                        }
                        ?>
                    </select>
                    <div class="col-sm-8 checkbox">
                        <?php echo __('管理ユーザー名')?>: 
                        <span class="name">
                            <?php
                            if($listUserID){
                                $index = array_values($listUserID);
                                echo $index[0];
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-4 checkbox"><span><?php echo __('プロジェクトID') ?>:</span></div>
                <div class="col-sm-8">
                    <select name="data[Order][teamID]" autocomplete="off" id="OrderTeamID" class="col-sm-4">
                        <?php
                        //echo "<option value='0' data-name=''>未設定</option>";
                        foreach ($listProjectID as $pID => $name) {
                            echo "<option value='{$pID}' data-name='{$name}'>{$pID}:{$name}</option>";
                        }
                        ?>
                    </select>
                    <div class="col-sm-8 checkbox">
                        <?php echo __('プロジェクト名')?>: 
                        <span class="name">
                            <?php
                            if($listProjectID){
                                $index = array_values($listProjectID);
                                echo $index[0];
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php echo $this->Form->input('ID', array('id' => 'orderID', 'div' => false, 'type' => 'hidden', 'label' => FALSE, 'value' => $id)); ?>
            <div class = "modal-footer">
                <button type="submit" id="addPlateSubmit" class="btn btn-default"><?php echo __('OK') ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel') ?></button>           
            </div>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>
</div>

<script type="text/javascript">
    var buildUrl = function(base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    };
    //Validate form
    $(document).ready(function() {
        $('#OrderSystemReleasePlateForm').submit(function(e) {
            var message = [];
            var aliasVal = $('#OrderAlias').val();
            var lotVal = $('#OrderLotNumber').val();
            var addlotVal = $('#OrderAddLotNumber').val();
            var order = $('#OrderIDPer1Lot').val();
            if (!aliasVal.match(/^[a-zA-Z]{2,2}$/)) {
                $('#OrderAlias').addClass('invalid');
                message.push("※ 識別子 must be 2 alphabet character<br/>");
            } else {
                $('#OrderAlias').removeClass('invalid');
            }
            if (!lotVal.match(/^[0-9]{5,5}$/)) {
                $('#OrderLotNumber').addClass('invalid');
                message.push("※ Lot番号 must be 5 numbers<br/>");
            } else {
                $('#OrderLotNumber').removeClass('invalid');
            }
            if (!addlotVal.match(/^[0-9]+$/) || addlotVal < 1) {
                $('#OrderAddLotNumber').addClass('invalid');
                message.push("※ 追加Lot数 must be numbers(min 1)<br/>");
            } else {
                $('#OrderAddLotNumber').removeClass('invalid');
            }
            if (!order.match(/^[0-9]+$/) || order < 1) {
                $('#OrderIDPer1Lot').addClass('invalid');
                message.push("※ 1Lot当たりのID数 must be numbers(min 1)<br/>");
            } else {
                $('#OrderIDPer1Lot').removeClass('invalid');
            }
            if (message.length != 0) {
                if ($('#messageErr').length == 0) {
                    $('#msg').append("<div class='form-group'><div class='col-sm-12'><div class='alert-dismissible p-md' id='messageErr'></div></div></div>")
                }
                $("#messageErr").addClass('alert-danger').html(message.join(""));
                e.preventDefault();
            } else {
                e.preventDefault();
                $('#msg').empty();
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    dataType: 'JSON',
                    success: function(rs) {
                        if (rs.result == 'pass') {
                            $('#ajaxModal').modal('hide');
                            $.confirm({
                                text: '<?php echo __("発行済に変更しますか") ?>',
                                title: '<?php echo __("確認") ?>',
                                confirm: function(confirmButton) {
                                    var id = $('#orderID').val();
                                    var status = 5;
                                    var data = {id: id, status: status};
                                    var now = new Date().getTime();
                                    var url = buildUrl('<?php echo $this->Html->url(array('controller' => 'orders', 'action' => 'changeStatus'), true); ?>', '_t', now);
                                    $.ajax({
                                        type: 'POST',
                                        dataType: 'JSON',
                                        url: url,
                                        data: data,
                                        success: function(rs) {
                                            location.reload();
                                        }
                                    });
                                },
                                cancelButton: "Cancel",
                                confirmButton: "OK",
                                confirmButtonClass: 'btn-default',
                                post: true
                            });
                        }
                    }
                });
            }
        });

$(document).on('change', '#OrderUserID, #OrderTeamID' , function(){
    var id = $(this).val();
    var name = $(this).find('option[value|="'+id+'"]').data('name');
    $(this).closest('div').find('.name').html(name);
});
});

</script>