<?php
$id = $invoice['id'];
$year = date('Y', strtotime($invoice['regist_date']));
$month = date('m', strtotime($invoice['regist_date']));
?>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('詳細') ?></h4>
        </div>
        <div class="modal-body">
            <div class="main-modal">              
                <div class="row m-b-sm">
                    <div class="col-sm-2 text-right"><span><?php echo __('ID') ?>:</span></div>
                    <div class="col-sm-7"><span><?php echo $id ?></span></div>   
                    <div class="col-sm-2 text-center fl_right">
                        <?php
                        echo $this->Html->link('PDF', array('controller' => 'pdfs', 'action' => 'createPdf', $id), array('class' => 'imgBtn wide hightlight-btn cf m-b-sm', 'target' => '_blank'));
                        ?>
                    </div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-2 text-right"><span><?php echo __('請求年月') ?>:</span></div>
                    <div class="col-sm-8"><span><?php echo $year . __('年') . $month . __('月') ?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-2 text-right"><span><?php echo __('金額') ?>:</span></div>
                    <div class="col-sm-8"><span><?php echo $invoice['price'] . __('円') ?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-2 text-right"><span><?php echo __('詳細') ?>:</span></div>
                    <div class="col-sm-3">
                        <span>
                            <?php
                            echo __('プロジェクト数');
                            echo (isset($invoice['team_id'])) ? '　' . implode(', ', $invoice['team_id']) : '';
                            ?>
                        </span>
                    </div>
                    <div class="col-sm-3">
                        <span>
                            <?php
                            echo __('稼働プレート');
                            echo (isset($invoice['tag'])) ? '　' . implode(', ', $invoice['tag']) : '';
                            ?>
                        </span>
                    </div>
                    <div class="col-sm-3">
                        <span>
                            <?php
                            $contents = (isset($invoice['contents'])) ? array_filter($invoice['contents']) : array();
                            echo __('稼働コンテンツ');
                            echo (!empty($contents)) ? '　' . implode(', ', $contents) : '';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class = "modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('OK') ?></button>                  
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

