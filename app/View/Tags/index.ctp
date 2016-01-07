<?php
$base_url = array('controller' => 'tags', 'action' => 'index');
$platinum = ($this->request->prefix == 'system') ? $this->requestAction(array('prefix' => 'system', 'controller' => 'accesslogs', 'action' => 'plateStatus')) : $this->requestAction(array('controller' => 'accesslogs', 'action' => 'plateStatus'));
$last_login = $this->requestAction(array('controller' => 'managements', 'action' => 'getLastLoggin'));
$session = $this->Session->read('Auth.User');
$authority = $session['authority'];
$team_id = ($this->request->prefix == 'system') ? '' : $session['team_id'];
?>
<div id="main" class="list">
<h2><?php echo __('プレート一覧') ?></h2>
<?php echo $this->Session->flash() ?>
<p class="paging">
    <?php
    if (isset($last_login['last_login'])) {
        $dates = $last_login['last_login']['Management']['last_login_date'];
        $date = new DateTime($dates);
        $year = $date->format('Y');
        $mon = $date->format('m');
        $day = $date->format('d');
        $hour = $date->format('H');
        $min = $date->format('i');
        $sec = $date->format('s');
        echo "{$year}-{$mon}-{$day} {$hour}:{$min}:{$sec}";
    }
    ?>
    <br>
    <?php echo __('稼働プレート数 当月') ?>:<span class="fs18 red"><?php echo $platinum['monthly'] ?></span>　
    <?php echo __('本日') ?>:<span class="fs18 red"><?php echo $platinum['daily'] ?></span> <br/>
    <?php echo __('今月のプラチナプレート（アクセス1000超）数') ?>:<span class="fs18 red"><?php echo $platinum['platinum'] ?></span>
</p>

<?php //echo $this->Form->create("QuickEdit", array('url' => $qedit_url, 'role' => "form", 'name' => 'Qform'));  ?>
<table class="operation">
    <tbody>
    <tr class="container-fluid">
        <input id="target_id" style="width: 200px;" type="hidden" name="data[Tag][id]">
        <td class="">
            <span class="text"><?php echo __('選択項目'); ?>:</span>
            <select id="selection" class="">
                <option selected="selected" value=""></option>
                <option value="0"><?php echo __('だけを表示'); ?></option>
                <option value="1"><?php echo __('以外を表示'); ?></option>
                <option value="3"><?php echo __('のコンテンツを変更する'); ?></option>
                <option value="2"><?php echo __('の名前を変更する'); ?></option>
                <?php if ($this->request->prefix != 'system') { ?>
                    <option value="4"><?php echo __('のラベルを変更する'); ?></option>
                <?php } ?>
                <?php if (($this->request->prefix == 'system') || $authority != 3) { ?>
                    <option value="8"><?php echo __('を割当てる'); ?></option>
                <?php } ?>
                <option value="6"><?php echo __('を無効・有効にする'); ?></option>
                <option value="9"><?php echo __('change image'); ?></option>
            </select>

                    <span id="changeName" style="display:none;" class="">　
                        <?php echo __('名前:'); ?>
                        <input id="newName" style="width: 200px;" type="text" name="data[Tag][name]" autocomplete="off"
                               maxlength="128" data-toggle='checklengh'>　
                    </span>

                    <span id="execute" style="display:none;">
                        <input id="newVisible" name="data[Tag][_available]" style="width: 200px;" type="hidden">　
                    </span>

                    <span id="changePB" style="display:none;" class="">　
                        <?php
                        if ($this->request->prefix == 'system') {
                            echo __('Admin User：');
                            echo $this->Form->input("Tag.pb", array('label' => false, 'options' => $adminUser, 'div' => false));
                        } else {
                            if ($authority == 1) {
                                echo __('Manager User：');
                            }else{
                                echo __('Project：');
                            }
                            echo $this->Form->input("Tag.pb", array('label' => false, 'options' => $listProject, 'div' => false));
                        }
                        ?>
                        <?php ?>
                    </span>
            <a id="quickAction-btn" style="display:none;" href="#" class="imgBtn wide m-l-sm"><?php echo __('設定') ?></a>
        </td>
        <td class="pull-right">
            <?php echo $this->Html->link(__('プレート申請'), array('controller' => 'orders', 'action' => 'orderRegist'), array('id' => 'dialog_order_open', 'class' => 'imgBtn wide', 'data-toggle' => 'ajaxModal')); ?>
        </td>
    </tr>
    </tbody>
