<?php
$data = $this->requestAction('accesslogs/contentStatus');
$base_url = array('controller' => 'bookmarks', 'action' => 'index');
$session = $this->Session->read('Auth.User');

$team_id = ($this->request->prefix != 'system') ? $session['team_id'] : null;
$authority = $session['authority'];
$last_login = $this->requestAction(array('controller' => 'managements', 'action' => 'getLastLoggin'));
?>
<div id="main" class="list">
<h2><?php echo __('コンテンツ一覧') ?></h2>
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
        //echo "{$year}年 {$mon}月 {$day}日 {$hour}時 {$min}分 {$sec}秒 時点";
        echo $year . '-' . $mon . '-' . $day . ' ' . $hour . ':' . $min . ':' . $sec;
    }
    ?>
    <br>
    <?php echo __('稼働コンテンツ数 当月'); ?>:<span class="fs18 red"><?php echo $data['monthly'] ?></span>　
    <?php echo __('本日') ?>:<span class="fs18 red"><?php echo $data['daily'] ?></span>
</p>

<table class="operation">
    <tbody>
    <tr class="container-fluid">
        <input id="target_id" style="width: 200px;" type="hidden" name="data[Bookmark][id]">
        <td>
            <span class="text"><?php echo __('選択項目'); ?>:</span>
            <select id="selection" class="">
                <option selected="selected" value=""></option>
                <option value="0"><?php echo __('だけを表示'); ?></option>
                <option value="1"><?php echo __('以外を表示'); ?></option>
                <option value="2"><?php echo __('の名前を変更する'); ?></option>
                <option value="3"><?php echo __('の種別を変更する'); ?></option>
                <?php if ($this->request->prefix != 'system') { ?>
                    <option value="4"><?php echo __('のラベルを変更する'); ?></option>
                <?php } ?>
                <option value="5"><?php echo __('のバーコードを変更する'); ?></option>
                <option value="6"><?php echo __('を無効・有効にする'); ?></option>
                <option value="7"><?php echo __('を削除する'); ?></option>
                <option value="8"><?php echo __('CSV'); ?></option>
            </select>

            <nobr>
            <span id="changeType" style="display:none;" class="">　
                <span class="text"><?php echo __('種別'); ?>:</span>
                <select id='myType' class="" name="data[Bookmark][kind]">
                    <?php foreach ($type as $key => $value) { ?>
                        <option value="<?php echo $key ?>"
                                title="<?php echo Utility_Str::escapehtml($value) ?>"><?php echo __(Utility_Str::wordTrim(Utility_Str::escapehtml($value), 20)) ?></option>
                    <?php } ?>
                </select>　
            </span>
            </nobr>

            <nobr>
            <span id="selectCSV" style="display:none;" class="">　
                <?php echo __('CSV File:'); ?>
                <?php echo $this->Form->create("CSV", array('url' => array('controller' => 'bookmarks', 'action' => 'importCSV'), 'type' => 'file', 'class' => 'inline-block')); ?>
                <input class="imgBtn wide hightlight-btn pull-right m-t-xs" type="submit"
                       value="<?php echo __('アップロード') ?>">
                <input class="pull-right m-t-xs" type="file" name="data[CSV][csv]"/>　
                <?php echo $this->Form->end(); ?>
            </span>
            </nobr>

            <nobr>
            <span id="changeName" style="display:none;" class="">　
                <?php echo __('名前'); ?>:
                <input id="newName" style="width: 200px;" type="text" name="data[Bookmark][name]" maxlength="128"
                       data-toggle='checklengh'>　
            </span>
            </nobr>

            <nobr>
            <span id="changeCode" style="display:none;" class="">　
                <?php echo __('新しいバーコード'); ?>:
                <input id="newCode" name="data[Bookmark][code]" style="width: 200px;" type="text" autocomplete="off"
                       maxlength="64" data-toggle='checklengh'>　
            </span>
            </nobr>

        <span id="execute" style="display:none;">
            <input id="newVisible" name="data[Bookmark][_visible]" style="width: 200px;" type="hidden">　
        </span>

            <a id="quickAction-btn" style="display:none;" class="imgBtn wide hightlight-btn m-l-sm"
               href="#"><?php echo __('設定') ?></a>
        </td>
        <?php if ($authority == 3) { ?>
            <td class="pull-right">
                <?php echo $this->Html->link(__('新規コンテンツを登録'), array('action' => 'add'), array('id' => 'dialog_new_open', 'class' => 'imgBtn wide', 'data-toggle' => 'ajaxModal')); ?>
            </td>
        <?php } ?>
    </tr>
    </tbody>
</table>

<!--Paginator-->
<?php echo $this->element('pagination'); ?>

