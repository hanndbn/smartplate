<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __("コンテンツを変更する") ?></h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list bookmarkcontent">
                <?php
                echo $this->Form->create('Tag', array('action' => 'quickedit_link_bm', 'class' => 'form-horizontal'));
                ?>
                <div class="quick-height">
                    <?php if (!empty($list_bms)) { ?>
                        <table id="bookmarkList" class="table-striped">
                            <thead>
                            <tr>
                                <td class="search" colspan="12">
                                    <?php echo __('絞り込み条件'); ?>:
                                    <select id="changeFilterKind" class="" name="data[Filter][kind]">
                                        <option value=""><?php echo __('種別を選択') ?></option>
                                        <?php foreach ($type as $key => $value) { ?>
                                            <option value="<?php echo $key ?>"><?php echo __($value) ?></option>
                                        <?php } ?>
                                    </select>
                                    ＋
                                    <div class="dropdown inline-block strip">
                                        <a id="dLabel_" role="button" data-toggle="dropdown" class="btn form-control"
                                           data-target="#" label-id="">
                                            <?php echo __('ラベルを選択') ?>
                                        </a>
                                        <span class="caret"></span>
                                        <?php echo $this->Label->renderDropdownLabels($list_lb); ?>
                                    </div>
                                    <?php echo $this->Form->input("Filter.label", array('label' => false, 'type' => 'hidden')); ?>
                                    ＋
                                    <?php
                                    echo $this->Form->input("Filter._name", array('id' => 'contentName', 'div' => false, 'label' => false, 'placeholder' => "検索キー"));
                                    echo $this->Html->link(__('検索'), '#self', array('class' => 'imgBtn wide submitFilter m-l-sm'));
                                    echo $this->Html->link(__('リセット'), '#self', array('class' => 'imgBtn wide resetFilter m-l-sm'));
                                    ?>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <th class="typeB highlight"><?php echo __('名前') ?></th>
                                <th class="typeB highlight"><?php echo __('URL') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($list_bms as $list_bm) { ?>
                                <tr id="<?php echo 'bookmark_' . $list_bm['id'] ?>" class="text-center"
                                    data-id="<?php echo $list_bm['id'] ?>" data-name="<?php echo $list_bm['name'] ?>"
                                    data-url="<?php ?>" data-kind="<?php echo $list_bm['kind'] ?>"
                                    data-label="<?php echo $list_bm['label_id'] ?>" data-toggle='collapse'
                                    data-target="#bookmark<?php echo $list_bm['id'] ?>">
                                    <td class="name" title="<?php echo $list_bm['name']; ?>">
                                        <?php echo Utility_Str::wordTrim(Utility_Str::escapehtml($list_bm['name']), 50); ?>
                                    </td>
                                    <td class="url" title="<?php ?>">
                                        <?php echo $this->Form->input('LinkHidden', array('type' => 'hidden', 'value' => $list_bm['Link:hidden'])); ?>
                                        <?php
                                        if ($list_bm['Link:url']) {
                                            $link_url = $list_bm['Link:url'];
                                            if (count($link_url) > 1) {
                                                ?>
                                                <div>
                                                    <?php echo Utility_Str::wordTrim(Utility_Str::escapehtml($link_url[0]['url']), 50) ?>
                                                    <span class="collapseIcon">
                                                        <i class='fa fa-bars pull-right'></i>
                                                    </span>
                                                </div>
                                                <div class='collapse' id='bookmark<?php echo $list_bm['id'] ?>'>
                                                    <?php
                                                    for ($i = 1; $i < count($list_bm['Link:url']); $i++) {
                                                        echo "<div>";
                                                        echo Utility_Str::wordTrim(Utility_Str::escapehtml($link_url[$i]['url']), 50);
                                                        echo "</div>";
                                                    }?>
                                                </div>
                                            <?php
                                            } else {
                                                echo "<div>";
                                                echo Utility_Str::wordTrim(Utility_Str::escapehtml($link_url[0]['url']), 50);
                                                echo "</div>";
                                            }
                                        }// END IF
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                            <?php echo $this->Form->input('selectall', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'value' => $selectall, 'id' => 'selectall')); ?>
                            <?php echo $this->Form->input('Tag.bm_id', array('div' => false, 'id' => 'hiddenID', 'type' => 'hidden', 'label' => FALSE)); ?>
                            <?php echo $this->Form->input('Tag.target_id', array('div' => false, 'id' => 'targetID', 'type' => 'hidden', 'label' => FALSE, 'value' => $tag_ids)); ?>
                        </table>
                    <?php
                    }
                    ?>
                </div>
                <div class="modal-footer">
                    <?php if ($status == 0) { ?>
                        <button id="submitBTN" type="submit" class="btn btn-default"><?php echo __('OK') ?></button>
                    <?php } else { ?>
                        <button id="getBookmarkBTN" type="button" class="btn btn-default"
                                data-dismiss="modal"><?php echo __('OK') ?></button>
                    <?php } ?>
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo __('Cancel') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var buildUrl = function (base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    };
    // Get bookmark content
    $(document).on('click', '#getBookmarkBTN', function () {
        var now = new Date().getTime(),
            id = $('#hiddenID').val(),
            data = {id: id},
            url = buildUrl('<?php echo $this->Html->url(array('controller' => 'tags', 'action' => 'ajaxBookmarkContent'), true); ?>', '_t', now);
        $('#TagBookmarkId').val(id);
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: url,
            data: data,
            success: function (rs) {
                var type = parseInt(rs[0].type),
                    html = '',
                    linkUrl = '',
                    subtype = '',
                    icon = '',
                    link_text = '',
                    subtype_label = '',
                    wrapper = $('.edit-wrap');
                wrapper.empty();

                switch (type) {
                    case 0:
                        linkUrl = rs[0].url;
                        html += "<div class='row'><div class='col-sm-2'>URL</div><div class='url col-sm-10'>" + linkUrl + "</div></div>";
                        break;
                    case 1:
                        $.each(rs, function (key, val) {
                            linkUrl = rs[key].url;
                            subtype = rs[key].sub_type;
                            switch (subtype) {
                                case '1':
                                    subtype_label = 'Android';
                                    break;
                                case '2':
                                    subtype_label = 'IOS';
                                    break;
                                case '3':
                                    subtype_label = 'Other';
                                    break;
                                default:
                                    break;
                            }
                            html += "<div class='row m-b-md'>\n\
					<div class='col-sm-3 text-center'>" + subtype_label + "</div>\n\
					<div class='url col-sm-9'>" + linkUrl + "</div>\n\
				</div>";
                        });
                        break;
                    case 2:
                        $.each(rs, function (key, val) {
                            var baseUrl = '<?php echo $this->webroot ?>';
                            linkUrl = rs[key].url;
                            icon = rs[key].icon;
                            link_text = rs[key].link_text;
                            if (linkUrl != '' && link_text != '') {
                                html += "<div class='row form-group'>\n\
						<div class='col-sm-8'>\n\
							<div class='linkTitle bold m-b-md'>" + link_text + "</div>\n\
							<div>" + linkUrl + "</div>\n\
						</div>";
                                html += (icon) ? '<img src="' + baseUrl + 'timthumb/image?src=' + baseUrl + 'app%2Fwebroot%2Fimg%2Ficon%2F' + icon + '.png&amp;q=80&amp;a=c&amp;zc=1&amp;ct=1&amp;w=80&amp;h=80" class="img-thumbnail" alt="">' : "<img class='default img-thumbnail' />";
                                html += "</div>";
                            }

                        });
                        break;
                    case 5:
                    case 6:
                        $.each(rs, function (k, v) {
                            html += "<div class='row m-b-md'>";
                            html += "<div class='col-sm-3 text-center'>" + (k + 1) + "</div>";
                            html += "<div class='url col-sm-9'>" + rs[k].url + "</div>";
                            html += "</div>";
                        });
                        break;
                    default:
                        break;
                }
                wrapper.html(html);
            }
        });
    });
    function filter(target, keyText, labelId, kindId, caseFilter) {
        $.each(target, function () {
            var targetLabelId = $(this).data('label').toString().split(',');
            var resultURL = $(this).find('input').val().match(new RegExp(keyText, 'i')),
                resultName = $(this).data('name').match(new RegExp(keyText, 'i'));
            switch (caseFilter) {
                case 1:
                    (($.inArray(labelId, targetLabelId) !== -1) && (resultURL != null || resultName != null)) ? (this).show() : $(this).hide();
                    break;
                case 2:
                    ((kindId == $(this).data('kind')) && (resultURL != null || resultName != null)) ? (this).show() : $(this).hide();
                    break;
                case 3:
                    ((kindId == $(this).data('kind')) && ($.inArray(labelId, targetLabelId) !== -1)) ? (this).show() : $(this).hide();
                    break;
                case 4:
                    (kindId == $(this).data('kind')) ? $(this).show() : $(this).hide();
                    break;
                case 5:
                    ($.inArray(labelId, targetLabelId) !== -1) ? $(this).show() : $(this).hide();
                    break;
                case 6:
                    (resultURL != null || resultName != null) ? $(this).show() : $(this).hide();
                    break;
                case 7:
                    ((kindId == $(this).data('kind')) && ($.inArray(labelId, targetLabelId) !== -1) && (resultURL != null || resultName != null)) ? (this).show() : $(this).hide();
                    break;
            }
        });
    }
    $(document).ready(function () {

        $('#submitBTN').addClass('disabled');
        $('#getBookmarkBTN').addClass('disabled');
        $('#bookmarkList tbody tr').on('click', function () {
            var that = $(this);
            var id = that.data('id');
            $('#hiddenID').val(id);
            $('.selected').removeClass('selected');
            that.addClass('selected');
            $('#submitBTN').removeClass('disabled');
            $('#getBookmarkBTN').removeClass('disabled');
        });

        // Get label selection id
        $('.label_name').click(function (e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            $('#FilterLabel').val(id);
            $('#dLabel_').attr('label-id', id);
            $('#dLabel_').html($(this).html());
        });
        //Filter
        $('.submitFilter').click(function (e) {
            e.preventDefault();

            var labelId = $('#dLabel_').attr('label-id'),
                kindId = $('#changeFilterKind').val(),
                keyText = $('#contentName').val(),
                target = $('#bookmarkList tbody tr'),
                caseFilter;
            if (kindId === '' && labelId !== '' && keyText !== '') {
                caseFilter = 1;
            }
            else if (kindId !== '' && labelId === '' && keyText !== '') {
                caseFilter = 2;
            }
            else if (kindId !== '' && labelId !== '' && keyText === '') {
                caseFilter = 3;
            }
            else if (kindId !== '' && labelId === '' && keyText === '') {
                caseFilter = 4;
            }
            else if (kindId === '' && labelId !== '' && keyText === '') {
                caseFilter = 5;
            }
            else if (kindId === '' && labelId === '' && keyText !== '') {
                caseFilter = 6;
            }
            else if (kindId !== '' && labelId !== '' && keyText !== '') {
                caseFilter = 7;
            }
            filter(target, keyText, labelId, kindId, caseFilter);
        });

        // Reset filter
        $('.resetFilter').on('click', function () {
            $('#contentName').val('');
            $('#bookmarkList tr').show();
        });

        $(document).on('click', 'tr[data-toggle=collapse]', function () {
            if ($(this).hasClass('collapsed')) {
                $(this).find('.collapseIcon').show();
            } else {
                $(this).find('.collapseIcon').hide();
            }
        });
    });
</script>