</table>

<!--Paginator-->
<?php echo $this->element('pagination'); ?>

<!-- Show contents-->
<div class="table-hover">

    <table id="plate-list" class="table-striped text-center">
        <thead>
        <tr>
            <td class="search" colspan="12">
                <?php
                // The base url is the url where we'll pass the filter parameters

                echo $this->Form->create("Filter", array('url' => $base_url, 'role' => "form", 'style' => 'display:inline-block;width: 100%;'));
                // add a select input for each filter. It's a good idea to add a empty value and set
                // the default option to that.
                ?>

                <?php echo __('絞り込み条件') ?>:

                <div class="dropdown inline-block strip">
                    <a id="pLabel" role="button" data-toggle="dropdown" class="btn form-control" data-target="#"
                       href="#">
                        <?php echo __('ラベルを選択') ?>
                    </a>
                    <span class="caret"></span>
                    <?php echo $this->Label->renderDropdownLabels($list_lb); ?>
                </div>
                <?php echo $this->Form->input("label", array('label' => false, 'type' => 'hidden')); ?>
                <span class="plus">＋</span>

                <select id="FilterUser" class="" name="data[Filter][user]">
                    <option value=""><?php echo __('アカウント') ?></option>
                    <?php foreach ($list_user as $key => $value) { ?>
                        <option value="<?php echo $key ?>"
                                title="<?php echo Utility_Str::escapehtml($value) ?>"><?php echo __(Utility_Str::wordTrim(Utility_Str::escapehtml($value), 10)) ?></option>
                    <?php } ?>
                </select>
                ＋
                <?php
                // Add a basic search
                echo $this->Form->input("name", array('label' => false, 'placeholder' => "検索キー"));

                echo $this->Form->submit(__('検索'), array('class' => 'imgBtn wide m-sm', 'id' => 'searchId'));
                // To reset all the filters we only need to redirect to the base_url
                echo $this->Html->link(__('リセット'), $base_url, array('class' => 'imgBtn wide subFilter'));
                if ($this->request->prefix != 'system') {
                    if ($authority == 3) {
                        ?>
                        <a class="icon-label" href="<?php echo $this->Html->url(array('action' => 'label')); ?>"
                           title="">
                            <i class="fa fa-tags fa-2x"></i>
                        </a>
                    <?php
                    }
                }
                ?>
                <a href="<?= $this->Html->url(array('controller' => 'tags', 'action' => 'index', 'act:export')) ?>"
                	class='icon-label' title="csv download" onclick="alert('<?php echo __('ダウンロードされるファイルのフォーマットはUTF8です。') ?>');">
                    <i class="fa-2x fa fa-download"></i>
                </a>
