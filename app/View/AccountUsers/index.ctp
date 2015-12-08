<?php
$base_url = array('controller' => 'account_users', 'action' => 'index');

/* Get sort */
?>
<div id="main" class="list">
    <h2><?php echo __('アカウント一覧') ?></h2>
    <?php echo $this->Session->flash() ?>
    <table class="operation">
        <tbody>
            <tr class="container-fluid">
        <input id="target_id" style="width: 200px;" type="hidden" name="data[Bookmark][id]">
        <td class="row">   
            <span class="text"><?php echo __('選択項目'); ?>：</span>
            <select id="selection" class="">
                <option value=""></option>
                <option value="0"><?php echo __('だけを表示') ?></option>
                <option value="1"><?php echo __('以外を表示') ?></option>
                <option value="2"><?php echo __('の管理名を変更する') ?></option>
                <option value="6"><?php echo __('を無効・有効にする') ?></option>
                <option value="7"><?php echo __('を削除する') ?></option>
            </select>
            <span id="changeName" style="display:none;" class="">　
                <?php echo __('氏:'); ?>
                <input id="newFName" style="width: 200px;" type="text" name="data[AccountUser][f_name]" autocomplete="off" maxlength="45" data-toggle = 'checklengh'>　
                <?php echo __('名:'); ?>
                <input id="newLName" style="width: 200px;" type="text" name="data[AccountUser][l_name]" autocomplete="off" maxlength="45" data-toggle = 'checklengh'>
            </span>
            <a id="quickAction-btn" href="#" style="display:none;" class="imgBtn wide m-l-sm"><?php echo __('設定') ?></a>
        </td>         
        </tr>
        </tbody>
    </table>

    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>

    <div class="table-hover">
        <table id="accountUser-list" class="table-striped">
            <thead>
                <tr>
                    <td class="search" colspan="12">
                        <?php
                        // The base url is the url where we'll pass the filter parameters

                        echo $this->Form->create("Filter", array('url' => $base_url, 'role' => "form"));
                        // add a select input for each filter. It's a good idea to add a empty value and set
                        // the default option to that.
                        ?>
                        <?php echo __('絞り込み条件'); ?>
                        <?php echo $this->element('country', array('country' => '', 'name' => 'data[Filter][country]')) ?>
                        <span class="plus">＋</span>
                        <?php
                        // Add a basic search 
                        echo $this->Form->input("name", array('label' => false, 'placeholder' => "検索キー", 'autocomplete' => 'off'));
                        ?>

                        <span class="m-sm">
                            <a href="javascript:;" class="imgBtn wide formSubmit m-r-xs"><?php echo __('検索') ?></a>
                            <?php echo $this->Html->link('リセット', array('action' => 'index'), array('class' => 'imgBtn wide subFilter')) ?>
                        </span>


                        <?php
                        echo $this->Form->end();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="typeB highlight"><?php echo __('選択') ?><br /><?php echo $this->Form->checkbox('all', array('class' => 'CheckAll', 'data-target' => '#accountUser-list tbody')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('status', '有効'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('id', 'No.'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('company', '会社名'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('family_name', __('氏')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('given_name', __('名')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('country', __('国')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('region', __('地域')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('cdate', __('登録日')); ?></th>                  
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($users as $user):
                    $id = $user['id'];
                    ?>
                    <tr id="<?php echo 'toggle' . $id ?>" class="modal-detail text-center" data-modal="<?php echo $this->webroot . 'system/account_users/detail/' . $id ?>">
                        <td class="ignore-modal"><?php echo $this->Form->checkbox('User_' . $id, array('class' => 'check', 'value' => $id, 'hiddenField' => false)); ?></td>
                        <td class="visible"  data-value="<?php echo $user['status'] === '' ? 0 : 1 ?>">
                            <?php echo $user['status'] == 1 ? '<i class="fa fa-circle-thin">' : '<i class="fa fa-times">'; ?>
                        </td>
                        <td><?php echo $id; ?></td>
                        <td class="company"><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($user['company']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($user['company']), 30) . '</span>' ?></td>         
                        <td class="f_name" data-name="<?php echo Utility_Str::escapehtml($user['family_name']) ?>"><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($user['family_name']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($user['family_name']), 20) . '</span>'; ?></td>
                        <td class="l_name" data-name="<?php echo Utility_Str::escapehtml($user['given_name']) ?>"><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($user['given_name']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($user['given_name']), 20) . '</span>'; ?></td>                        
                        <td><?php echo $user['country'] ?></td>
                        <td><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($user['region']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($user['region']), 30) . '</span>'; ?></td>                        
                        <td><?php echo date('Y/m/d H:i:s', strtotime($user['regist_date'])) ?></td>
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
            $('#selection').val('');
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
        $('#changeName').hide();
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
        if ($.urlParam('country') !== null) {
            $('#country').val($.urlParam('country').replace(/\%20/g, ' '));
        }

        $('.check, .CheckAll').on('change', function() {
            //resetFilter(true);
            var selection = parseInt($('#selection').val()),
                    $checked = $('.check:checked');
            console.log(selection);
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
                    if (notAllowMultiCheckbox(checked) === true) {
                        var f_name = checked.closest("tr").find('.f_name').data('name').toString().replace(/\&lt;/g, '<').replace(/\&gt;/g, '>'),
                                l_name = checked.closest("tr").find('.l_name').data('name').toString().replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');
                        $('#newFName').val(f_name);
                        $('#newLName').val(l_name);
                        $('#quickAction-btn').show();
                        $('#changeName').show();
                    }
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
            var url = buildUrl('<?php echo $this->Html->url(array('prefix' => 'system', 'controller' => 'account_users', 'action' => 'quickEdit'), true); ?>', '_t', now);
            var filter_val = $('#selection').find(':selected').attr('value');
            var checked = $('.check:checked');
            var id = checked.val();
            var input = '';
            var data = [];
            var value = $("#selection").val();

            switch (value) {
                case '2':
                    if ($('#newFName').val().replace(/ /g, '') === '' || $('#newLName').val().replace(/ /g, '') === '')
                    {
                        $.confirm({
                            text: '<?php echo __("このフィールドを入力してください。") ?>',
                            title: '<?php echo __("確認") ?>',
                            confirmButton: "",
                            cancelButton: "OK",
                            confirmButtonClass: 'btn-default hide'
                        });

                        return;
                    }
                    input = [$("#newFName").val(), $("#newLName").val()];
                    data = {id: id, type: filter_val, input: input};
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
                    var _url = buildUrl('<?php echo $this->Html->url(array('prefix' => 'system', 'controller' => 'account_users', 'action' => 'delete'), true); ?>', '_t', now);
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
        });

    });
</script>
