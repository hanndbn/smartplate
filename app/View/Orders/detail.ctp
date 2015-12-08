<?php $id = $order['Order']['id'];?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('発注詳細') ?></h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list">
                <div class="clearfix">
                    <?php echo $this->Html->link('プレート申請', array('controller' => 'orders', 'action' => 'releasePlate', $id), array('class' => 'imgBtn wide hightlight-btn cf m-b-sm pull-right', 'data-toggle' => 'ajaxModal')); ?>
                </div>
                <table class="table table-striped table-hover">
                    <tbody>   
                        <tr>
                            <th class="typeB"><?php echo __('ID'); ?></th>
                            <td><?php echo $id; ?></td>
                        </tr>
                        <tr>
                            <th class="typeB"><?php echo __('計算'); ?></th>
                            <td><?php echo $order['Order']['count']; ?></td>
                        </tr>  
                        <tr>
                            <th class="typeB"><?php echo __('種類'); ?></th>
                            <td><?php echo $order['Order']['type']; ?></td>
                        </tr>
                        <tr>
                            <th class="typeB"><?php echo __('状態'); ?></th>
                            <td><?php echo $order['Order']['status'] ?></td>
                        </tr>                                       
                        <tr>
                            <th class="typeB"><?php echo __('依頼日'); ?></th>
                            <td><?php echo date('Y/m/d H:i:s', strtotime($order['Order']['request_date'])); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