<div class="table-hover">
    <table id="bookmark-list" class="table-striped">
        <thead>
        <tr>
            <td class="search" colspan="12">
                <?php echo $this->Form->create("Filter", array('url' => $base_url, 'role' => "form")); ?>
                <?php echo __('絞り込み条件'); ?>:
                <select id="FilterKind" class="" name="data[Filter][kind]">
                    <option value=""><?php echo __('種別を選択') ?></option>
                    <?php foreach ($type as $key => $value) { ?>
                        <option value="<?php echo $key ?>"><?php echo __($value) ?></option>
                    <?php } ?>
                </select>
                ＋
                <div class="dropdown inline-block strip">
                    <a id="dLabel_" role="button" data-toggle="dropdown" class="btn form-control" data-target="#">
                        <?php echo __('ラベルを選択') ?>
                    </a>
                    <span class="caret"></span>
                    <?php echo $this->Label->renderDropdownLabels($list_lb); ?>
                </div>
                <?php echo $this->Form->input("label", array('label' => false, 'type' => 'hidden')); ?>
                ＋
                <?php
                // Add a basic search
                echo $this->Form->input("name", array('label' => false, 'placeholder' => "検索キー"));

                echo $this->Form->submit(__('検索'), array('class' => 'imgBtn wide hightlight-btn m-sm'));

                echo $this->Html->link(__('リセット'), $base_url, array('class' => 'imgBtn wide subFilter'));
                ?>
                <?php if ($authority == 3) { ?>
                    <a class="icon-label" href="<?php echo $this->Html->url(array('action' => 'label')); ?>"
                       title="Edit Label">
                        <i class="fa fa-tags fa-2x"></i>
                    </a>
                <?php } ?>

                <a href="<?= $this->Html->url(array('controller' => 'bookmarks', 'action' => 'index', 'act:export')) ?>"
                   class='icon-label' title="csv download" onclick="alert('<?php echo __('ダウンロードされるファイルのフォーマットはUTF8です。') ?>');">
                    <i class="fa-2x fa fa-download"></i>
                </a>
                <?php
                echo $this->Form->end();
                ?>
            </td>
        </tr>

        <tr class="text-center">
            <th class="typeB highlight"><?php echo __('選択') ?>
                <br/><?php echo $this->Form->checkbox('all', array('class' => 'CheckAll', 'data-target' => '#bookmark-list tbody')); ?>
            </th>
            <th class="typeB"><?php echo $this->Paginator->sort('visible', __('有効')); ?>  </th>
            <th class="typeB"><?php echo $this->Paginator->sort('id', 'ID'); ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('kind', __('種別')); ?></th>
            <th class="typeB highlight"><?php echo __('ラベル')/* $this->Paginator->sort('label', __('ラベル')); */ ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('name', __('名前')); ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('code', __('バーコード')); ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('cdate', __('更新日時')); ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('link', __('配信プレート数')); ?></th>
            <th class="typeB highlight"><?php echo __('当月アクセス数')/* $this->Paginator->sort('access', __('当月アクセス数')); */ ?></th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($bookmarks as $bookmark): ?>
            <?php $id = $bookmark['Bookmark']['id']; ?>
            <tr id="<?php echo 'toggle' . $id ?>" class="modal-detail text-center"
                data-modal="<?php echo $this->webroot . 'bookmarks/detail/' . $id ?>">
                <td class="ignore-modal"><?php echo $this->Form->checkbox('Bookmark.id.' . $id, array('class' => 'check', 'value' => $id, 'hiddenField' => false)); ?></td>
                <td class="bm_visible" data-value="<?php echo $bookmark['Bookmark']['visible'] == '' ? 0 : 1 ?>">
                    <?php echo $bookmark['Bookmark']['visible'] == 1 ? '<i class="fa fa-circle-thin">' : '<i class="fa fa-times">'; ?>
                </td>
                <td><?php echo $bookmark['Bookmark']['id']; ?></td>
                <td class="bm_kind"
                    data-type="<?php echo $bookmark['Type']['id'] ?>"><?php echo $bookmark['Type']['bookmark_type']; ?></td>
                <td class="bm_label"
                    data-id="<?php //echo $bookmark['Label']['id']                                         ?>">

                    <?php
                    $_labels = $bookmark['lb_name'];
                    echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml(reset($_labels)) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml(reset($_labels)), 20) . '</span>';
                    if (count($_labels) > 1) {
                        $key = key($_labels);
                        unset($_labels[$key]);
                        $more = implode(', ', $_labels);
                        echo '<span data-toggle="tooltip" data-placement="bottom" title="' . $more . '">, ... </span>';
                    }
                    ?>

                </td>
                <td class="bm_name"
                    data-name="<?php echo Utility_Str::escapehtml($bookmark['Bookmark']['name']) ?>"><?php echo Utility_Str::wordTrim(Utility_Str::escapehtml($bookmark['Bookmark']['name']), 50); ?></td>
                <td class="bm_code"
                    data-name="<?php echo Utility_Str::escapehtml($bookmark['Bookmark']['code']) ?>"><?php echo Utility_Str::wordTrim(Utility_Str::escapehtml($bookmark['Bookmark']['code']), 50); ?></td>
                <td><?php echo date('Y/m/d H:i:s', strtotime($bookmark['Bookmark']['cdate'])); ?></td>
                <td><?php echo $bookmark['Link']; ?></td>
                <td><?php echo $bookmark['Access']; ?></td>

            </tr>
        <?php endforeach; ?>
        <?php unset($bookmark); ?>
        </tbody>
    </table>
