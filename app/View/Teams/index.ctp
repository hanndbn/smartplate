<?php
$data = ($this->request->prefix == 'system') ? $this->requestAction(array('prefix' => 'system', 'controller' => 'accesslogs', 'action' => 'contentStatus')) : $this->requestAction(array('controller' => 'accesslogs', 'action' => 'contentStatus'));
$platinum = ($this->request->prefix == 'system') ? $this->requestAction(array('prefix' => 'system', 'controller' => 'accesslogs', 'action' => 'plateStatus')) : $this->requestAction(array('controller' => 'accesslogs', 'action' => 'plateStatus'));
$base_url = array('controller' => 'teams', 'action' => 'index');
$last_login = $this->requestAction(array('controller' => 'managements', 'action' => 'getLastLoggin'));

/* Get sort */
?>
<div id="main" class="list">
    <h2><?php echo __('プロジェクト一覧') ?></h2>
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
        <?php echo __('稼働プロジェクト数 当月') ?>:<span class="fs18 red"><?php echo $num_project ?></span> <br />
        <?php echo __('稼働プレート総数 当月') ?>:<span class="fs18 red"><?php echo $platinum['monthly'] . '  ' ?></span>  
        <?php echo __('本日') ?>:<span class="fs18 red"><?php echo $platinum['daily'] . ' ' ?></span>
        <?php echo __('稼働コンテンツ総数 当月') ?>:<span class="fs18 red"><?php echo $data['monthly'] ?></span>　 
        <?php echo __('本日') ?>:<span class="fs18 red"><?php echo $data['daily'] ?></span> <br />
        <?php echo __('今月のプラチナプレート（アクセス1000超）数') ?>:<span class="fs18 red"><?php echo $platinum['platinum'] ?></span>
    </p>

    <?php //echo $this->Form->create("QuickEdit", array('url' => $qedit_url, 'role' => "form", 'name' => 'Qform')); ?>
    <table class="operation">
        <tr>
            <td>
                <span class="text"><?php echo __('選択項目'); ?>:</span>
                <select id="selection">
                    <option value=""></option>
                    <option value="0"><?php echo __('だけを表示') ?></option>
                    <option value="1"><?php echo __('以外を表示') ?></option>
                    <option value="2"><?php echo __('の名前を変更する') ?></option>
                    <option value="4"><?php echo __('のラベルを変更する') ?></option>
                    <option value="3"><?php echo __('の管理アカウントを変更する') ?></option>
                    <option value="6"><?php echo __('を無効・有効にする') ?></option>
                    <option value="7"><?php echo __('を削除する') ?></option>
                </select>
                <span id="changeManager" style="display:none;">　
                    <?php echo __('管理アカウント') ?>:
                    <select id="myManager" name="data[Team][manager]">
                        <?php foreach ($list_manager as $key => $value) { ?>
                            <option value="<?php echo $key ?>" title="<?php echo Utility_Str::escapehtml($value) ?>"><?php echo __(Utility_Str::wordTrim(Utility_Str::escapehtml($value), 20)) ?></option>
                        <?php } ?>
                    </select>　                  
                </span>
                <span id="changeName" style="display:none;">　
                    <?php echo __('名前') ?>:
                    <input type="text" id="newName" name="data[Team][name]" style="width: 200px;" autocomplete="off" maxlength="128" data-toggle = 'checklengh'>　                  
                </span>
                <a id="quickAction-btn" style="display:none;" class="imgBtn wide hightlight-btn m-l-sm" href="#"><?php echo __('設定') ?></a>
            </td>
            <td class="pull-right">
                <?php echo $this->Html->link(__('新規プロジェクトを登録'), array('action' => 'add'), array('id' => 'dialog_new_open', 'class' => 'imgBtn wide', 'data-toggle' => 'ajaxModal')); ?>
            </td>
        </tr>
    </table>


    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>

    <table id ="team-list">
        <thead>
            <tr>
                <td class="search" colspan="11">
                    <?php echo $this->Form->create("Filter", array('url' => $base_url, 'role' => "form")); ?>
                    <?php echo __('絞り込み条件'); ?>:
                    <div class="dropdown inline-block strip">
                        <a id="dLabel_" role="button" data-toggle="dropdown" class="btn form-control" data-target="#" href="#">
                            <?php echo __('ラベルを選択') ?> 
                        </a>
                        <span class="caret"></span>
                        <?php echo $this->Label->renderDropdownLabels($list_lb); ?>
                    </div>
                    <?php echo $this->Form->input("label", array('label' => false, 'type' => 'hidden')); ?>
                    <span class="plus">＋ </span>
                    <select id="FilterManager" class="" name="data[Filter][manager]">
                        <option value=""><?php echo __('管理アカウント') ?></option>
                        <?php foreach ($list_manager as $key => $value) { ?>
                            <option value="<?php echo $value ?>" title="<?php echo Utility_Str::escapehtml($value) ?>"><?php echo __(Utility_Str::wordTrim(Utility_Str::escapehtml($value), 20)) ?></option>
                        <?php } ?>
                    </select>
                    ＋
                    <?php
                    echo $this->Form->input("name", array('label' => false, 'placeholder' => "検索キー"));
                    echo $this->Form->submit(__('検索'), array('class' => 'imgBtn wide hightlight-btn m-sm'));
                    echo $this->Html->link(__('リセット'), $base_url, array('class' => 'imgBtn wide subFilter'));
                    ?>
                    <a class="icon-label" href="<?php echo $this->Html->url(array('action' => 'label')); ?>" title="">
                        <!--<i class="fa fa-tags fa-2x"></i>-->
                    </a>
                    <?php echo $this->Form->end(); ?>
                </td>
            </tr>
            <tr>
                <th class="typeB highlight"><?php echo __('選択') ?><br /><?php echo $this->Form->checkbox('all', array('class' => 'CheckAll', 'data-target' => '#team-list tbody')); ?>
                <th class="typeB"><?php echo $this->Paginator->sort('id', __('No.')) ?></th>
                <th class="typeB"><?php echo $this->Paginator->sort('valid', __('有効')) ?></th>
                <th class="typeB"><?php echo $this->Paginator->sort('name', __('名前')) ?></th>
                <th class="typeB highlight"><?php echo __('ラベル') ?></th>
                <th class="typeB"><?php echo $this->Paginator->sort('plan', __('プラン')) ?></th>
                <th class="typeB highlight"><?php echo __('当月稼働プレート') ?></th>
                <th class="typeB highlight"><?php echo __('当月稼働コンテンツ') ?></th>
                <th class="typeB highlight"><?php echo __('当月アクセス数') ?></th>
                <th class="typeB highlight"><?php echo __('開始日') ?></th>
                <th class="typeB highlight"><?php echo __('最新アクセス') ?></th>
                <th class="typeB highlight"><?php echo __('管理アカウント') ?></th>
            </tr>
        </thead>
        <tbody>          
            <?php foreach ($teams as $team) { ?>
                <?php $id = $team['Team']['id']; ?>
                <tr id="<?php echo 'toggle' . $id ?>" class="modal-detail text-center" data-modal="<?php echo $this->webroot . 'teams/detail/' . $id ?>">
                    <td class="ignore-modal" ><?php echo $this->Form->checkbox('Team.id.' . $id, array('class' => 'check', 'value' => $id, 'hiddenField' => false)); ?></td>
                    <td class="centering nowrap"><?php echo h($id) ?></td>
                    <td class="bm_visible">
                        <?php echo $team['Team']['valid'] == 1 ? '<i class="fa fa-circle-thin">' : '<i class="fa fa-times">'; ?>
                    </td>
                    <td class="_name" data-name="<?php echo Utility_Str::escapehtml($team['Team']['name']) ?>"><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($team['Team']['name']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($team['Team']['name']), 50) . '</span>' ?></td>
                    <td class="centering">
                        <?php
                        if (isset($team['Team']['label'])) {
                            $_labels = $team['Team']['label'];
                            echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml(reset($_labels)) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml(reset($_labels)), 20) . '</span>';
                            if (count($_labels) > 1) {
                                $key = key($_labels);
                                unset($_labels[$key]);
                                $more = implode(', ', $_labels);
                                echo '<span data-toggle="tooltip" data-placement="bottom" title="' . $more . '">, ... </span>';
                            }
                        }
                        ?>
                    </td>
                    <td class="centering">
                        <?php
                        $plan = $team['Team']['plan'];
                        switch ($plan) {
                            case 1:
                                $planName = 'pay-per-use';
                                break;
                            case 2:
                                $planName = 'Bronze';
                                break;
                            case 3:
                                $planName = 'Silver';
                                break;
                            case 4:
                                $planName = 'Gold';
                                break;
                            case 5:
                                $planName = 'Platinum';
                                break;
                            default:
                                $planName = '';
                                break;
                        }
                        echo $planName;
                        ?>
                    </td>
                    <td class="centering"><?php echo $team['Team']['plate_count'] . '/' . $team['Team']['plate_total'] ?></td>
                    <td class="centering"><?php if (isset($team['Team']['content_count'])) echo $team['Team']['content_count'] . '/' . $team['Team']['content_total']; ?></td>
                    <td class="centering"><?php echo $team['Team']['access_count'] ?></td>
                    <td class="centering nowrap">
                        <?php if ($team['Team']['start_date'] != null) echo h(date('Y/m/d', strtotime($team['Team']['start_date']))) ?>
                    </td>
                    <td class="centering nowrap">
                        <?php if ($team['Team']['last_access'] != null) echo h(date('Y/m/d H:i:s', strtotime($team['Team']['last_access']))) ?>
                    </td>
                    <td class="centering _manager" data-id = "<?php echo $team['Management']['id'] ?>"><?php echo $team['Management']['login_name'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>

</div>


<?php
echo $this->Html->link('Detail', array(), array('class' => 'invisible', 'id' => 'detailModal', 'data-toggle' => 'ajaxModal'));
echo $this->Html->link('label_edit', array('controller' => 'teams', 'action' => 'quickedit_label'), array('class' => 'invisible', 'id' => 'quicklabelModal', 'data-toggle' => 'ajaxModal', 'data-href' => $this->Html->url(array('controller' => 'teams', 'action' => 'quickedit_label'))));
?>
<script type="text/javascript">
    function ajaxEdit(url, data) {
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(rs) {
                location.reload();
                $('#selection').val('');
            }
        });
    }

    function notAllowMultiCheckbox(check) {
        if (check.length > 1) {

            //alert('<?php echo __("複数のチェックボックスを選択することはできません。") ?>');
            //return false;
        }
        return true;
    }


    function resetFilter(resetSelection)
    {
        if (resetSelection)
        {
            $('#selection').val('');
        }
        $('.check').show();
        $('.check').closest('tr').show();
        $('#quickAction-btn').hide();
        $('#execute').hide();
        $('#changeName').hide();
        $('#changeManager').hide();
    }

    var buildUrl = function(base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    };

    $(document).ready(function() {
        $('th a').append(' <i class="fa fa-sort"></i>');
        $('th a.asc i').attr('class', 'fa fa-sort-down');
        $('th a.desc i').attr('class', 'fa fa-sort-up');

        // Get GET filter parameter
        $.urlParam = function(name) {
            var results = new RegExp('[\/]' + name + ":([^&#/]*)").exec(window.location.href);
            if (results === null) {
                return null;
            }
            else {
                return results[1] || 0;
            }
        };
        if ($.urlParam('manager') !== null) {
            $('#FilterManager').val($.urlParam('manager').replace(/\%20/g, ' '));
        }
        if ($.urlParam('label') !== null) {
            var label = $('.label_name[data-id*=' + $.urlParam('label').replace(/\%20/g, ' ') + ']').html();
            $('#dLabel_').html(label);
        }

        // Get label selection id
        $('.label_name').click(function(e) {
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

        $('.check, .CheckAll').on('change', function() {
            //resetFilter(true);
            var selection = parseInt($('#selection').val()),
                    $checked = $('.check:checked'),
                    name = $(this).closest("tr").find('._name').html();

            if (selection === 6 || selection === 7)
            {
                if ($(this).hasClass('CheckAll'))
                {
                    if ($checked.length === $('.check').length)
                    {
                        resetFilter(true);
                    }
                }

                if (!$checked.length)
                {
                    resetFilter(true);
                }
            }
            else
            {
                resetFilter(true);
            }
        });

        $('#selection').change(function(e) {
            e.preventDefault();

            var unchecked = $('.check:not(:checked)');
            var checked = $('.check:checked');
            var value = $("#selection").val();

            if (!checked.length)
            {
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
                case '3':
                    resetFilter();
                    if (notAllowMultiCheckbox(checked) === true) {
                        var cr_id = checked.closest("tr").find('._manager').data('id');
                        $('#myManager').val(cr_id);
                        $('#changeManager').show();
                        $('#quickAction-btn').show();
                    }
                    break;
                case '4':
                    resetFilter();
                    if (notAllowMultiCheckbox(checked) === true) {
                        var value = checked.val();
                        var href = $('#quicklabelModal').attr('data-href');
                        $('#quicklabelModal').attr('href', href + '?id=' + value).trigger('click');
                        $('#selection').val('');
                    }
                    break;
                case '5':
                    resetFilter();
                    break;
                case '6':
                    resetFilter();
                    $('#quickAction-btn').show();
                    break;
                case '7':
                    resetFilter();
                    $('#quickAction-btn').show();
                    break;
                default :
                    resetFilter();
                    break;
            }
        });
        $('#quickAction-btn').click(function(e) {
            e.preventDefault();
            var now = new Date().getTime();
            var url = buildUrl('<?php echo $this->Html->url(array('controller' => 'teams', 'action' => 'quickEdit'), true); ?>', '_t', now);
            var filter_val = $('#selection').find(':selected').attr('value');
            var checked = $('.check:checked');
            var id = checked.val();
            var input;
            var data = [];
            var value = $("#selection").val();
            switch (value) {
                case '2':
                    input = $("#newName").val();
                    if (input.replace(/ /g, '') == '')
                    {
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
                        confirm: function(confirmButton) {
                            var ids = [];
                            checked.each(function() {
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
                case '3':
                    input = $('#myManager option:selected').attr('value');
                    var cr_id = checked.closest("tr").find('._manager').data('id');
                    data = {id: id, type: filter_val, cr_data: cr_id, input: input};
                    ajaxEdit(url, data);
                    break;
                case '6':
                    var ids = [];
                    checked.each(function() {
                        ids.push($(this).val());
                    });
                    data = {ids: ids, type: filter_val};
                    ajaxEdit(url, data);
                    break;
                case '7':
                    var _url = buildUrl('<?php echo $this->Html->url(array('controller' => 'teams', 'action' => 'delete'), true); ?>', '_t', now);
                    $.confirm({
                        text: '<?php echo __("選択した項目を削除してもよろしいですか？") ?>',
                        title: '<?php echo __("確認") ?>',
                        confirm: function(confirmButton) {
                            var ids = [];
                            checked.each(function() {
                                ids.push($(this).val());
                            });
                            var datas = {id: ids};
                            $.ajax({
                                type: 'POST',
                                dataType: 'JSON',
                                url: _url,
                                data: datas,
                                success: function(rs) {
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
            }
        });

        $('.modal-detail').find('td').on('click', function() {
            var $this = $(this),
                    $detail = $('#detailModal'),
                    href = $this.closest('.modal-detail').data('modal');

            if ($this.hasClass('ignore-modal'))
            {
                return;
            }
            if (href)
            {
                $detail.attr('href', href).trigger('click');
            }
        })
    });
</script>
