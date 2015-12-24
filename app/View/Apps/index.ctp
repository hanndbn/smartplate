<?php
$data = $this->requestAction('accesslogs/contentStatus');
$base_url = array('controller' => 'apps', 'action' => 'index');
$session = $this->Session->read('Auth.User');
$team_id = $session['team_id'];
$last_login = $this->requestAction(array('controller' => 'managements', 'action' => 'getLastLoggin'));
?>
<div id="main" class="list">
<h2><?php echo __('アプリアカウント一覧') ?></h2>
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
    <?php echo __('アプリアカウント総数') ?>:<span class="fs18 red"><?php echo $total ?></span>　
</p>
<table class="operation">
    <tbody>
    <tr class="container-fluid">
        <input id="target_id" style="width: 200px;" type="hidden" name="data[Bookmark][id]">
        <td class="row">
            <span class="text"><?php echo __('選択項目'); ?>:</span>
            <select id="selection" class="">
                <option value=""></option>
                <option value="0"><?php echo __('だけを表示') ?></option>
                <option value="1"><?php echo __('以外を表示') ?></option>
                <option value="2"><?php echo __('の管理名を変更する') ?></option>
                <?php if ($this->request->prefix != 'system') { ?>
                    <option value="3"><?php echo __('のラベルを変更する') ?></option>
                <?php } ?>
                <option value="5"><?php echo __('を無効・有効にする') ?></option>
                <option value="6"><?php echo __('を削除する') ?></option>
            </select>

            <nobr>
            <span id="changeName" style="display:none;" class="">　
                <?php echo __('管理名'); ?>
                <input id="newName" style="width: 200px;" type="text" name="data[Bookmark][name]" autocomplete="off"
                       maxlength="64" data-toggle='checklengh'>　
            </span>
            </nobr>

            <nobr>
            <span id="changeLabel" style="display:none;" class="ma_26">　
                <div class="row">
                    <span class="col-sm-4"><?php echo __('ラベルを選択'); ?></span>

                    <div class="dropdown col-sm-8">
                        <a id="dLabel" role="button" data-toggle="dropdown" class="btn form-control" data-target="#">
                            <?php echo __('ラベルを選択') ?>
                        </a>
                        <span class="caret"></span>
                        <?php echo $this->Label->renderDropdownLabels($labels, 'label-edit'); ?>
                    </div>
                    <?php
                    echo $this->Form->input("myLabel", array('label' => false, 'type' => 'hidden'));
                    ?>
                </div>
            </span>
            </nobr>

            <a id="execute" href="#" style="display:none;" class="imgBtn wide m-l-sm"><?php echo __('設定') ?></a>
        </td>
        
            <td class="pull-right">
                <?php echo $this->Html->link(__('新規アカウントを登録'), array('action' => 'add'), array('id' => 'dialog_new_open', 'class' => 'imgBtn wide', 'data-toggle' => 'ajaxModal')); ?>
            </td>
    </tr>
    </tbody>
</table>

<!--Paginator-->
<?php echo $this->element('pagination'); ?>

