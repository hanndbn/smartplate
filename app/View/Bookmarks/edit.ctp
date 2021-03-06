<?php
/* Check action */
$action = $this->action;
$session = $this->Session->read('Auth.User');
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
                <span aria-hidden="true">&times;</span>
                <span class="sr-only">Close</span>
            </button>
            <h4 class="modal-title"><?php
echo ($action == 'edit') ? __('コンテンツ修正') : __('コンテンツ新規登録');
            ?></h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list modal-edit">
                <?php
                echo $this->Form->create('Bookmark', array('type' => 'file', 'class' => 'form-horizontal'));
                ?>
                <div class="of-none">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?php if ($action == 'edit') {
                                ?>
                                <div class="control-label col-sm-3">
                                    <span><?php echo __('ID')
                                        ?>:</span>
                                </div>
                                <div class="detail-id-content col-sm-2">
                                    <div class="checkbox">
                                        <?php echo $bookmark['Bookmark']['id'];?>
                                    </div>
                                </div>
                                <div class="control-label col-sm-2">
<!--                                    <span>--><?php //echo __('種別');?><!--:</span>-->
                                </div>
                                <div class="text-right col-sm-5">
                                    <?php
//                                    echo $this->Form->input("Bookmark.kind", array('label' => false, 'options' => $sp_type, 'div' => false, 'default' => $cr_type));
                                    ?>
                                </div>
                                <?php } else {?>
                                <div class="control-label col-sm-3">
                                    <span><?php echo __('種別');?>:</span>
                                </div>
                                <div class="text-right col-sm-4">
                                    <?php
                                    echo $this->Form->input("Bookmark.kind", array('label' => false, 'options' => $sp_type, 'div' => false));
                                    ?>
                                </div>
                                <?php }?>
                            </div>
                            <div class="form-group">
                                <div class="control-label col-sm-3">
                                    <span><?php echo __('名前')
                                        ?>:</span>
                                </div>
                                <div class="detail-name-content col-sm-9">
                                    <?php echo $this->Form->input('Bookmark.name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'data-toggle' => 'checklengh'));?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--
                    <?php if ($action == 'add') { ?>
                    <div class="row">
                    <div class="col-sm-9">
                    <div class="form-group">
                    <div class="control-label col-sm-2">
                    <span><?php echo __('リンク先：') ?></span>
                    </div>
                    <div class="detail-url-content col-sm-10">
                    <?php echo $this->Form->input('Bookmark.url', array('div' => false, 'type' => 'text', 'label' => FALSE, 'value' => '')); ?>
                    </div>
                    </div>
                    </div>
                    </div>
                    <?php
                    } else {
                    echo $this->Form->input('Bookmark.url', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'value' => ""));
                    }
                    ?>
                    -->
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="control-label col-sm-3">
                                    <span><?php echo __('バーコード')
                                        ?>:</span>
                                </div>
                                <div class="detail-code-content col-sm-9">
                                    <?php echo $this->Form->input('Bookmark.code', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '64', 'data-toggle' => 'checklengh'));?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="detail-visible-label control-label col-sm-3">
                                    <span><?php echo __('有効')
                                        ?>:</span>
                                </div>
                                <div class="detail-visible-content col-sm-2">
                                    <div class="checkbox">
                                        <?php
                                        if ($action == 'edit') {
                                            echo $this->Form->input('Bookmark.visible', array('div' => false, 'class' => 'm-n', 'type' => 'checkbox', 'label' => FALSE));
                                        } else {
                                            echo $this->Form->input('Bookmark.visible', array('div' => false, 'class' => 'm-n', 'type' => 'checkbox', 'checked' => true, 'label' => FALSE));
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="detail-gps-label control-label col-sm-4">
                                    <span><?php echo __('位置情報取得')
                                        ?>:</span>
                                </div>
                                <div class="detail-gps-content col-sm-2">
                                    <div class="checkbox">
                                        <?php echo $this->Form->input('Bookmark.gps', array('div' => false, 'type' => 'checkbox', 'class' => 'm-n', 'label' => FALSE));?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="control-label col-sm-3">
                                    <span><?php echo __('リダイレクトタイプ')
                                        ?>:</span>
                                </div>
                                <div class="control-label col-sm-4">
                                    <?php
                                    $options = array(__('通常'), __('OS'), __('タイルズ'), __('ランダム'), __('ローテーション'), __('TIME Range'));
                                    echo $this->Form->input("Bookmark.linkType", array('label' => false, 'options' => $options, 'div' => false, 'default' => (isset($linkType)) ? $linkType : ''));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group" style="margin-top: -70px;">
                                <div class="control-label col-sm-3">
                                </div>
                                <div class="detail-image-content col-sm-8">
                                    <div style="width: 110px;text-align: center;"><span>Icon</span></div>
                                    <div class="icon image_contenner">
                                        <?php
if ($action == 'edit') {
    if(!empty($bookmark['Bookmark']['image'])){
        if(strpos($bookmark['Bookmark']['image'], 'icon') !== false){
            echo '<img style = "background-color: #46AFFA" class="avatar" src="' . $bookmark['Bookmark']['image'] . '"/><img id="image_delete" src="/img/delete.png" height="20px"/>';
        }else{
            echo '<img  class="avatar" src="' . Bookmark::imageURL($bookmark['Bookmark']['image']) . '"/><img id="image_delete" src="/img/delete.png" height="20px"/>';
        }
    } else {
      echo '<img class="avatar"/><img id="image_delete" src="/img/delete.png" height="20px"/>';
    }
} else {
echo ' <img src="" class="avatar"/><img id="image_delete" src="/img/delete.png" height="20px"/>';
}
                                        echo $this->Form->input('Bookmark.image', array('type' => 'file', 'id' => "BookmarkImageUpload", 'div' => false, 'label' => false, 'class' => 'm-b-sm'));
                                        echo $this->Form->input('Bookmark.icon', array('div' => false, 'type' => 'hidden', 'id' => "BookmarkIcon"));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="left-block col-sm-6">
                        <div class="clearfix">
                            <span><?php echo __('リンク')
                                ?>:</span>
                            <?php echo $this->Html->link(__('クリア'), '#', array('style' => 'margin-top: -10px;', 'id' => 'resetLink', 'class' => 'imgBtn pull-right', 'div' => false, 'label' => FALSE));?>
                        </div>
                        <div class="control-label-content p-sm">
                            <div class="link-content">
                                <div class="redirectType0">
                                    <div class="row form-group">
                                        <div class="sub-type control-label col-sm-4 text-left">
                                            <span>URL</span>
                                        </div>
                                        <div class="url col-sm-8">
                                            <?php echo $this->Form->input("Link.url", array('div' => false, 'label' => false, 'name' => "data[Link][url]", 'type' => 'text', 'value' => isset($linkType0['Link']['url']) ? $linkType0['Link']['url'] : '', 'maxlength' => '1024'));?>
                                        </div>
                                    </div>
                                </div>
                                <div class="redirectType1">
                                    <?php
$nameOS = array('1'=>'Android', '2'=>'iOS', '9'=>'Other');
foreach ($nameOS as $key => $os) {
                                    ?>
                                    <div class="row form-group">
                                        <div class="sub-type control-label col-sm-4 text-left">
                                            <span><?php echo $os;?></span>
                                        </div>
                                        <div class="url col-sm-8">
                                            <?php echo $this->Form->input("Link." . $os . "Url", array('div' => false, 'label' => false, 'name' => "data[Link][OS][" . $key . "]", 'type' => 'text', 'value' => isset($linkType1[$key]['Link']['url']) ? $linkType1[$key]['Link']['url'] : '', 'maxlength' => '1024'));?>
                                        </div>
                                    </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="redirectType2">
                                    <div class="form-group">
                                        <div class="control-label col-sm-3">
                                            <span><?php echo __('タイトル')
                                                ?>:</span>
                                        </div>
                                        <div class="detail-code-content col-sm-12">
                                            <?php echo $this->Form->input("BookmarkExtData.title", array('div' => false, 'label' => false, 'type' => 'text', 'value' => isset($bookmarkExtData['title']) ? $bookmarkExtData['title'] : ''));?>
                                        </div>
                                    </div>

                                    <!--image title-->
                                    <div class="col-sm-12">
                                        <div class="form-group" style = "border: 1px solid #ccc;">
                                            <div class="control-label col-sm-2">
                                    <span><?php echo __('画像')
                                        ?>:</span>
                                            </div>
                                            <div class="detail-header-image-content col-sm-10">
                                                <?php
                                                echo $this->Form->input('BookmarkExtData.title_header_image', array('type' => 'file', 'id' => "BookmarkHeaderImageUpload", 'div' => false, 'label' => false, 'class' => 'm-b-sm'));
                                                ?>
                                                <div class="image_header_contenner">
                                                    <?php
                                                    if ($action == 'edit') {
                                                        echo (isset($bookmarkExtData['title_header_image']) && !empty($bookmarkExtData['title_header_image'])) ? '<img  class="avatarHeader edit img-thumbnail" src="' . Bookmark::imageURL($bookmarkExtData['title_header_image']) . '"/><img id="image_header_delete" src="/img/delete.png" height="20px"/>' : '<img class="avatarHeader"/>';
                                                    } else {
                                                        echo ' <img src="" class="avatarHeader"/>';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--image title END-->

                                    <?php
for ($i = 0; $i < 8; $i++) {
                                    ?>
                                    <table id="editBookmarkTable" class="table">
                                        <tbody>
                                            <tr>
                                                <td class="text-center id-column"><?php echo($i+1)
                                                ?></td>
                                                <td>
                                                <div class="url col-sm-12">
                                                    <?php echo $this->Form->input("Link" . ($i) . "title", array('div' => false, 'label' => 'Button title', 'name' => "data[Link][Btn][" . ($i) . "][title]", 'type' => 'text', 'value' => isset($linkType2[$i]['Link']['link_text']) ? $linkType2[$i]['Link']['link_text'] : '', 'maxlength' => '1024'));?>
                                                </div>
                                                <div class="url col-sm-12">
                                                    <?php echo $this->Form->input("Link" . ($i) . "url", array('div' => false, 'label' => 'Url', 'name' => "data[Link][Btn][" . ($i) . "][url]", 'type' => 'text', 'value' => isset($linkType2[$i]['Link']['url']) ? $linkType2[$i]['Link']['url'] : '', 'maxlength' => '1024'));?>
                                                </div></td>
                                                <td class="icon text-center" data-number="<?php echo($i) ?>">
                                                    <label for="BookmarkLink2icon" style="display: block">Icon</label>
                                                    <?php
                                                    if(isset($linkType2[$i]['Link']['icon']) && $linkType2[$i]['Link']['icon'] != "0"){
                                                        echo '<img id = "img-thumbnail_'. $i .'" class="img-thumbnail" src="/img/icon/thumb/' . $linkType2[$i]['Link']['icon'] . '.png" />';
                                                    } elseif(isset($linkType2[$i]['Link']['image']) && !empty($linkType2[$i]['Link']['image'])){
                                                        echo '<img style = "border: none;background-color: transparent;"id = "img-thumbnail_'. $i .'" class="img-thumbnail" src="'. Bookmark::imageURL($linkType2[$i]['Link']['image']) . '" />';
                                                    } else{
                                                        echo '<img class="default img-thumbnail" id = "img-thumbnail_'. $i .'" />';
                                                    }
                                                    echo $this->Form->input('Link.image'.($i), array('type' => 'file', 'style' => 'width:90px;', 'class' => 'LinkImageUpload', 'id' => "LinkImageUpload_$i", 'name' => "data[Link][Btn][" . ($i) . "][image]", 'div' => false, 'label' => false));
                                                ?></td>
                                                <?php echo $this->Form->input('Link.icon.' . ($i), array('div' => false, 'type' => 'hidden', 'name' => "data[Link][Btn][" . ($i) . "][icon]", 'value' => ((isset($linkType2[$i]['Link']['icon']) && $linkType2[$i]['Link']['icon'] != '0') ? $linkType2[$i]['Link']['icon'] . '.png' : ''), 'id' => "LinkIcon_$i"));?>

                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php }?>
                                </div>
                                <div class="redirectType3">
                                    <?php
for ($i = 1; $i <= 8; $i++) {
                                    ?>
                                    <table id="editBookmarkTable" class="table">
                                        <tbody>
                                            <tr>
                                                <td class="text-center id-column"><?php echo($i)
                                                ?></td>
                                                <td>
                                                <div class="url col-sm-12">
                                                    <?php echo $this->Form->input("Link" . $i . "url", array('div' => false, 'label' => 'Url', 'name' => "data[Link][random][" . $i . "][url]", 'type' => 'text', 'value' => isset($linkType3[$i - 1]['Link']['url']) ? $linkType3[$i - 1]['Link']['url'] : '', 'maxlength' => '1024'));?>
                                                </div></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php }?>
                                </div>
                                <div class="redirectType4">
                                    <?php
                                    for ($i = 1; $i <= 8; $i++) {
                                        ?>
                                        <table id="editBookmarkTable" class="table">
                                            <tbody>
                                            <tr>
                                                <td class="text-center id-column"><?php echo($i)
                                                    ?></td>
                                                <td>
                                                    <div class="url col-sm-12">
                                                        <?php echo $this->Form->input("Link" . $i . "url", array('div' => false, 'label' => 'Url', 'name' => "data[Link][rotate][" . $i . "][url]", 'type' => 'text', 'value' => isset($linkType4[$i - 1]['Link']['url']) ? $linkType4[$i - 1]['Link']['url'] : '', 'maxlength' => '1024'));?>
                                                    </div></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    <?php }?>
                                </div>
                                <div class="redirectType5">
                                    <?php
for ($i = 1; $i <= 8; $i++) {
                                    ?>
                                    <table id="editBookmarkTable" class="table">
                                        <tbody>
                                            <tr>
                                                <td class="text-center id-column" style="border: solid 1px #ccc !important"><?php echo($i)
                                                ?></td>
                                                <td style="width: 35%;margin: 0;padding: 10px 0 0 5px;">
                                                    <div class="form-group">
                                                        <div class="control-label col-sm-3">
                                                            <span><?php echo __('Start') ?>:</span>
                                                        </div>
                                                        <div class="detail-code-content col-sm-9">
                                                            <?php echo $this->Form->input("Link" . $i . "start_date", array('div' => false, 'label' => false, 'name' => "data[Link][time][" . $i . "][start_date]", 'type' => 'text', 'value' => isset($linkType5[$i - 1]['Link']['start_date']) ? $linkType5[$i - 1]['Link']['start_date'] : '', 'class' => 'start_date'));?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="control-label col-sm-3">
                                                            <span><?php echo __('End') ?>:</span>
                                                        </div>
                                                        <div class="detail-code-content col-sm-9">
                                                            <?php echo $this->Form->input("Link" . $i . "end_date", array('div' => false, 'label' => false, 'name' => "data[Link][time][" . $i . "][end_date]", 'type' => 'text', 'value' => isset($linkType5[$i - 1]['Link']['end_date']) ? $linkType5[$i - 1]['Link']['end_date'] : '', 'class' => 'end_date'));?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: baseline;">
                                                <div class="url col-sm-12">
                                                    <?php echo $this->Form->input("Link" . $i . "url", array('div' => false, 'label' => 'Url', 'name' => "data[Link][time][" . $i . "][url]", 'type' => 'text', 'value' => isset($linkType5[$i - 1]['Link']['url']) ? $linkType5[$i - 1]['Link']['url'] : '', 'maxlength' => '1024'));?>
                                                </div></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php }?>
                                </div>
                            </div>
                            <?php echo $this->Form->input('Link.clear', array('div' => false, 'type' => 'hidden', 'value' => 0));?>
                            <?php echo $this->Form->input('Link.type', array('div' => false, 'type' => 'hidden'));?>
                        </div>
                    </div>
                    <div class="right-block col-sm-6" style="padding-left: 0px;">
                        <div class="control-label-title">
                            <span><?php echo __('ラベル')
                                ?>:</span>
                            <?php echo $this->Html->link('クリア', '#', array('style' => 'display:none; margin-top: -10px;', 'id' => 'resetLabel', 'class' => 'imgBtn pull-right', 'div' => false, 'label' => FALSE));?>
                        </div>
                        <div class="control-label-content">
                            <div class="row">
                                <div class="control-label-list col-sm-6">
                                    <div class="dropdown m-b-sm _strip">
                                        <a id="List_label" role="button" data-toggle="dropdown" class="btn form-control" data-target="#"> <?php echo __('ラベルを選択')
                                        ?></a>
                                        <span class="caret"></span>
                                        <?php echo $this->Label->renderDropdownLabels($list_lb);?>
                                    </div>
                                </div>
                                <?php echo $this->Form->input("Label.new_label", array('label' => false, 'type' => 'hidden'));?>
                                <!--------Add new Label----------->
                                <div class="control-label-text col-sm-6" style="padding-left: 0px;">
                                    <?php
                                    if ($session['authority'] == 3) {
                                        echo $this->Form->input('Label.add_new_text', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '64'));
                                    } else {
                                        echo $this->Form->input('Label.add_new_text', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'maxlength' => '64'));
                                    }
                                    ?>
                                </div>
                                <!--------End---------->
                            </div>
                            <?php if ($action == 'edit') {
                            ?>
                            <div class="control-label-own col-sm-5">
                                <?php foreach ($cr_label as $lb_id) {
                                ?>
                                <div class="row">
                                    <div class="dropdown m-b-sm _strip">
                                        <a id="Label_dropdowm_<?php echo $lb_id ?>" role="button" data-toggle="dropdown"
                                        class="btn form-control" data-target="#" href="/page.html"> <?php echo __('ラベルを選択')
                                        ?></a>
                                        <?php echo $this->Label->renderEditDropdownLabels($list_lb, $lb_id);?>
                                    </div>
                                </div>
                                <?php }?>
                            </div>
                            <?php echo $this->Form->input("Label.clearLabel", array('label' => false, 'type' => 'hidden'));?>
                            <?php }?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer m-t-md">
                    <button type="submit" class="btn btn-default" id="btnSubmit">
                        <?php echo __('OK')
                        ?>
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?php echo __('Cancel')
                        ?>
                    </button>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div><!-- /.modal-content -->
<!-----modal icon--------->
<div id="modalIcon" class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span
                aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Icon</h4>
        </div>
        <div class="modal-body">
            <div class="modal-content">
                <div class="listicon jumbotron">
                    <?php
                    foreach ($listIcon as $icon) {
                        echo '<span data-icon ="' . $icon . '">' . '<img class="img-thumbnail" src="/img/icon/thumb/' . $icon . '" />' . '</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
                Cancel
            </button>
            <button type="button" id="applyIcon" class="btn btn-primary">
                Apply
            </button>
        </div>
    </div>
    <!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script type="text/javascript">
    $(document).ready(function () {
var typeIconSelect;
var linkTypeVal = $('#BookmarkLinkType').val();
switch (linkTypeVal) {
case "0": $('#LinkType').val('normal'); break;
case "1": $('#LinkType').val('os');     break;
case "2": $('#LinkType').val('button'); break;
case "3": $('#LinkType').val('random'); break;
case "4": $('#LinkType').val('rotate'); break;
case "5": $('#LinkType').val('time'); break;
default:
break;
}
for( i=0; i<=5; i++){
if( $('.redirectType'+i) )
$('.redirectType'+i).hide();
}
$('.redirectType'+linkTypeVal).show();
$('#BookmarkLinkType').change(function () {
var $val = $(this).val();
switch ($val) {
case "0": $('#LinkType').val('normal'); break;
case "1": $('#LinkType').val('os');     break;
case "2": $('#LinkType').val('button'); break;
case "3": $('#LinkType').val('random'); break;
case "4": $('#LinkType').val('rotate'); break;
case "5": $('#LinkType').val('time'); break;
default:
break;
}
for( i=0; i<=5; i++){
if( $('.redirectType'+i) )
$('.redirectType'+i).hide();
}
$('.redirectType'+$val).show();
});

$('#resetLink').click(function () {
$('.link-content input').val('');
$('.link-content img').attr('src', '').addClass('default');
//            $('#LinkClear').val(1);
});

$('#image_delete').click(function (e) {
    e.preventDefault();
    $(".avatar").attr({"src" : "", style: ""})
    $("#image_deleted").remove();
    $(".image_contenner").append('<input type="hidden" id="image_deleted" name="data[Bookmark][image_deleted]" value=1 />');
    $("#BookmarkImageUpload").val("");
    $("#BookmarkIcon").val("");

});
$('#image_header_delete').click(function (e) {
    $('.image_header_contenner').empty().html('<img class="avatarHeader"/><input type="hidden" name="data[BookmarkExtData][image_header_deleted]" value=1 />');
});
$('.label_edit').each(function () {
var $this = $(this),
value = $this.attr('data-value');

if (value) {
$('#Label_dropdowm_' + value).html($(".label_edit[data-id=" + value + "]").html());
}
});
$('.label_name').click(function (e) {
var id = $(this).attr('data-id');
$('input#LabelNewLabel').val(id);
$('#List_label').empty().html($(this).html());
});
$('.label_edit').click(function (e) {
e.preventDefault();
var id = $(this).attr('data-id');
var value = $(this).attr('data-value');
var selector = $("input#InputLabel_" + value).val(id);
$('#Label_dropdowm_' + value).empty().html($(this).html());
});

var checked = $('.control-label-own .row');
if (checked.length) {
$('#resetLabel').show();
} else {
$('#resetLabel').hide();
}

$('#resetLabel').click(function (e) {
$('.control-label-own').hide();
$('#LabelClearLabel').val(1);
$(this).hide();
});

$('#BookmarkImageUpload').on('change', function () {
imageURL(this);
});

$('.LinkImageUpload').on('change', function () {
    imageIconURL(this);
});

$('#BookmarkHeaderImageUpload').on('change', function () {
    imageHeaderURL(this);
});

var canUpload = true;

$('#BookmarkAddForm').on('submit', function (e) {
if (!canUpload) {
e.preventDefault();
}
});

function imageURL(input) {
var url = input.value,
ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase(),
_validFileExtensions = ["jpg", "jpeg", "bmp", "gif", "png"];

canUpload = true;

if ($('#BookmarkImageUploadError').length) {
$('#BookmarkImageUploadError').empty().remove();
}

if ($.inArray(ext, _validFileExtensions) == -1) {
error = '<span id="BookmarkImageUploadError" style="color: red;">* The selected file is not valid.</span>';

$('.detail-image-content').append(error);

canUpload = false;

return;
}

if (input.files && input.files[0]) {
var reader = new FileReader();

reader.onload = function (e) {
    $('.avatar').attr({
        src: e.target.result,
        width: '110',
        height : '110',
        style: ''});
    $("#BookmarkIcon").val('');
};

reader.readAsDataURL(input.files[0]);
}
}
function imageIconURL(input) {
    var id = input.id.split("_")[1];
    var url = input.value,
        ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase(),
        _validFileExtensions = ["jpg", "jpeg", "bmp", "gif", "png"];

    canUpload = true;

    if ($('#LinkImageUploadError_'+ id).length) {
        $('#LinkImageUploadError_'+ id).empty().remove();
    }

    if ($.inArray(ext, _validFileExtensions) == -1) {
        error = '<span id="LinkImageUploadError_" style="color: red;">* The selected file is not valid.</span>';

        $('#LinkImageUploadError_'+ id).append(error);

        canUpload = false;

        return;
    }

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#img-thumbnail_'+ id).attr({
                src: e.target.result,
                width: '80px',
                height : '80px',
                style : 'border: none; padding: 0px;background-color: transparent;'});
            $("#LinkIcon_" + id).val('');
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function imageHeaderURL(input) {
    var url = input.value,
        ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase(),
        _validFileExtensions = ["jpg", "jpeg", "bmp", "gif", "png"];

    canUpload = true;

    if ($('#BookmarkHeaderImageUploadError').length) {
        $('#BookmarkHeaderImageUploadError').empty().remove();
    }

    if ($.inArray(ext, _validFileExtensions) == -1) {
        error = '<span id="BookmarkHeaderImageUploadError" style="color: red;">* The selected file is not valid.</span>';

        $('.detail-header-image-content').append(error);

        canUpload = false;

        return;
    }

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('.avatarHeader')
                .attr('src', e.target.result)
                .width('200')
                .height('100');
        };

        reader.readAsDataURL(input.files[0]);
    }
}

$('.icon').on("click", "img", function () {
var $this = $(this),
$remote = $('#modalIcon'),
id = $this.data('id');
if(this.id == 'image_delete'){
    return;
}
if ($('#ajaxSubModal').length) {
$modal = '';
}
else {
$modal = $('<div class="modal fade" id="ajaxSubModal" tabindex="-1" role="dialog"></div>');
$remote.show();
$modal.html($remote);
$modal.on('hidden.bs.modal', function (event) {
$(this).removeClass('fv-modal-stack');
$('body').data('fv_open_modals', $('body').data('fv_open_modals') - 1);
});

$modal.on('shown.bs.modal', function (event) {
// keep track of the number of open modals
if (typeof($('body').data('fv_open_modals')) == 'undefined') {
$('body').data('fv_open_modals', 0);
}
// if the z-index of this modal has been set, ignore.
if ($(this).hasClass('fv-modal-stack')) {
return;
}
$(this).addClass('fv-modal-stack');
$('body').data('fv_open_modals', $('body').data('fv_open_modals') + 1);
$(this).css('z-index', 1041 + (10 * $('body').data('fv_open_modals')));
$('.modal-backdrop').not('.fv-modal-stack')
.css('z-index', 1039 + (10 * $('body').data('fv_open_modals')));
$('.modal-backdrop').not('fv-modal-stack')
.addClass('fv-modal-stack');
});
}
if($(this).attr("class") == "avatar"){
    typeIconSelect = $(this).attr("class");
} else {
    typeIconSelect = $(this).closest('td').data('number');
}
var className =  $(this).attr("className");
$('body').append($modal);
$('#ajaxSubModal').modal({
show: true
});
})

// Selected icon
$('.listicon img').on("click", function () {
$('.listicon img.selected').removeClass('selected');
$(this).addClass('selected');

});

// Apply icon
$('#applyIcon').on('click', function () {
var iconSelected = $('.listicon img.selected'),
iconName,
imgPath,
imgTag;
var baseUrl = '<?php echo $this->webroot ?>';
    if(iconSelected.length > 0) {
        iconName = iconSelected.closest('span').data('icon');
        if(typeIconSelect == "avatar") {

            $("#BookmarkIcon").val(baseUrl + 'img/icon/thumb/' + iconName);
            $("#BookmarkImageUpload").val('');
            $(".avatar").attr({'src' : baseUrl + 'img/icon/thumb/' + iconName}).css({'background-color': '#46AFFA', 'padding': '5px', 'width' : '110px', 'height' : '110px', 'border-radius' : '4px'});
        } else {
            imgPath = $('.icon[data-number|=' + typeIconSelect + ']');
            imgPath.find('img').attr({"src": baseUrl + 'img/icon/thumb/' + iconName, class:'img-thumbnail', style : 'background-color: #46AFFA'});
            $("#LinkImageUpload_"+ typeIconSelect).val("");
            $('#LinkIcon_' + typeIconSelect).val(iconName);
        }
    }
    typeIconSelect = null;
    $('#ajaxSubModal.in').modal('hide');
    });

// process datepicker
        $(".start_date").timepicker({
            showSeconds:  false,  // 秒まで
            disableFocus: true,
            defaultTime: '',
            showMeridian: false,
            disableMousewheel: false
        });
        $(".end_date").timepicker({
            showSeconds:  false,  // 秒まで
            disableFocus: true,
            defaultTime: '',
            showMeridian: false,
            disableMousewheel: false
        });
        $("#btnSubmit").click(function(e){
            e.preventDefault();
            var doSubmit = true;
            var timeArray = [];
            for(var i=1;i <=8; i++){
                var isValid = true;
                var error = '';
                var startDateSelector = $("#BookmarkLink" + i + "startDate");
                var endDateSelector = $("#BookmarkLink" + i + "endDate");
                if(startDateSelector.val() != '' && endDateSelector.val() != '') {
                    var startTimeInt = parseInt(startDateSelector.val().replace(":", ""));
                    var endTimeInt = parseInt(endDateSelector.val().replace(":",""));
                    var timestr = startTimeInt.toString().concat(endTimeInt.toString());
                    if(startTimeInt >= endTimeInt){
                        isValid = false;
                        error = 'Invalid!';
                    }
                    if(jQuery.inArray(timestr, timeArray) == -1){
                        timeArray.push(timestr);
                    } else {
                        isValid = false;
                        error = 'Duplicate!';
                    }
                }else if((startDateSelector.val() == '' && endDateSelector.val() != '') || (startDateSelector.val() != '' && endDateSelector.val() == '')){
                    isValid = false;
                    error = 'Invalid!';
                }

                if(!isValid){
                    doSubmit = false;
                    var errorSelector = $("#error" + i);
                    if(errorSelector.size() == 0) {
                        $(startDateSelector).before('<span id="error' + i + '" style="color: red">' + error + '</span>');
                    } else {
                        $("#error" + i).html(error);
                    }
                    $(startDateSelector).css("border","1px solid red");
                    $(endDateSelector).css("border","1px solid red");
                } else {
                    $("#error" + i).remove();
                    $(startDateSelector).css("border","");
                    $(endDateSelector).css("border","");
                }
            }
            if(doSubmit) {
                $("#BookmarkEditForm").submit();
                $("#BookmarkAddForm").submit();
            }
        });

        $("#avatarCss").remove() && $("<style type='text/css' id='avatarCss'> .avatar{ width:110px; height:110px;margin-bottom:5px; max-height: 110px !important; max-width: 110px !important; border-radius: 4px;} </style>").appendTo("head");
});
</script>
