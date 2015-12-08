<?php $id = $team['Team']['id']; ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('プロジェクト詳細') ?></h4>
        </div>
        <div class="modal-body">
            <div class="main-modal">
                <div class="row">
                    <?php echo $this->Html->link(__('編集'), array('controller' => 'teams', 'action' => 'edit', $id), array('class' => 'imgBtn wide hightlight-btn cf m-b-sm m-r-sm pull-right', 'data-toggle' => 'ajaxModal')); ?>
                </div>
                <div class="row m-b-sm">
                    <div class="col-sm-4"><span><?php echo __('ID') ?>:</span></div>
                    <div class="col-sm-7"><span><?php echo $id ?></span></div>    
                    <div class="col-sm-1 text-right"><span><?php echo $team['Team']['valid'] == 1 ? '<i class="fa fa-circle-thin"></i>' : '<i class="fa fa-times"></i>'; echo __(' 有効') ?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-4"><span><?php echo __('プロジェクト名') ?>:</span></div>
                    <div class="col-sm-8"><span><?php echo Utility_Str::escapehtml($team['Team']['name']) ?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-4"><span><?php echo __('管理ユーザ') ?>:</span></div>
                    <div class="col-sm-8"><span><?php echo Utility_Str::escapehtml($manager_name)?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-4"><span><?php echo __('コメント') ?>:</span></div>
                    <div class="col-sm-8"><span><?php echo Utility_Str::escapehtml($team['Team']['comment']) ?></span></div>
                </div>

                <div class="row m-b-sm">
                    <div class="col-sm-4"><span><?php echo __('最終更新日') ?>:</span></div>
                    <div class="col-sm-8"><span><?php echo date('Y/m/d H:i:s', strtotime($team['Team']['cdate'])) ?></span></div>
                </div>

                <div class="row m-t-lg">                    
                    <div class="col-sm-6">
                        <span><?php echo __('プレート') ?>:</span>
                        <div class="content-detail-wrap p-sm">
                            <?php foreach ($tags as $tag) { ?>
                                <div class="row">
                                    <div class="tag-tag col-sm-5"><span><?php echo Utility_Str::escapehtml($tag['tag']) ?></span></div>
                                    <div class="tag-name col-sm-7"><span><?php echo Utility_Str::escapehtml($tag['name']) ?></span></div>
                                </div>
                            <?php } ?>                           
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <span><?php echo __('リンク') ?>:</span>
                        <div class="content-detail-wrap p-sm">
                            <?php
                            foreach ($list_url as $url) {
                                $sub_type = '';
                                $link = '';
                                if (isset($url)) {
                                    $sub_type = $url['sub_type'];
                                    $link = $url['url'];
                                    switch ($sub_type) {
                                        case '1':
                                            $sub_type = 'Android';
                                            break;
                                        case '2':
                                            $sub_type = 'IOS';
                                            break;
                                        case '3':
                                            $sub_type = 'Other';
                                            break;
                                        default:
                                            break;
                                    }
                                }
                                ?>
                                <div class="row">
                                    <div class="sub-type col-sm-5"><span><?php echo 'name ' . $sub_type ?></span></div>
                                    <div class="url col-sm-7"><span><?php echo Utility_Str::escapehtml($link) ?></span></div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class = "modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('OK') ?></button>                  
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