<div class="table-hover">
    <table id="app-list" class="table-striped">
        <thead>
        <tr>
            <td class="search" colspan="12">
                <?php
                // The base url is the url where we'll pass the filter parameters

                echo $this->Form->create("Filter");
                // add a select input for each filter. It's a good idea to add a empty value and set
                // the default option to that.
                ?>
                <?php echo __('絞り込み条件') ?>:

                <div class="dropdown inline-block strip">
                    <a id="dLabel_" role="button" data-toggle="dropdown" class="btn form-control" data-target="#">
                        <?php echo __('ラベルを選択') ?>
                    </a>
                    <span class="caret"></span>
                    <?php echo $this->Label->renderDropdownLabels($labels, 'label-filter'); ?>
                </div>
                <span class="plus">＋</span>
                <?php
                echo $this->Form->input("label", array('label' => false, 'type' => 'hidden'));
                // Add a basic search
                echo $this->Form->input("name", array('label' => false, 'placeholder' => "検索キー", 'autocomplete' => 'off'));
                ?>

                <span class="m-sm">
                            <a href="javascript:;"
                               class="imgBtn wide m-r-xs formSubmit subFilter"><?php echo __('検索') ?></a>
                    <?php echo $this->Html->link(__('リセット'), array('action' => 'index'), array('class' => 'imgBtn wide subFilter')) ?>
                        </span>
                <?php if ($team_id != null) { ?>
                    <a class="icon-label" href="<?php echo $this->Html->url(array('action' => 'label')); ?>"
                       title=""><i class="fa fa-tags fa-2x"></i></a>
                <?php } ?>
                <?php
                echo $this->Form->end();
                ?>
            </td>
        </tr>
        <tr>
            <th class="typeB highlight"><?php echo __('選択') ?>
                <br/><?php echo $this->Form->checkbox('all', array('class' => 'CheckAll', 'data-target' => '#app-list tbody')); ?>
            </th>
            <th class="typeB"><?php echo $this->Paginator->sort('status', __('有効')); ?>  </th>
            <th class="typeB"><?php echo $this->Paginator->sort('id', 'No.'); ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('login_name', __('アカウント')); ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('name', __('管理名')); ?></th>
            <th class="typeB highlight"><?php echo __('ラベル')/* $this->Paginator->sort('label', __('ラベル')) */
                ; ?></th>
            <th class="typeB highlight"><?php echo __('管理プレート数'); ?></th>
            <th class="typeB highlight"><?php echo __('当月アクセス総数')/* $this->Paginator->sort('access', __('当月アクセス総数')) */
                ; ?></th>
            <th class="typeB highlight"><?php echo __('Version'); ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('last_access_date', __('最新起動日')); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <?php
            $id = $user['id'];
            $_devices = $_labels = array();

            if (!empty($user['devices'])) {
                foreach ($user['devices'] as $device) {
                    if (!empty($device['version']) && !isset($_devices[$device['version']])) {
                        $_devices[$device['version']] = $device['version'];
                    }
                    /* $_devices['test'] = 'test';
                      $_devices['test2'] = 'test 2';
                      $_devices['test3'] = 'test 3'; */
                }
            }

            if (!empty($user['labels'])) {
                foreach ($user['labels'] as $label) {
                    if (!empty($label['label']) && !isset($_labels[$label['label']])) {
                        $_labels[$label['label']] = $label['label'];
                    }
                }
            }
            ?>
            <tr id="<?php echo 'toggle' . $id ?>" class="modal-detail text-center"
                data-modal="<?php echo $this->webroot . 'apps/detail/' . $id ?>">
                <td class="ignore-modal"><?php echo $this->Form->checkbox('User_' . $id, array('class' => 'check', 'value' => $id, 'hiddenField' => false)); ?></td>
                <td class="status" data-value="<?php echo $user['status'] == '' ? 0 : 1 ?>">
                    <?php echo $user['status'] == 1 ? '<i class="fa fa-circle-thin">' : '<i class="fa fa-times">'; ?>
                </td>
                <td><?php echo $id; ?></td>
                <td class="login-name"><?php echo Utility_Str::wordTrim(Utility_Str::escapehtml($user['login_name']), 50); ?></td>
                <td class="name"
                    data-name="<?php echo Utility_Str::escapehtml($user['name']) ?>"><?php echo Utility_Str::wordTrim(Utility_Str::escapehtml($user['name']), 50); ?></td>
                <td><?php
                    echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml(reset($_labels)) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml(reset($_labels)), 20) . '</span>';
                    if (count($_labels) > 1) {
                        $key = key($_labels);
                        unset($_labels[$key]);
                        $more = implode(', ', $_labels);
                        echo '<span data-toggle="tooltip" data-placement="bottom" title="' . $more . '">, ... </span>';
                    }
                    ?></td>
                <td><?php echo isset($count[$id]['total']) ? $count[$id]['total'] : 0 ?></td>
                <td><?php echo $user['access'] ?></td>
                <td><?php
                    echo reset($_devices);
                    if (count($_devices) > 1) {
                        $key2 = key($_devices);
                        unset($_devices[$key2]);
                        $more = implode(', ', $_devices);
                        echo '<span data-toggle="tooltip" data-placement="bottom" title="' . $more . '">, ... </span>';
                    }
                    ?></td>
                <td><?php
                    if ($user['last_access_date']) {
                        echo date('Y/m/d H:i:s', strtotime($user['last_access_date']));
                    } else {
                        echo '';
                    }
                    ?></td>

            </tr>
        <?php endforeach; ?>
        <?php unset($users); ?>
        </tbody>
    </table>
</div>

