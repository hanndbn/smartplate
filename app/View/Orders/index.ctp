<?php
$session = $this->Session->read('Auth.User');
$authority = $session['authority'];
?>
<div id="main" class="list">
    <h2><?php echo __('発注一覧') ?></h2>
    <?php echo $this->Session->flash() ?>

    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>
    <div>

    <div class="list">
    <?php
	echo $this->Form->create(null, array('action' => 'index', 'enctype' => 'multipart/form-data'));
	echo $this->Form->input(null, array('type'=>'file','label'=>'' ));
	echo $this->Form->submit('CSVファイルアップロード', array('class' => 'imgBtn wide'));
	?>
	</div>

       <?php if ($this->request->prefix == 'system') {
           echo $this->Html->link(__('プレート発行'), array('controller' => 'orders', 'action' => 'releasePlate/0'), array('id' => 'dialog_new_order_open', 'class' => 'imgBtn wide right_fix_menu', 'data-toggle' => 'ajaxModal'));
       } ?>
    </div>
    <div class="table-hover">
        <table id="order-list">
            <thead>

                <tr>
                    <th class="typeB highlight"><?php echo __('プロジェクト名'); ?></th>
                    <th class="typeB highlight"><?php echo __('プラン'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('count', __('パッケージ数')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('status', __('状態')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('request_date', __('依頼日')); ?></th>
                    <?php if ($this->request->prefix == 'system') { ?>
                    <th class="typeB"></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="text-center" id="<?php echo 'toggle' . $order['Order']['id'] ?>" >
                        <td><?php echo $order['Team']['name']; ?></td>
                        <td><?php echo $order['planType'] ?></td>
                        <td><?php echo $order['Order']['count'] ?></td>
                        <td data-id="<?php echo $order['Order']['id'] ?>" data-status="<?php echo $order['Order']['status'] ?>">
                            <?php
                            $status = $order['Order']['status'];
                            $statusText = '';
                            switch ($status) {
                                case 0:
                                    $statusText = __('未設定');
                                    break;
                                case 1:
                                    $statusText = __('申請');
                                    break;
                                case 2:
                                    $statusText = __('承認');
                                    break;
                                case 3:
                                    $statusText = __('発行依頼');
                                    break;
                                case 4:
                                    $statusText = __('作成中');
                                    break;
                                case 5:
                                    $statusText = __('発行済');
                                    break;
                                case 9:
                                    $statusText = __('キャンセル');
                                    break;
                                case 99:
                                    $statusText = __('完了');
                                    break;
                            }
                            if ($this->request->prefix != 'system') {
                                if ($authority == 2) {
                                    echo ($status == 1) ? '<span class="apply status">' . $statusText . '</span>' : $statusText;
                                }
                                if ($authority == 1) {
                                    echo ($status == 2 || $status == 5) ? '<span class="confirm status">' . $statusText . '</span>' : $statusText;
                                }
                            } else {
                                echo ($status == 3) ? '<span class="newplate status" data-modal="' . $this->webroot . 'system/orders/releasePlate/' . $order['Order']['id'] . '">' . $statusText . '</span>' : $statusText;
                            }
                            ?>
                        </td>
                        <td><?php echo date('Y/m/d H:i:s', strtotime($order['Order']['request_date'])) ?></td>

                        <?php if ($this->request->prefix == 'system') { ?>
                        <td><?php if( !empty($order['csv'])){ echo '<a href="./orders/index/act:download/num:' . $order['csv'] . '">csv</a>'; } ?></td>
                        <?php } ?>
                    </tr>
                <?php endforeach; ?>
                <?php unset($orders); ?>
            </tbody>
        </table>
    </div>
    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>
</div>
<!------Confirm Modal Dialog---------->
<div id="applyPlate" class="modal fade">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body m-b-lg">
                <button id="cancelApply" type="button" class="btn btn-default pull-left"><?php echo __('申請をキャンセル') ?></button>
                <button id="agreeApply" type="button" class="btn btn-default pull-right"><?php echo __('承認する') ?></button>
            </div>
        </div>
    </div>
</div>
<div id="confirmPlate" class="modal fade">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Modal title</h4>
            </div>
            <div class="modal-body m-b-lg">
                <button id="cancelDistribution" type="button" class="btn btn-default pull-left"><?php echo __('新規プレート発注') ?></button>
                <button id="completeDistribution" type="button" class="btn btn-default pull-right"><?php echo __('割当完了') ?></button>
            </div>
        </div>
    </div>
</div>
<?php echo $this->Html->link('Detail', array(), array('class' => 'invisible', 'id' => 'detailModal', 'data-toggle' => 'ajaxModal')); ?>
<script type="text/javascript">
    var status;
    var id;
    var buildUrl = function(base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    };
    function ajaxChangeStatus(data) {
        var now = new Date().getTime();
        var url = buildUrl('<?php echo $this->Html->url(array('controller' => 'orders', 'action' => 'changeStatus'), true); ?>', '_t', now);
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(rs) {
                location.reload();
            }
        });
    }
    $(document).ready(function() {
        $('th a').append(' <i class="fa fa-sort"></i>');
        $('th a.asc i').attr('class', 'fa fa-sort-down');
        $('th a.desc i').attr('class', 'fa fa-sort-up');

        $('.newplate').on('click', function() {
            var $this = $(this),
                    $detail = $('#detailModal'),
                    href = $this.data('modal');
            if (href)
            {
                $detail.attr('href', href).trigger('click');
            }
        })
        $('.download').on('click', function() {
            var $this = $(this),
                    $detail = $('#detailModal'),
                    href = $this.data('modal');
            if (href)
            {
                $detail.attr('href', href).trigger('click');
            }
        })
        $('.apply').on('click', function() {
            id = $(this).closest('td').data('id');
            $('#applyPlate').modal();
            $('#cancelApply').click(function() {
                status = 9;
                data = {id: id, status: status};
                ajaxChangeStatus(data);
            });
            $('#agreeApply').click(function() {
                status = 2;
                data = {id: id, status: status};
                ajaxChangeStatus(data);
            });
        })

        $('.confirm').on('click', function() {
            $('#confirmPlate').modal();
            id = $(this).closest('td').data('id');
            status = $(this).closest('td').data('status');
            if (status == 5) {
                $('#completeDistribution').attr('disabled', 'disabled');
            } else {
                $('#completeDistribution').removeAttr('disabled');
            }
            $('#cancelDistribution').click(function() {
                status = 3;
                data = {id: id, status: status};
                ajaxChangeStatus(data);
            });
            $('#completeDistribution').click(function() {
                status = 99;
                data = {id: id, status: status};
                ajaxChangeStatus(data);
            });
        })
    });
</script>
