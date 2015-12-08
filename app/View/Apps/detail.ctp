<?php $id = $user['id']; ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('ユーザー詳細') ?></h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list">
                <div class="clearfix">
                    <?php echo $this->Html->link(__('編集'), array('controller' => 'apps', 'action' => 'edit', $id), array('class' => 'imgBtn wide hightlight-btn cf m-b-sm pull-right', 'data-toggle' => 'ajaxModal')); ?>
                </div>
                <div class="row">
                    <div class="col-sm-7">
                        <div class="row m-b-md">
                            <div class="control-label col-sm-4 text-right">
                                <span><?php echo __('ログインID') ?>:</span>
                            </div>
                            <div class="detail-name-content col-sm-8">
                                <?php echo $user['login_name'] ?>
                            </div>
                        </div>
                        <div class="row m-b-md">
                            <div class="control-label col-sm-4 text-right">
                                <span><?php echo __('使用者名') ?>:</span>
                            </div>
                            <div class="detail-name-content col-sm-8">
                                <?php echo $user['name']; ?>
                            </div>
                        </div>                        
                        <div class="row m-b-md">
                            <div class="control-label col-sm-4 text-right">
                                <span><?php echo __('コメント') ?>:</span>
                            </div>
                            <div class="detail-url-content col-sm-8">
                                <?php echo $user['comment']; ?>
                            </div>
                        </div>
                        <div class="row m-b-md">
                            <div class="detail-visible-content col-sm-10 m-l-lg">
                                <div class="checkbox m-n">
                                    <?php echo $user['status'] ? '<i class="fa fa-circle-thin"></i>' : '<i class="fa fa-times"></i>' ?>
                                    <span class="m-l-sm"><?php echo __('有効') ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="detail-visible-content col-sm-10 m-l-lg">
                                <div class="checkbox m-n">
                                    <?php echo $user['power'] ? '<i class="fa fa-circle-thin"></i>' : '<i class="fa fa-times"></i>'; ?>
                                    <span class="m-l-sm"><?php echo __('アプリを管理モードで使用する') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-5" style="padding-left: 0px;">
                        <div class="control-label-title">
                            <span><?php echo __('ラベル') ?>:</span>
                        </div>
                        <div class="app-label-content">
                            <span><?php echo $labels ? Utility_Str::escapehtml(implode(', ', $labels)) : '' ?></span>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        <div class = "modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('OK') ?></button>                  
        </div>
    </div>
</div>