<!--                --><?php
//                    echo $this->Form->end();
//                ?>
<!--                --><?php //echo $this->Form->create("FilterFollowDate", array('url' => $base_url, 'role' => "form", 'style' => 'display:inline-block; float:right')); ?>
                <div style="display:inline-block; float:right">
                    <?php
                        if(isset($this->request->data['Filter']['from'])) {
                            echo $this->Form->input("from", array('label' => false, 'type' => 'text', 'style' => 'width: 100px; height: 25px;', 'id' => 'from', 'value' => $this->request->data['Filter']['from']));
                        } else {
                            echo $this->Form->input("from", array('label' => false, 'type' => 'text', 'style' => 'width: 100px; height: 25px;', 'id' => 'from'));
                        }
                    ?>
                    <?php
                        if(isset($this->request->data['Filter']['to'])) {
                            echo $this->Form->input("to", array('label' => false, 'type' => 'text', 'style' => 'width: 100px; height: 25px;', 'id' => 'to', 'value' => $this->request->data['Filter']['to']));
                        } else {
                            echo $this->Form->input("to", array('label' => false, 'type' => 'text', 'style' => 'width: 100px; height: 25px;', 'id' => 'to'));
                        }
                    ?>
                    <?php  echo $this->Form->submit(__('確認'), array('style' => 'margin-right: 0px;', 'class' => 'imgBtn wide hightlight-btn m-sm', 'id' => 'datePickerBtn')); ?>
                </div>
                <?php echo $this->Form->end(); ?>

            </td>
        </tr>
        <tr>
            <th class="typeB highlight"><?php echo __('選択') ?>
                <br><?php echo $this->Form->checkbox('all', array('id' => 'masterbox', 'class' => 'CheckAll', 'data-target' => '#plate-list tbody')); ?>
            </th>
            <th class="typeB"><?php echo $this->Paginator->sort('available', __('有効')); ?>  </th>
            <th class="typeB"><?php echo $this->Paginator->sort('tag', __('ID')); ?></th>
            <th class="typeB highlight"><?php echo __('ラベル') ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('name', __('名前')); ?></th>
            <th class="typeB highlight"><?php echo __('配信コンテンツID'); ?></th>
            <th class="typeB highlight"><?php echo __('最新アクセス'); ?></th>
            <th class="typeB highlight"><?php echo __('当月アクセス数'); ?></th>
            <th class="typeB highlight"><?php echo __('NFC'); ?></th>
            <th class="typeB highlight"><?php echo __('QR'); ?></th>
            <th class="typeB highlight"><?php echo __('最新設定日'); ?></th>
            <th class="typeB highlight"><?php echo __('設定アカウント'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tags as $tag): ?>
            <?php $id = $tag['Tag']['id']; ?>
            <tr id="<?php echo 'toggle' . $id ?>" class="modal-detail"
                data-modal="<?php echo ($this->request->prefix == 'system') ? $this->webroot . 'system/tags/detail/' . $id : $this->webroot . 'tags/detail/' . $id ?>"
                data-pb="<?php echo ($this->request->prefix == 'system') ? $tag['Tag']['management_id'] : $tag['Tag']['team_id'] ?>">
                <td class="ignore-modal"><?php echo $this->Form->checkbox('Tag.id.' . $id, array('class' => 'check', 'value' => $id, 'hiddenField' => false)); ?></td>
                <td class="bm_available" data-value="<?php echo $tag['Tag']['available'] == '' ? 0 : 1 ?>">
                    <?php echo $tag['Tag']['available'] == 1 ? '<i class="fa fa-circle-thin">' : '<i class="fa fa-times">'; ?>
                </td>
                <td><?php echo $tag['Tag']['tag']; ?></td>
                <td class="_label" data-id="">
                    <?php
                    $_labels = $tag['lb_name'];
                    echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml(reset($_labels)) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml(reset($_labels)), 20) . '</span>';
                    if (count($_labels) > 1) {
                        $key = key($_labels);
                        unset($_labels[$key]);
                        $more = implode(', ', $_labels);
                        echo '<span data-toggle="tooltip" data-placement="bottom" title="' . $more . '">, ... </span>';
                    }
                    ?>
                </td>
                <td class="_name"
                    data-name="<?php echo Utility_Str::escapehtml($tag['Tag']['name']) ?>"><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($tag['Tag']['name']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($tag['Tag']['name']), 20) . '</span>'; ?></td>
                <td class="link_bm"><?php echo $tag['Tag']['bookmark_id']; ?></td>
                <td><?php echo ($tag['Access_cdate']) ? date('Y/m/d H:i:s', strtotime($tag['Access_cdate'])) : ''; ?></td>
                <td><?php echo $tag['Access_total']; ?></td>
                <td><?php echo $tag['nfc']; ?></td>
                <td><?php echo $tag['qr']; ?></td>
                <td><?php
                    if ($tag['Link_update'] != null) {
                        echo ($tag['Link_update']['Link']['udate']) ? date('Y/m/d H:i:s', strtotime($tag['Link_update']['Link']['udate'])) : '';
                    }
                    ?>
                </td>
                <td class="_username">
                    <?php
                    if ($tag['user_name'] != null) {
                        $_usernames = $tag['user_name'];
                        echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml(reset($_usernames)) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml(reset($_usernames)), 20) . '</span>';
                        if (count($_usernames) > 1) {
                            $key = key($_usernames);
                            unset($_usernames[$key]);
                            $more = implode(', ', $_usernames);
                            echo '<span data-toggle="tooltip" data-placement="bottom" title="' . $more . '">, ... </span>';
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php unset($tag); ?>
        </tbody>
    </table>

</div>

<!--Paginator-->
<?php echo $this->element('pagination'); ?>
</div>
<?php
echo $this->Html->link('Detail', array(), array('class' => 'invisible', 'id' => 'detailModal', 'data-toggle' => 'ajaxModal'));
echo $this->Html->link('label_edit', array(), array('class' => 'invisible', 'id' => 'quicklabelModal', 'data-toggle' => 'ajaxModal', 'data-href' => $this->Html->url(array('controller' => 'tags', 'action' => 'quickedit_label'))));
echo $this->Html->link('link_bm_edit', array(), array('class' => 'invisible', 'id' => 'link_bm_Modal', 'data-toggle' => 'ajaxModal', 'data-href' => $this->Html->url(array('controller' => 'tags', 'action' => 'quickedit_link_bm'))));
echo $this->Html->link('img_edit', array(), array('class' => 'invisible', 'id' => 'img_Modal', 'data-toggle' => 'ajaxModal', 'data-href' => $this->Html->url(array('controller' => 'changeimages', 'action' => 'quickedit_img'))));
?>
<script type="text/javascript">
function ajaxEdit(url, data) {
    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        success: function (rs) {
            location.reload();
            $('#selection').val('');
        }
    });
}

