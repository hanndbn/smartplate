<?php
$data = ($this->request->prefix == 'system') ? $this->requestAction(array('prefix' => 'system', 'controller' => 'accesslogs', 'action' => 'contentStatus')) : $this->requestAction(array('controller' => 'accesslogs', 'action' => 'contentStatus'));
$base_url = array('controller' => 'accounts', 'action' => 'index');
$last_login = $this->requestAction(array('controller' => 'managements', 'action' => 'getLastLoggin'));
?>
<div id="main" class="list">
    <h2><?php echo __('クラウドアカウント一覧') ?></h2>
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
        <br/>
        <?php echo __('稼働コンテンツ総数 当月') ?>：<span class="fs18 red"><?php echo $data['monthly'] ?></span>　
        <?php echo __('本日') ?>：<span class="fs18 red"><?php echo $data['daily'] ?></span>
    </p>



    <?php //echo $this->Form->create("QuickEdit", array('url' => $qedit_url, 'role' => "form", 'name' => 'Qform')); ?>
    <table class="operation">
        <tr>
            <td>
                <?php echo __('選択項目') ?>：
                <select id="selection">
                    <option value=""></option>
                    <option value="0"><?php echo __('だけを表示') ?></option>
                    <option value="1"><?php echo __('以外を表示') ?></option>
                    <option value="2"><?php echo __('の管理名を変更する') ?></option>
                    <option value="6"><?php echo __('を無効・有効にする') ?></option>
                </select>
                <span id="changeName" style="display:none;">　
                    <?php echo __('名前') ?>：
                    <input type="text" id="newName" name="data[Management][name]" style="width: 200px;"
                           autocomplete="off" maxlength="64" data-toggle='checklengh'>　
                </span>
                <a id="quickAction-btn" style="display:none;" class="imgBtn wide hightlight-btn m-l-sm"
                   href="#"><?php echo __('設定') ?></a>
            </td>
            <td class="pull-right">
                <?php echo $this->Html->link(__('新規アカウントを登録'), array('action' => 'add'), array('id' => 'dialog_new_open', 'class' => 'imgBtn wide', 'data-toggle' => 'ajaxModal')); ?>
            </td>
        </tr>
    </table>


    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>

    <table id="account-list">
        <thead>
        <tr>
            <td class="search" colspan="11">
                <?php echo $this->Form->create("Filter", array('url' => $base_url, 'role' => "form")); ?>
                <?php echo __('絞り込み条件'); ?>
                <select id="FilterManager" class="" name="data[Filter][manager]">
                    <option value=""><?php echo __('プロジェクト') ?></option>
                    <?php foreach ($list_manager as $key => $value) { ?>
                        <option value="<?php echo $value ?>"
                                title="<?php echo Utility_Str::escapehtml($value) ?>"><?php echo __(Utility_Str::wordTrim(Utility_Str::escapehtml($value), 20)) ?></option>
                    <?php } ?>
                </select>
                ＋
                <?php
                echo $this->Form->input("name", array('label' => false, 'placeholder' => "Search word"));
                echo $this->Form->submit(__('検索'), array('class' => 'imgBtn wide hightlight-btn m-sm'));
                echo $this->Html->link(__('リセット'), $base_url, array('class' => 'imgBtn wide subFilter'));
                ?>
                <?php echo $this->Form->end(); ?>
            </td>
        </tr>
        <tr>
            <th class="typeB highlight"><?php echo __('選択') ?>
                <br/><?php echo $this->Form->checkbox('all', array('class' => 'CheckAll', 'data-target' => '#account-list tbody')); ?>
            <th class="typeB"><?php echo $this->Paginator->sort('status', __('有効')) ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('id', __('No.')) ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('authority', __('種別')) ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('login_name', __('アカウント')) ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('name', __('管理名')) ?></th>
            <th class="typeB highlight"><?php echo __('管理アカウント数') ?></th>
            <th class="typeB highlight"><?php echo __('管理コンテンツ数') ?></th>
            <th class="typeB highlight"><?php echo __('管理プレート数') ?></th>
            <th class="typeB"><?php echo $this->Paginator->sort('last_login_date', __('最新ログイン')) ?></th>
        </tr>
        </thead>

        <tbody>

        <?php foreach ($users as $user) { ?>
            <?php $id = $user['Management']['id']; ?>
            <tr id="<?php echo 'toggle' . $id ?>" class="modal-detail text-center"
                data-modal="<?php echo ($this->request->prefix == 'system') ? $this->webroot . 'system/accounts/detail/' . $id : $this->webroot . 'accounts/detail/' . $id ?>">
                <td class="ignore-modal"><?php echo $this->Form->checkbox('Account.id.' . $id, array('class' => 'check', 'value' => $id, 'hiddenField' => false)); ?></td>
                <td class="bm_visible">
                    <?php echo $user['Management']['status'] == 1 ? '<i class="fa fa-circle-thin">' : '<i class="fa fa-times">'; ?>
                </td>
                <td class="centering nowrap"><?php echo h($id) ?></td>
                <td class="centering">
                    <?php
                    $authority = $user['Management']['authority'];
                    switch ($authority) {
                        case '1':
                            echo __('admin');
                            break;
                        case '2':
                            echo __('manager');
                            break;
                        case '3':
                            echo __('editor');
                            break;
                    }
                    ?>
                </td>
                <td><?php echo Utility_Str::wordTrim(Utility_Str::escapehtml($user['Management']['login_name']), 20) ?></td>
                <td class="_name"
                    data-name="<?php echo Utility_Str::escapehtml($user['Management']['name']) ?>"><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($user['Management']['name']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($user['Management']['name']), 20) . '</span>' ?></td>
                <td class="centering"><?php echo $user['Management']['child_count'] ?></td>
                <td class="centering"><?php echo $user['Management']['content_count'] ?></td>
                <td class="centering"><?php echo $user['Management']['plate_count'] ?></td>
                <td class="centering nowrap">
                    <?php echo ($user['Management']['last_login'] != null) ? date('Y/m/d H:i:s', strtotime($user['Management']['last_login'])) : '' ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>

</div>

<?php
echo $this->Html->link('Detail', array(), array('class' => 'invisible', 'id' => 'detailModal', 'data-toggle' => 'ajaxModal'));
echo $this->Html->link('label_edit', array('action' => 'quickedit_label'), array('class' => 'invisible', 'id' => 'quicklabelModal', 'data-toggle' => 'ajaxModal', 'data-href' => $this->Html->url(array('controller' => 'accounts', 'action' => 'quickedit_label'))));
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
        $('#changeName').hide();
    }

    var buildUrl = function (base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
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
        if ($.urlParam('manager') != null) {
            $('#FilterManager').val($.urlParam('manager').replace(/\%20/g, ' '));
        }

        $('.check, .CheckAll').on('change', function () {
            //resetFilter(true);
            var selection = parseInt($('#selection').val()),
                $checked = $('.check:checked');

            if (selection == 6) {
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

            if (!checked.length) {
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
                    var cr_name = checked.closest("tr").find('._name').data('name').toString().replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');
                    (checked.length == 1) ? $('#newName').val(cr_name) : $('#newName').val('');
                    $('#changeName').show();
                    $('#quickAction-btn').show();
                    break;
                case '6':
                    resetFilter();
                    $('#quickAction-btn').show();
                    break;
                default :
                    resetFilter();
                    break;
            }
        });
        $('#quickAction-btn').click(function (e) {
            e.preventDefault();
            var now = new Date().getTime();
            var url = buildUrl('<?php echo $this->Html->url(array('controller' => 'accounts', 'action' => 'quickEdit'), true); ?>', '_t', now);
            var filter_val = $('#selection').find(':selected').attr('value');
            var checked = $('.check:checked');
            var id = checked.val();
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
                            confirmButtonClass: 'btn-default hide',
                        });

                        return;
                    }
                    $.confirm({
                        text: '<?php echo __("「選択した項目の名前を変更します」") ?>',
                        title: '<?php echo __("確認") ?>',
                        confirm: function (confirmButton) {
                            var ids = [];
                            checked.each(function () {
                                ids.push($(this).val());
                            });
                            data = {id: ids, type: filter_val, input: input};
                            ajaxEdit(url, data);
                        },
                        confirmButton: "Yes",
                        cancelButton: "No",
                        confirmButtonClass: 'btn-default',
                        post: true
                    });
                    break;
                case '6':
                    var ids = [];
                    checked.each(function () {
                        ids.push($(this).val());
                    });
                    data = {ids: ids, type: filter_val};
                    ajaxEdit(url, data);
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
