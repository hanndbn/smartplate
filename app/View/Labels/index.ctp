<?php
$session = $this->Session->read('Auth.User');
//print_r($this->Session->read('visible')); die;
$team_id = @$session['team_id'];
$data = ($this->request->prefix == 'system') ? $this->requestAction(array('prefix' => 'system', 'controller' => 'accesslogs', 'action' => 'contentStatus')) : $this->requestAction(array('controller' => 'accesslogs', 'action' => 'contentStatus'));
switch ($type) {
    case 'BookmarkModel':
        $name = __('登録コンテンツ数');
        $header = __('コンテンツ用ラベル一覧');
        $url = $this->Html->url(array('controller' => 'bookmarks', 'action' => 'label'));
        break;

    case 'TagModel':
        $name = __('登録プレート数');
        $header = __('プレート用ラベル一覧');
        $url = $this->Html->url(array('controller' => 'tags', 'action' => 'label'));
        break;

    case 'UserModel':
        $header = __('アプリアカウント用ラベル一覧');
        $name = __('所属ユーザー数');
        $url = $this->Html->url(array('controller' => 'apps', 'action' => 'label'));
        break;
    case 'TeamModel':
        $header = __('プロジェクト用ラベル一覧');
        $name = __('登録プロジェクト数');
        $url = $this->Html->url(array('controller' => 'teams', 'action' => 'label'));
        break;

    default:
        $header = '';
        $name = '';
        $url = $this->webroot;
        break;
}
?>
<div id="main" class="list">
    <h2><?php echo $header ?></h2>

    <?php echo $this->Session->flash(); ?>

    <table class="operation">
        <tr>
            <td>
                <span class="text"><?php echo __('選択項目'); ?>:</span>
                <select id="selection">
                    <option value=""></option>
                    <option value="0"><?php echo __('だけを表示') ?></option>
                    <option value="1"><?php echo __('以外を表示') ?></option>
                    <option value="2"><?php echo __('のラベル名を変更する') ?></option>
                    <option value="5"><?php echo __('を無効・有効にする') ?></option>
                    <option value="6"><?php echo __('を削除する') ?></option>
                </select>
        <nobr>
            <span id="changeName" style="display:none;">　<?php echo __('ラベル名') ?>:
                <input type="text" id="newName" name="" value="（現在のラベル名）" style="width: 200px;" autocomplete="off" maxlength="64" data-toggle = 'checklengh'>　
                <a href="#" class="imgBtn wide hide"><?php echo __('設定') ?></a>
            </span>
        </nobr>
        <span id="execute" style="display:none;">　<a href="#" class="imgBtn wide"><?php echo __('設定') ?></a></span>
        </td>
        </tr>
    </table>

    <div class="row m-t-md m-b-lg">        
        <div class="col-xs-10 col-xs-offset-1">
            <?php echo $this->Form->create('Filter'); ?>
            <?php echo __('絞り込み条件') ?>:
            <nobr>
                <select id="filterVisible" name="label_status">
                    <option value=""><?php echo __('指定しない') ?></option>
                    <option value="1"><?php echo __('有効') ?></option>
                    <option value="0"><?php echo __('無効') ?></option>
                </select>
                ＋
                <input type="text" name="search" placeholder="検索キー" onfocus="this.value = '';" autocomplete="off" style="width: 300px;">
            </nobr>　
            <nobr>
                <a id="filterBtn" href="javascript:;" class="imgBtn wide formSubmit"><?php echo __('検索') ?></a>　
                <a id="resetFilter" href="<?php echo $url ?>" class="imgBtn wide subFilter"><?php echo __('リセット') ?></a>
            </nobr>
            <?php echo $this->Form->end(); ?>
        </div>
        <div class="col-xs-1 trash">
            <div id="trash">
                <i class="fa fa-4x fa-trash"></i>
            </div>
        </div>        
        <div class="col-xs-9 col-xs-offset-1">
            <div class="row typeB">
                <div class="col-xs-6 col-typeB text-center"><?php echo __('名前') ?></div>
                <div class="col-xs-3 col-typeB text-center"><?php echo $name; ?></div>
                <div class="col-xs-2 col-typeB text-center">
                    <?php echo __('選択') ?>
                    <input type="checkbox" class="CheckAll" style="position: absolute; top: 2px; margin: 0 4px;" data-target=".sortable" autocomplete="off"/>
                </div>
                <div class="col-xs-1 col-typeB text-center"><?php echo __('有効') ?></div>
            </div>
        </div>    
        <div class="col-xs-2"></div>        
        <div class="col-xs-9 col-xs-offset-1">
            <?php
            echo $this->element('nested', array(
                'labels' => $labels,
                'count' => $count,
                'type' => $type
            ))
            ?>            
        </div>

        <div class="col-xs-1"><button class="btn" id="addLabel" style="background: transparent;"><i class="fa fa-2x fa-plus"></i></button></div>
        <div class="col-xs-1"></div>
    </div>