function notAllowMultiCheckbox(check) {
    if (check.length > 1) {
        $('#selection').val('');
        // alert('<?php echo __('複数のチェックボックスを選択することはできません。') ?>');
        // return false;
    }
    return true;
}


function resetFilter(resetSelection) {
    if (resetSelection) {
        $('#selection').val('');
    }

    $('.check').show();
    $('.check').closest('tr').show();
    $('#quickAction-btn').hide();
    $('#execute').hide();
    $('#changeName').hide();
    $('#changePB').hide();
}

var buildUrl = function (base, key, value) {
    var sep = (base.indexOf('?') > -1) ? '&' : '?';
    return base + sep + key + '=' + value;
}
function getIDs(checked) {
    var ids = [];
    checked.each(function () {
        ids.push($(this).val());
    });
    return ids;
}
$(document).ready(function () {
    $('th a').append(' <i class="fa fa-sort"></i>');
    $('th a.asc i').attr('class', 'fa fa-sort-down');
    $('th a.desc i').attr('class', 'fa fa-sort-up');

    // Get GET filter parameter
    $.urlParam = function (name) {
        var results = new RegExp('[\/]' + name + ":([^&#/]*)").exec(window.location.href);
        if (results == null) {
            return null;
        }
        else {
            return results[1] || 0;
        }
    }

    if ($.urlParam('user') != null) {
        $('#FilterUser').val($.urlParam('user').replace(/\%20/g, ' '));
    }
    if ($.urlParam('label') != null) {
        var label = $('.label_name[data-id*=' + $.urlParam('label').replace(/\%20/g, ' ') + ']').html();
        $('#pLabel').html(label);
    }

    // Get label selection id
    $('.label_name').click(function (e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        if ($(this).hasClass('edit')) {
            $('#myLabel').val(id);
            $('#dLabel').empty().html($(this).html());
        } else {
            $('#FilterLabel').val(id);
            $('#pLabel').empty().html($(this).html());
        }
    });

    $('.check, .CheckAll').on('change', function () {
//            resetFilter();
        var selection = parseInt($('#selection').val()),
            $checked = $('.check:checked');

        if (selection == 6 || selection == 7) {
            if ($(this).hasClass('CheckAll')) {
                if ($checked.length == $('.check').length) {
                    resetFilter(true);
                }
            }

            if (!$checked.length) {
                resetFilter(true);
            }
        }
        else {
            resetFilter(true);
        }
    });

    $('#selection').change(function (e) {
        e.preventDefault();

        var unchecked = $('.check:not(:checked)');
        var checked = $('.check:checked');
        var value = $("#selection").val();
        var ids;

        if (!checked.length) {
            resetFilter(true);
            alert('<?php echo __('少なくとも１つの項目を選択してください') ?>');
            return;
        }
        var $hrefredirect = '';

        switch (value) {
            case '0':
                resetFilter();
                unchecked.closest("tr").hide();
                break;
            case '1':
                resetFilter();
                checked.closest("tr").hide();

                break;
            case '2':
                resetFilter();
                var cr_name = checked.closest("tr").find('._name').data('name').toString().replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');
                (checked.length == 1) ? $('#newName').val(cr_name) : $('#newName').val('');
                $('#quickAction-btn').show();
                $('#changeName').show();
                break;
            case '3':
                resetFilter();
                if ($("#ajaxSubModal").length) $('#ajaxSubModal').remove();
                ids = getIDs(checked);
                var href = $('#link_bm_Modal').attr('data-href');
                if($('#masterbox').prop("checked")) {
                    $hrefredirect = href + '?selectall=1';
                }else{
                    $hrefredirect = href + '?id=' + ids;
                }
                $('#link_bm_Modal').attr('href', $hrefredirect).trigger('click');
                $('#selection').val('');
                break;
            case '4':
                resetFilter();
                ids = getIDs(checked);
                var href = $('#quicklabelModal').attr('data-href');
                if($('#masterbox').prop("checked")) {
                    $hrefredirect = href + '?selectall=1';
                }else{
                    $hrefredirect = href + '?id=' + ids;
                }
                $('#quicklabelModal').attr('href', $hrefredirect).trigger('click');
                $('#selection').val('');img_Modal
                break;
            case '5':
                resetFilter();
                $('#quickAction-btn').show();
                break;
            case '6':
                resetFilter();
                $('#quickAction-btn').show();
                break;
            case '7':
                resetFilter();
                $('#quickAction-btn').show();
                break;
            case '8':
                resetFilter();
                var cr_pb = checked.closest("tr").data('pb');
                (checked.length == 1) ? $('#TagPb').val(cr_pb) : $('#TagPb').val('');
                $('#changePB').show();
                $('#quickAction-btn').show();
                break;
            case '9':
                resetFilter();
                ids = getIDs(checked);
                var href = $('#img_Modal').attr('data-href');
                var hrefredirect;
                if($('#masterbox').prop("checked")) {
                    hrefredirect = href + '?selectall=1';
                }else{
                    hrefredirect = href + '?id=' + ids;
                }
                hrefredirect = hrefredirect + '&strtable=Tag';

                $('#img_Modal').attr('href', hrefredirect).trigger('click');
                $('#selection').val('');
                break;
            default :
                resetFilter(true);
                break;
        }
    });
    $('#quickAction-btn').click(function (e) {
        e.preventDefault();
        var now = new Date().getTime();
        var url = buildUrl('<?php echo $this->Html->url(array('controller' => 'tags', 'action' => 'quickEdit'), true); ?>', '_t', now);
        var filter_val = $('#selection').find(':selected').attr('value');
        var checked = $('.check:checked');
        var id = checked.val(),
            ids;
        var input = '';
        var data = [];
        var value = $("#selection").val();
        var selectall = '0';
        if($('#masterbox').prop("checked")) {
            selectall = '1';
        }
        switch (value) {
            case '2':
                input = $("#newName").val();
                if (input.replace(/ /g, '') == '') {
                    $.confirm({
                        text: '<?php echo __("このフィールドを入力してください。") ?>',
                        title: '<?php echo __("確認") ?>',
                        confirmButton: "",
                        cancelButton: "OK",
                        confirmButtonClass: 'btn-default hide'
                    });

                    return;
                }
                $.confirm({
                    text: '<?php echo __("「選択した項目の名前を変更します」") ?>',
                    title: '<?php echo __("確認") ?>',
                    confirm: function (confirmButton) {

                        ids = getIDs(checked);

                        data = {id: ids, type: filter_val, input: input, selectall:selectall};
                        ajaxEdit(url, data);
                    },
                    confirmButton: "Yes",
                    cancelButton: "No",
                    confirmButtonClass: 'btn-default',
                    post: true
                });
                break;
            case '3':
            case '4':
            case '5':
                break;
            case '6':
                ids = getIDs(checked);
                data = {ids: ids, type: filter_val, selectall:selectall};
                ajaxEdit(url, data);
                break;
            case '7':
                var _url = buildUrl('<?php echo $this->Html->url(array('controller' => 'tags', 'action' => 'delete'), true); ?>', '_t', now);
                $.confirm({
                    text: '<?php echo __("選択した項目を削除してもよろしいですか？") ?>',
                    title: '<?php echo __("確認") ?>',
                    confirm: function (confirmButton) {
                        ids = getIDs(checked);
                        var datas = {id: ids};
                        $.ajax({
                            type: 'POST',
                            dataType: 'JSON',
                            url: _url,
                            data: datas,
                            success: function (rs) {
                                location.reload();
                            }
                        });
                    },
                    confirmButton: "Yes",
                    cancelButton: "No",
                    confirmButtonClass: 'btn-default',
                    post: true
                });
                $('#selection').val('');
                break;
            case '8':
                var system;
                input = $("#TagPb").val();
            <?php if ($this->request->prefix == 'system') { ?>
                system = 1;
            <?php } else { ?>
                system = 0;
            <?php } ?>
                ids = getIDs(checked);
                data = {ids: ids, type: filter_val, system: system, input: input, selectall:selectall};
                ajaxEdit(url, data);
                break;
            default:
                break;
        }
    });

    $('.modal-detail').find('td').on('click', function () {
        var $this = $(this),
            $detail = $('#detailModal'),
            href = $this.closest('.modal-detail').data('modal');

        if ($this.hasClass('ignore-modal')) {
            return;
        }

        if (href) {
            $detail.attr('href', href).trigger('click');
        }
    })

    <?php if ($authority == 2 || $authority == 1 || $this->request->prefix == 'system') { ?>
    $('#dialog_order_open').hide();
    <?php } ?>

    // process date picker filter NFC
    $("#from").datepicker({
        defaultDate: "+1w",
        inline: true,
        numberOfMonths: 1,
        dateFormat: 'yy-mm-dd',
        maxDate: 0,
        onClose: function(selectedDate) {
            $("#to").datepicker("option", "minDate", selectedDate);
        }
    });
    $("#to").datepicker({
        defaultDate: "+1w",
        inline: true,
        numberOfMonths: 1,
        dateFormat: 'yy-mm-dd',
        maxDate: 0,
        onClose: function(selectedDate) {
            $("#from").datepicker("option", "maxDate", selectedDate);
        }
    });
    $('#datePickerBtn').click(function() {
        var fromVal = $('#from').val(),
            toVal = $('#to').val();
        if (fromVal == '' || toVal == '') {
            alert('<?php echo __('Input date should not be empty.') ?>');
            return false;
        } else {
            var input = $("<input>").attr("type", "hidden").attr("name", "data[Filter][filter]").val("filterFollowDate");
            $("#FilterIndexForm").append($(input));
        }
    });

    $('#searchId').click(function() {
        var input = $("<input>").attr("type", "hidden").attr("name", "data[Filter][filter]").val("filter");
        $("#FilterIndexForm").append($(input));
    });
});
</script>
