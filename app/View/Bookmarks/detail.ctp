<?php
$id = $bookmark['Bookmark']['id'];
$session = $this->Session->read('Auth.User');
$authority = $session['authority'];
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('コンテンツ詳細') ?></h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list">
                <?php if ($authority == 3) { ?>
                    <div class="of-none">
                        <?php echo $this->Html->link(__('編集'), array('controller' => 'bookmarks', 'action' => 'edit', $id), array('class' => 'imgBtn wide hightlight-btn cf m-b-sm pull-right', 'data-toggle' => 'ajaxModal')); ?>
                    </div>
                <?php } ?>
                <!---------Show chart---------------->
                <?php
                echo $this->element('Dashboard' . DS . 'accesslog_status', array('detail' => 1));
                echo $this->element('Dashboard' . DS . 'accesslog', array('detail' => 1));
                ?>
                <!----------End chart--------------------->
                <table class="table table-striped table-hover">
                    <tbody>   
                        <tr>
                            <th class="typeB"><?php echo __('ID'); ?></th>
                            <td><?php echo $id; ?></td>
                        </tr>
                        <tr>
                            <th class="typeB"><?php echo __('有効'); ?></th>
                            <td><?php echo $bookmark['Bookmark']['visible'] == 1 ? '<i class="fa fa-circle-thin">' : '<i class="fa fa-times">'; ?></td>
                        </tr>                                              
                        <tr>
                            <th class="typeB"><?php echo __('種別'); ?></th>
                            <td><?php echo (isset($bookmark['Type']['bookmark_type'])) ? $bookmark['Type']['bookmark_type'] : ''; ?></td>
                        </tr>  
                        <tr>
                            <th class="typeB"><?php echo __('名前'); ?></th>
                            <td><?php echo Utility_Str::escapehtml($bookmark['Bookmark']['name']); ?></td>
                        </tr>
                        <tr>
                            <th class="typeB"><?php echo __('ラベル'); ?></th>
                            <td> <?php
                                $_labels = $label['lb_name'];
                                $more = implode(', ', $_labels);
                                echo Utility_Str::wordTrim(Utility_Str::escapehtml($more), 50);
                                ?></td>
                        </tr>
                        <tr>
                            <th class="typeB"><?php echo __('バーコード'); ?></th>
                            <td><?php echo $bookmark['Bookmark']['code'] ?></td>
                        </tr>  
                        <tr>
                            <th class="typeB" style="vertical-align: middle;"><?php echo __('リンク先'); ?></th>
                            <td>
                                <?php if($linkUrls){
                                    foreach($linkUrls as $linkUrl){
                                        $title = ($linkUrl['link_text']) ? $linkUrl['link_text'].'： ' : '';
                                        echo $title.$this->Html->link($linkUrl['url'], $linkUrl['url'], array('div' => false, 'target' => '_blank')).'<br/>';
                                    }
                                }?>
                            </td>
                        </tr>                                            
                        <tr>
                            <th class="typeB" style="vertical-align: middle;"><?php echo __('プレート'); ?></th>
                            <td>
                                <?php if($plates){
                                    foreach($plates as $plate){
                                        echo $plate['tag'].'　';
                                    }
                                }?>
                            </td>
                        </tr>                                          
                        <tr>
                            <th class="typeB"><?php echo __('更新日時'); ?></th>
                            <td><?php echo date('Y/m/d H:i:s', strtotime($bookmark['Bookmark']['cdate'])); ?></td>
                        </tr>
                        <tr>
                            <th class="typeB"><?php echo __('位置情報'); ?></th>
                            <td><?php echo $bookmark['Bookmark']['gps'] == 1 ? '<i class="fa fa-circle-thin">' : '<i class="fa fa-times">'; ?></td>
                        </tr>
                        <tr>
                            <th class="typeB" style="vertical-align: middle;"><?php echo __('画像'); ?></th>
                            <td><?php echo (!empty($bookmark['Bookmark']['image'])) ? '<img  class="img-thumbnail" src="' . Bookmark::imageURL($bookmark['Bookmark']['image']) . '"/>' : ''; ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