<!--Paginator-->
<?php echo $this->element('pagination'); ?>
</div>
<?php
echo $this->Html->link('Detail', array(), array('class' => 'invisible', 'id' => 'detailModal', 'data-toggle' => 'ajaxModal'));
echo $this->Html->link('labelEdit', array(), array('class' => 'invisible', 'id' => 'quicklabelModal', 'data-toggle' => 'ajaxModal', 'data-href' => $this->Html->url(array('controller' => 'apps', 'action' => 'ajaxLabel'))));
?>
<script type="text/javascript">

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
    if ($.urlParam('label') != null) {
        var label = $('.label_name[data-id*=' + $.urlParam('label').replace(/\%20/g, ' ') + ']').html();
        $('#dLabel_').html(label);
    }

    // Get label selection id
    $('.label_name').click(function (e) {
        e.preventDefault();
        var $this = $(this),
            id = $this.attr('data-id'),
            name = $this.html();

        if ($this.hasClass('label-edit')) {
            $('#myLabel').val(id);
            $('#dLabel').empty().html(name);
        }
        else if ($this.hasClass('label-filter')) {
            $('#FilterLabel').val(id);
            $('#dLabel_').empty().html(name);
        }
    });

    $('.check, .CheckAll').on('change', function () {
        //resetFilter(true);
        var selection = parseInt($('#selection').val()),
            $checked = $('.check:checked'),
            name = $checked.closest("tr").find('.name').html();

        if (selection == 5 || selection == 6) {
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

        var value = $("#selection").val(),
            $check = $('.check'),
            checked = $('.check:checked');
        if (!$('.check:checked').length) {
            resetFilter(true);
            alert('<?php echo __('少なくとも１つの項目を選択してください') ?>');
            return;
        }

        var $hrefredirect = '';

        switch (parseInt(value)) {
            case 0:
                resetFilter();

                $check.each(function () {
                    if (!$(this).is(':checked')) {
                        $(this).closest('tr').hide();
                    }
                });
                break;

            case 1:
                resetFilter();

                $check.each(function () {
                    if ($(this).is(':checked')) {
                        $(this).closest('tr').hide();
                    }
                });
                $
                break;

            case 2:
                resetFilter();
                var cr_name = checked.closest("tr").find('.name').data('name').toString().replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');
                (checked.length == 1) ? $('#newName').val(cr_name) : $('#newName').val('');
                $('#execute').show();
                $('#changeName').show();
                break;

            case 3:
                resetFilter();

                var $checked = $('.check:checked'),
                    $label = $('#quicklabelModal'),
                    href = $label.attr('data-href'),
                    id = [];
                checked.each(function () {
                    id.push($(this).val());
                });
                if($('#all').prop("checked")) {
                    $hrefredirect = href + '?selectall=1';
                }else{
                    $hrefredirect = href + '?id=' + id;
                }

                $label.attr('href', $hrefredirect).trigger('click');
                $('#selection').val('');
                break;

            case 5:
                resetFilter();
                $('#execute').show();


                /*if (checked.length === 0) {
                 alert('Please selected checkbox');
                 document.getElementById('changeCode').style.display = 'none';
                 } else {
                 if (notAllowMultiCheckbox(checked) === true) {
                 var cr_code = checked.closest("tr").find('.bm_code').html();
                 $('#newCode').val(cr_code);
                 } else {
                 document.getElementById('changeCode').style.display = 'none';
                 }
                 }*/
                break;

            case 6:
                resetFilter();
                $('#execute').show();

                break;

            default:
                resetFilter(true);
                $('#changeType').hide();
                $('#changeName').hide();
                $('#changeLabel').hide();
                $('#execute').hide();

                break;
        }
    });

    $('#execute').on('click', function (e) {

        e.preventDefault();
        var now = new Date().getTime();
        var selection = parseInt($('#selection').val()),
            $check = $('.check'),
            ajaxData = {
                id: [],
                selectall:'0'
            },
            url;

        if ($.inArray(selection, [2, 3, 5, 6]) != -1) {
            $check.each(function () {
                if ($(this).is(':checked')) {
                    ajaxData.id.push($(this).val());
                }
            });
            if($('#all').prop("checked")) {
                ajaxData.selectall = '1';
            }

            switch (selection) {
                case 2:
                    if ($('#newName').val().replace(/ /g, '') == '') {
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
                            ajaxData.name = $('#newName').val();
                            $.ajax({
                                type: 'POST',
                                dataType: 'JSON',
                                url: buildUrl('<?php echo $this->Html->url(array('controller' => 'apps', 'action' => 'ajaxName'), true); ?>', '_t', now),
                                data: ajaxData,
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
                case 5:
                    url = buildUrl('<?php echo $this->Html->url(array('controller' => 'apps', 'action' => 'ajaxStatus'), true); ?>', '_t', now);
                    break;
                case 6:
                    $.confirm({
                        text: '<?php echo __("選択した項目を削除してもよろしいですか？") ?>',
                        title: '<?php echo __("確認") ?>',
                        confirm: function (confirmButton) {
                            $.ajax({
                                type: 'POST',
                                dataType: 'JSON',
                                url: buildUrl('<?php echo $this->Html->url(array('controller' => 'apps', 'action' => 'ajaxDelete'), true); ?>', '_t', now),
                                data: ajaxData,
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
            }
        }

        if (ajaxData && url) {
            $.ajax({
                type: 'post',
                dataType: "json",
                url: url,
                data: ajaxData,
                success: function (response) {
                    if (response.success) {
                        location.reload();
                        $('#selection').val('');
                    }
                }
            });
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
    });

    var buildUrl = function (base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    }

    function notAllowMultiCheckbox(check) {
        if (check.length > 1) {
            resetFilter(true);
            alert('<?php echo __("複数のチェックボックスを選択することはできません。") ?>');
            return true;
        }

        return false;
    }

    function resetFilter(resetSelection) {
        if (resetSelection) {
            $('#selection').val('');
        }

        $('.check').show();
        $('.check').closest('tr').show();
        $('#changeType').hide();
        $('#changeName').hide();
        $('#changeLabel').hide();
        $('#execute').hide();
    }

});
</script>