</div>

<button class="btn hide" id="nestableConfirm" data-toggle="modal" data-target="#nestableModal"></button>

<div class="modal fade" id="nestableModal" data-reload="1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <?php echo $this->Form->create('Label', array('controller' => 'LabelsController', 'action' => 'nestable')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo __('確認') ?></h4>
            </div>
            <div class="modal-body">
                Are you sure?
                <?php echo $this->Form->input('Label.type', array('type' => 'hidden', 'value' => $type, 'label' => FALSE)) ?>
                <input type="hidden" name="serialize" id="serialize">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-default">Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>                
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<?php
echo $this->Html->link('Edit', array(), array('class' => 'invisible', 'id' => 'editLabel', 'data-toggle' => 'ajaxModal'));
echo $this->Html->link('Delete', array(), array('class' => 'invisible', 'id' => 'deleteLabel', 'data-toggle' => 'ajaxModal', 'data-type' => $type, 'data-reload' => 1));

echo $this->Html->script(array('jquery-ui.min', 'jquery.mjs.nestedSortable'/* 'jquery.nestable' */));

echo $this->fetch('script');
?>

<script>
                    $(function() {

                        var draggedItem, isOverTrash;

                        $('.sortable').nestedSortable({
                            forcePlaceholderSize: true,
                            handle: '.dd-handle',
                            helper: 'clone',
                            items: 'li.dd-item',
                            //containment: 'window',
                            scroll: false,
                            opacity: .6,
                            placeholder: 'placeholder',
                            revert: 250,
                            tabSize: 25,
                            tolerance: 'pointer',
                            toleranceElement: '> div',
                            isTree: true,
                            sort: function(e, ui) {
                                draggedItem = ui.item;
                                $(window).mousemove(moved);
                            },
                            start: function(e, ui) {
                                isOverTrash = false;
                            },
                            stop: function(e) {

                                $(window).unbind("mousemove", moved);

                                if (isOverTrash)
                                {
                                    var $delete = $('#deleteLabel'),
                                            deleteUrl = draggedItem.data('delete');


                                    if (deleteUrl)
                                    {
                                        $delete.attr('href', deleteUrl + '?type=' + $delete.data('type'));
                                        $delete.trigger('click');

                                        return;
                                    }
                                }

                                var toHierarchy = JSON.stringify($(this).nestedSortable('toHierarchy'));

                                //console.log(e);
                                if (toHierarchy != $('#serialize').val())
                                {
                                    $('#nestableConfirm').trigger('click');
                                }

                                $('#serialize').val(toHierarchy);
                            }
                        });

                        function moved(e) {

                            //Dragged item's position++
                            /*var d = {
                             top: draggedItem.position().top,
                             bottom: draggedItem.position().top + draggedItem.height(),
                             left: draggedItem.position().left,
                             right: draggedItem.position().left + draggedItem.width()
                             };*/

                            var $target = $(e.target),
                                    $delete = $('#deleteLabel');
                            if ($target.closest('.trash').length
                                    || $target.hasClass('.trash')
                                    )
                            {
                                $('#trash').addClass('trash-scale');
                                isOverTrash = true;
                            }
                        }
                        ;

                        //hierarchy = JSON.stringify($('.sortable').nestedSortable('toHierarchy'));
                        if ($('.sortable').length)
                            $('#serialize').val(JSON.stringify($('.sortable').nestedSortable('toHierarchy', {startDepthCount: 0})));

                        $('#addLabel').on('click', function(e) {
                            e.preventDefault();
                            var $this = $(this),
                                    $fa = $this.find('.fa');

                            $('#newLabel input[type=text]').val('');

                            if ($fa.hasClass('fa-plus'))
                            {
                                $('#newLabel').removeClass('hide').find(':text').focus();
                                $fa.removeClass('fa-plus').addClass('fa-minus');
                            }
                            else if ($fa.hasClass('fa-minus'))
                            {
                                $('#newLabel').addClass('hide');
                                $fa.removeClass('fa-minus').addClass('fa-plus');
                            }

                        });

                        $('#newLabel input[type=text]').on('keydown', function(e) {

                            if ((e.keyCode == 13 || e.keyCode == 9) && $(this).val() != '')
                            {
                                $(this).closest('form').submit();
                            }

                            if (e.keyCode == 27)
                            {
                                if ($('#addLabel').find('.fa').hasClass('fa-minus'))
                                {
                                    $('#addLabel').trigger('click');
                                }
                            }
                        });
                        $('#newLabel input[type=text]').on('change', function(e) {
                            if( $(this).val() != '' ) {
                              $(this).closest('form').submit();
                            }
                        });
                        $('.dd-item').find('.lb-node').click(function(e) {
                            //console.log($(this).attr('class'));
                            var $edit = $('#editLabel'),
                                    editUrl = $(this).data('edit');

                            if ($(this).find('input').hasClass('ignore-modal'))
                            {
                                return;
                            }
                            else
                            {
                                $edit.attr('href', editUrl).trigger('click');

                            }

                        });

                        $('li.dd-item').each(function() {
                            var $this = $(this),
                                    $list = $this.find('.list-inline'),
                                    $label = $this.find('.label-name'),
                                    width = parseInt($this.width()) - parseInt($list.width());

                            $label.width((width - 65) + 'px');
                        });

                        $('.label-item, .CheckAll').on('change', function() {
                            //resetFilter(true);
                            var selection = parseInt($('#selection').val()),
                                    $checked = $('.label-item:checked'),
                                    name = $checked.closest('.dd3-content').find('.label-name').html();

                            if (selection == 5 || selection == 6)
                            {
                                if ($(this).hasClass('CheckAll'))
                                {
                                    if ($checked.length == $('.label-item').length)
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

                        $('#selection').on('change', function(e) {
                            //alert('change');                

                            var $this = $(this),
                                    $check = $('.label-item'),
                                    $execute = $('#execute'),
                                    $changeName = $('#changeName');

                            if (!$('.label-item:checked').length)
                            {
                                resetFilter(true);
                                alert('<?php echo __('少なくとも１つの項目を選択してください') ?>');
                                return;
                            }

                            switch (parseInt($this.val()))
                            {
                                case 0:
                                    resetFilter();
                                    //$execute.show();

                                    $check.each(function() {
                                        if (!$(this).is(':checked'))
                                        {
                                            var $item = $(this).closest('li.dd-item');
                                            if (!$item.find('ol').find('.label-item:checked').length)
                                            {
                                                $item.hide();
                                            }
                                        }
                                    });
                                    break;

                                case 1:
                                    resetFilter();
                                    //$execute.show();

                                    $check.each(function() {
                                        if ($(this).is(':checked'))
                                        {
                                            var $item = $(this).closest('li.dd-item');
                                            if (!$item.find('ol').find('.label-item:not(:checked)').length)
                                            {
                                                $item.hide();
                                            }
                                        }
                                    });
                                    break;

                                case 2:
                                    resetFilter();
                                    var $checked = $('.label-item:checked'),
                                            name = $checked.closest('.dd3-content').find('.label-name').html().toString().replace(/\&lt;/g, '<').replace(/\&gt;/g, '>');

                                   /* if ($checked.length > 1)
                                    {
                                        resetFilter(true);
                                        alert('複数のチェックボックスを選択することはできません。');
                                        return;
                                    }*/

                                    $changeName.show();
                                    $execute.show();
                                    $('#newName').focus().val(name);

                                    break;

                                case 5:
                                case 6:
                                    resetFilter();
                                    $execute.show();

                                    break;

                                default:
                                    resetFilter(true);
                                    $execute.hide();

                                    break;
                            }
                        });

                        $('#execute').on('click', function(e) {

                            e.preventDefault();

                            var selection = parseInt($('#selection').val()),
                                    $check = $('.label-item'),
                                    ajaxData = {
                                id: []
                            },
                            url;

                            if ($.inArray(selection, [2, 5, 6]) != -1)
                            {
                                $check.each(function() {
                                    if ($(this).is(':checked'))
                                    {
                                        ajaxData.id.push($(this).val());
                                    }
                                });

                                switch (selection)
                                {
                                    case 2:
                                        if ($('#newName').val().replace(/ /g, '') == '')
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

                                        url = '<?php echo $this->Html->url(array('controller' => 'labels', 'action' => 'ajaxLabel'), true); ?>';
                                        ajaxData.label = $('#newName').val();
                                        break;

                                    case 5:
                                        url = '<?php echo $this->Html->url(array('controller' => 'labels', 'action' => 'ajaxStatus'), true); ?>';
                                        ajaxData.hierarchy = JSON.stringify($('.sortable').nestedSortable('toHierarchy'));
                                        $('#selection').val('');
                                        break;

                                    case 6:
                                        //url = '<?php echo $this->Html->url(array('controller' => 'labels', 'action' => 'ajaxDelete'), true); ?>';

                                        var text = '<?php echo __("選択した項目を削除してもよろしいですか？") ?>';

                                        $('.label-item:checked').each(function() {
                                            var $this = $(this),
                                                    $li = $this.closest('li');

                                            if ($li.find('ol').length)
                                            {
                                                text = '<?php echo __('下の階層にラベルがあります。すべて削除されますがよろしいですか？') ?>';
                                            }
                                        });

                                        $.confirm({
                                            text: text,
                                            title: '<?php echo __("確認") ?>',
                                            confirm: function(confirmButton) {
                                                $.ajax({
                                                    type: 'POST',
                                                    dataType: 'JSON',
                                                    url: '<?php echo $this->Html->url(array('controller' => 'labels', 'action' => 'ajaxDelete'), true); ?>',
                                                    data: ajaxData,
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
                                }
                            }

                            if (ajaxData && url)
                            {
                                $.ajax({
                                    type: 'post',
                                    dataType: "json",
                                    url: url,
                                    data: ajaxData,
                                    success: function(response) {
                                        location.reload();
                                    }
                                });
                            }
                        });

                        function resetFilter(resetSelection)
                        {
                            if (resetSelection)
                            {
                                $('#selection').val('');
                            }
                            $('.label-item').show();
                            $('.label-item').closest('li.dd-item').show();
                            $('#changeName').hide();
                            $('#execute').hide();
                        }

<?php if ($team_id == null) { ?>
                            $('#addLabel').click(function() {
                                alert('<?php echo __('指定したパスのアクセス許可を持っていません') ?>');
                                return false;
                            });
<?php } ?>

                        //Storage filter session
                        $('#resetFilter').click(function() {
                            sessionStorage.removeItem("visible");
                        });
                        $('#filterBtn').click(function() {
                            var $val = $('#filterVisible').val();
                            sessionStorage.setItem("visible", $val);
                        });
                        if (sessionStorage.getItem('visible') !== null)
                            $('#filterVisible').val(sessionStorage.getItem("visible"));
                    });
</script>