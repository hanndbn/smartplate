<?php $id = $account['Management']['id']; ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('アカウント詳細') ?></h4>
        </div>
        <div class="modal-body">
            <div class="main-modal">
                <div class="clearfix">
                    <?php echo $this->Html->link(__('編集'), array('controller' => 'accounts', 'action' => 'edit', $id), array('class' => 'imgBtn wide hightlight-btn cf m-b-sm pull-right', 'data-toggle' => 'ajaxModal')); ?>
                </div>
                <div class="row m-b-sm">
                    <div class="col-sm-3"><span><?php echo __('種別') ?>:</span></div>                 
                    <div class="col-sm-7"><span><?php
                            $authority = $account['Management']['authority'];
                            switch ($authority) {
                                case '1':
                                    echo 'admin';
                                    break;
                                case '2':
                                    echo 'manager';
                                    break;
                                case '3':
                                    echo 'editor';
                                    break;
                            }
                            ?></span>
                    </div>
                    <div class="col-sm-2 text-right"><span><?php echo $account['Management']['status'] == 1 ? '<i class="fa fa-circle-thin"></i>' : '<i class="fa fa-times"></i>'; echo __('有効') ?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-3"><span><?php echo __('プロジェクト') ?>:</span></div>
                    <div class="col-sm-9"><span><?php echo ($team_name) ? Utility_Str::escapehtml($team_name['Team']['name']) : ''?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-3"><span><?php echo __('ログインID') ?>:</span></div>
                    <div class="col-sm-9"><span><?php echo Utility_Str::escapehtml($account['Management']['login_name']) ?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-3"><span><?php echo __('管理名') ?>:</span></div>
                    <div class="col-sm-9"><span><?php echo Utility_Str::escapehtml($account['Management']['name']) ?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-3"><span><?php echo __('コメント') ?>:</span></div>
                    <div class="col-sm-9"><span><?php echo Utility_Str::escapehtml($account['Management']['memo']) ?></span></div>
                </div>

            </div>
            <div class ="modal-footer p-l-none p-r-none">
                <div class="col-sm-3 text-left p-l-none"><span><?php echo __('最終更新日') ?>:</span></div>
                <div class="col-sm-6 text-left p-l-sm"><span><?php echo (isset($account['Team']['cdate'])) ? date('Y/m/d H:i:s', strtotime($account['Team']['cdate'])): '' ?></span></div>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('OK') ?></button>                  
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

