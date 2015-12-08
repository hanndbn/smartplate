<?php 
$id = $tags['Tag']['id']; 
$session = $this->Session->read('Auth.User');
$authority = $session['authority'];
?>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('プレート詳細') ?></h4>
        </div>
        <div class="modal-body">
            <div class="main-modal">
                <div id="main" class="list">
                    <?php if($authority == 3){?>
                    <div class="of-none">
                        <?php echo $this->Html->link(__('編集'), array('controller' => 'tags', 'action' => 'edit', $id), array('id' => 'editBtn', 'class' => 'imgBtn wide hightlight-btn cf m-b-sm pull-right', 'data-toggle' => 'ajaxModal')); ?>
                    </div>
                    <?php } ?>
                    <!---------Show chart---------------->
                    <?php
                    echo $this->element('Dashboard' . DS . 'accesslog_status', array('detail' => 1));
                    echo $this->element('Dashboard' . DS . 'accesslog', array('detail' => 1));
                    ?>
                    <!----------End chart--------------------->

                    <div class="row m-b-sm m-t-md">
                        <div class="col-sm-4"><span><?php echo __('チーム') ?>:</span></div>
                        <div class="col-sm-7"><span><?php echo (isset($team_name['Team']['name'])) ? Utility_Str::escapehtml($team_name['Team']['name']) : '' ?></span></div>
                        <div class="col-sm-1 text-right"><span><?php
                                echo $tags['Tag']['available'] == 1 ? '<i class="fa fa-circle-thin"></i>' : '<i class="fa fa-times"></i>';
                                echo __('有効')
                                ?></span></div>
                    </div>

                    <div class="row m-b-sm">
                        <div class="col-sm-4"><span><?php echo __('タグ識別子') ?>:</span></div>
                        <div class="col-sm-8"><span><?php echo Utility_Str::escapehtml($tags['Tag']['tag']) ?></span></div>
                    </div>

                    <div class="row m-b-sm">
                        <div class="col-sm-4"><span><?php echo __('名前') ?>:</span></div>
                        <div class="col-sm-8"><span><?php echo Utility_Str::escapehtml($tags['Tag']['name']) ?></span></div>
                    </div>

                    <div class="row m-b-sm">
                        <div class="col-sm-4"><span><?php echo __('アクティベーションコード') ?>:</span></div>
                        <div class="col-sm-8"><span><?php echo $tags['Tag']['activation_code'] ?></span></div>
                    </div>

                    <div class="row m-b-sm">
                        <div class="col-sm-4"><span><?php echo __('登録更新日') ?>:</span></div>
                        <div class="col-sm-8"><span><?php echo date('Y/m/d H:i:s', strtotime($tags['Tag']['cdate'])) ?></span></div>
                    </div>

                    <div class="row m-t-lg">
                        <div class="col-sm-6">
                            <span><?php echo __('リンク') ?>:</span>
                            <div class="content-detail-wrap p-sm">
                                <?php
                                if ($bookmarkContents != null) {
                                    foreach ($bookmarkContents as $bookmarkContent) {
                                        $type = $bookmarkContent['type'];
                                        $sub_type = $bookmarkContent['sub_type'];
                                        $url = $bookmarkContent['url'];
                                        switch ($type) {
                                            case '0':
                                                echo "<div class='row form-group'><div class='col-sm-3'></div>";
                                                echo "<div class='url col-sm-9'>" . $url . "</div></div>";
                                                break;
                                            case '1':
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
                                                echo "<div class='row form-group'><div class='col-sm-3 text-center'>" . $sub_type . "</div>";
                                                echo "<div class='url col-sm-9'>" . $url . "</div></div>";
                                                break;
                                            case '2':
                                              $sub_type += 1;
                                            case '3':
                                            case '4':
                                                echo "<div class='row form-group'><div class='col-sm-3 text-center'>" . $sub_type . "</div>";
                                                echo "<div class='url col-sm-9'>" . $url . "</div></div>";
                                                break;
                                        }
                                    }
                                }
                                ?>    
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <span><?php echo __('ラベル') ?>:</span>
                            <div class="content-detail-wrap p-sm"><span><?php echo Utility_Str::escapehtml($labels) ?></span></div>
                        </div>
                    </div>
                </div>
                <div class = "modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('OK') ?></button>                  
                </div>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