</div>

<!--Paginator-->
<?php echo $this->element('pagination'); ?>
</div>
<?php
echo $this->Html->link('Detail', array(), array('class' => 'invisible', 'id' => 'detailModal', 'data-toggle' => 'ajaxModal'));
echo $this->Html->link('label_edit', array('action' => 'quickedit_label'), array('class' => 'invisible', 'id' => 'quicklabelModal', 'data-toggle' => 'ajaxModal', 'data-href' => $this->Html->url(array('controller' => 'bookmarks', 'action' => 'quickedit_label'))));
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
        alert('<?php echo __("複数のチェックボックスを選択することはできません。") ?>');
        return false;
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
    $('#changeType').hide();
    $('#changeName').hide();
    $('#changeCode').hide();
    $('#selectCSV').hide();
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
    if ($.urlParam('kind') != null) {
        $('#FilterKind').val($.urlParam('kind').replace(/\%20/g, ' '));
    }
    if ($.urlParam('label') != null) {
        var label = $('.label_name[data-id*=' + $.urlParam('label').replace(/\%20/g, ' ') + ']').html();
        $('#dLabel_').html(label);
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
            $('#dLabel_').empty().html($(this).html());
        }
    });

    $('.check, .CheckAll').on('change', function () {
        //resetFilter(true);
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
        if (!checked.length && (value != 8 && value != '')) {
            resetFilter(true);
            alert('<?php echo __('少なくとも１つの項目を選択してください') ?>');
            return;
        }

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
                var cr_name = checked.closest("tr").find('.bm_name').data('name').toString().replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');
                (checked.length == 1) ? $('#newName').val(cr_name) : $('#newName').val('');
                $('#quickAction-btn').show();
                $('#changeName').show();
                break;
            case '3':
                resetFilter();
                var cr_type = checked.closest("tr").find('.bm_kind').attr('data-type');
                (checked.length == 1) ? $('#myType').val(cr_type) : $('#myType').val('');
                $('#quickAction-btn').show();
                $('#changeType').show();
                break;
            case '4':
                resetFilter();
                ids = getIDs(checked);
                var href = $('#quicklabelModal').attr('data-href');
                $('#quicklabelModal').attr('href', href + '?id=' + ids).trigger('click');
                $('#selection').val('');
                break;
            case '5':
                resetFilter();
                var cr_code = checked.closest("tr").find('.bm_code').data('name').toString().replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');
                (checked.length == 1) ? $('#newCode').val(cr_code) : $('#newCode').val('');
                $('#quickAction-btn').show();
                $('#changeCode').show();
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
                $('#selectCSV').show();
                break;
            default :
                resetFilter();
                break;
        }
    });
    $('#quickAction-btn').click(function (e) {
        e.preventDefault();
        var now = new Date().getTime();
        var url = buildUrl('<?php echo $this->Html->url(array('controller' => 'bookmarks', 'action' => 'quickEdit'), true); ?>', '_t', now);
        var filter_val = $('#selection').find(':selected').attr('value');
        var checked = $('.check:checked');
        var id = checked.val(),
            ids;
        var input = '';
        var data = [];
        var value = $("#selection").val();
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
                        data = {ids: ids, type: filter_val, input: input};
                        ajaxEdit(url, data);
                    },
                    confirmButton: "Yes",
                    cancelButton: "No",
                    confirmButtonClass: 'btn-default',
                    post: true
                });
                break;
            case '3':
                input = $('#myType option:selected').attr('value');
                ids = getIDs(checked);
                data = {ids: ids, type: filter_val, input: input};
                ajaxEdit(url, data);
                break;
            case '4':
                break;
            case '5':
                input = $("#newCode").val();
                if (input === '') {
                    alert('<?php echo __('このフィールドを入力してください。') ?>');
                } else {
                    ids = getIDs(checked);
                    data = {ids: ids, type: filter_val, input: input};
                    ajaxEdit(url, data);
                }
                break;
            case '6':
                ids = getIDs(checked);
                data = {ids: ids, type: filter_val};
                ajaxEdit(url, data);
                break;
            case '7':
                var _url = buildUrl('<?php echo $this->Html->url(array('controller' => 'bookmarks', 'action' => 'delete'), true); ?>', '_t', now);
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
                break;
            default :
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
});
</script>
