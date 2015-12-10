<?php $session = $this->Session->read('Auth.User'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('プレート編集'); ?></h4>
        </div>
        <div class="modal-body">
            <?php echo $this->Form->create('Tag', array('type' => 'file', 'class' => 'form-horizontal')); ?>
            <div class="main-modal">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="wrapper control-label col-sm-2"><span><?php echo __('名前') ?>:</span></div>
                            <div class="wrapper col-sm-10">
                                <?php echo $this->Form->input('Tag.name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'data-toggle' => 'checklengh')); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="wrapper control-label col-sm-2"><span><?php echo __('有効') ?>:</span></div>
                            <div class="wrapper col-sm-10">
                                <div class="checkbox">
                                    <?php echo $this->Form->input('Tag.available', array('div' => false, 'id' => 'EditPlateForm', 'type' => 'checkbox', 'label' => FALSE, 'class' => 'm-n')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="control-label col-sm-3">
                                <span><?php echo __('画像') ?>:</span>
                            </div>
                            <div class="detail-image-content col-sm-8">
                                <?php
                                echo $this->Form->input('Tag.icon', array('type' => 'file', 'id' => "TagImageUpload", 'div' => false, 'label' => false, 'class' => 'm-b-sm'));
                                echo (!empty($tag['Tag']['icon'])) ? '<img  class="img-thumbnail" src="/upload/plate/' . $tag['Tag']['icon'] . '"/>' : '<img class="avatar"/>';
                                ?>
                                <!--<input id="BookmarkImage" type="file" required="required" onchange="imageURL(this)" name="data[Bookmark][image]">-->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row m-t-lg">
                    <div class="col-sm-6">
                        <div class="clearfix">
                            <span><?php echo __('リンク') ?>:</span>
                            <?php echo $this->Html->link(__('選択'), array('controller' => 'tags', 'action' => 'quickedit_link_bm', "?id=$id&edit=1"), array('style' => 'margin-top: -10px;', 'id' => 'changeBookmark', 'class' => 'imgBtn pull-right', 'div' => false, 'label' => FALSE, 'data-id' => $id)); ?>
                        </div>
                        <div class="content-detail-wrap p-sm">
                            <div class="form-group">
                                <div class="control-label col-sm-3">
                                            <span><?php echo __('タイトル')
                                                ?>:</span>
                                </div>
                                <div class="detail-code-content col-sm-12">
                                    <?php echo $this->Form->input("BookmarkExtData.ext_data", array('div' => false, 'label' => false, 'type' => 'text', 'value' => isset($bookmarkExtData['ext_data']) ? $bookmarkExtData['ext_data'] : ''));?>
                                </div>
                            </div>
                            <div class="edit-wrap">
                                <?php
                                if ($bookmarkContents != null) {
                                    foreach ($bookmarkContents as $bookmarkContent) {
                                        $type = $bookmarkContent['type'];
                                        $sub_type = $bookmarkContent['sub_type'];
                                        $url = $bookmarkContent['url'];
                                        switch ($type) {
                                            case '0':
                                                echo "<div class='row form-group'><div class='col-sm-2'>URL</div>";
                                                echo "<div class='url col-sm-10'>" . $url . "</div></div>";
                                                break;
                                            case '1':
                                                switch ($sub_type) {
                                                    case '1':
                                                        $sub_type = 'Android';
                                                        break;
                                                    case '2':
                                                        $sub_type = 'IOS';
                                                        break;
                                                    case '3':
                                                        $sub_type = 'Other';
                                                        break;
                                                    default:
                                                        break;
                                                }
                                                echo "<div class='row form-group'><div class='col-sm-3 text-center'>" . $sub_type . "</div>";
                                                echo "<div class='url col-sm-9'>" . $url . "</div></div>";
                                                break;
                                            case '2':
                                                $link_text = $bookmarkContent['link_text'];
                                                $icon = $bookmarkContent['icon'];
                                                if ($link_text != '' || $url != '') {
                                                    echo "<div class='row form-group'>
                                                    <div class='col-sm-8'>
                                                        <div class='linkTitle bold m-b-md'>" . $link_text . "</div>
                                                        <div>" . $url . "</div>
                                                    </div>";
                                                    echo (isset($icon)) ? '<img class="img-thumbnail" src="/img/icon/thumb/' . $icon . '.png" />' : '<img class="default img-thumbnail" />';
                                                   // echo ($icon) ? $this->Timthumb->image('/img/icon/' . $icon . '.png', array('width' => 80, 'height' => 80), array('class' => 'img-thumbnail')) : '<img class="default img-thumbnail" />';
                                                    echo "</div>";
                                                }
                                                break;
                                            case '3':
                                            case '4':
                                                echo "<div class='row m-b-md'>
                                                        <div class='col-sm-3 text-center'>" . $sub_type . "</div>
                                                        <div class='url col-sm-9'>" . $url . "</div>
                                                    </div>";
                                                break;
                                        }
                                    }
                                }
                                ?>
                            </div>
                            <?php echo $this->Form->input('Tag.bookmark_id', array('div' => false, 'type' => 'hidden')); ?>
                        </div>
                    </div>
                    <div class="col-sm-6" style="padding-left: 0px;">
                        <div class="clearfix">
                            <span><?php echo __('ラベル') ?>:</span>
                            <?php echo $this->Html->link(__('クリア'), '#', array('style' => 'display:none; margin-top: -10px;', 'id' => 'resetLabel', 'class' => 'imgBtn pull-right', 'div' => false, 'label' => FALSE)); ?>
                        </div>
                        <div class="content-detail-wrap p-sm">
                            <div class="row m-b-sm">
                                <div class="detail-label-list col-sm-6">
                                    <div class="dropdown _strip">
                                        <a id="List_label" role="button" data-toggle="dropdown" class="btn form-control"
                                           data-target="#">
                                            <?php echo __('ラベルを選択') ?>
                                        </a>
                                        <span class="caret"></span>
                                        <?php echo $this->Label->renderDropdownLabels($list_lb); ?>
                                    </div>
                                </div>
                                <?php echo $this->Form->input("Label.new_label", array('label' => false, 'type' => 'hidden')); ?>

                                <div class="detail-label-text col-sm-6" style="padding-left: 0;">
                                    <?php
                                    if ($this->request->prefix != 'system' && $session['authority'] == 3) {
                                        echo $this->Form->input('Label.add_new_text', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '64'));
                                    } else {
                                        echo $this->Form->input('Label.add_new_text', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'maxlength' => '64'));
                                    }
                                    ?>
                                </div>

                            </div>
                            <div class="detail-label-own">
                                <?php foreach ($cr_label as $lb_id) { ?>
                                    <div class="row">
                                        <div class="dropdown col-sm-6 m-b-sm _strip">
                                            <a id="Label_dropdowm_<?php echo $lb_id ?>" role="button"
                                               data-toggle="dropdown" class="btn form-control" data-target="#"
                                               href="/page.html">
                                                <?php echo __('ラベルを選択') ?>
                                            </a>
                                            <?php echo $this->Label->renderEditDropdownLabels($list_lb, $lb_id); ?>
                                            <?php echo $this->Form->checkbox('Label.clear_id.' . $lb_id, array('class' => '_check invisible', 'value' => $lb_id, 'hiddenField' => false, 'checked' => true)); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php echo $this->Form->input('Label.clear', array('div' => false, 'type' => 'hidden', 'value' => 0)); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-default"><?php echo __('OK') ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel') ?></button>
            </div>
        </div>
    </div>
</div><!-- /.modal-content -->

<script type="text/javascript">
    // Check IE Browser
    function msieversion() {

        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer, return version number
            return true

        return false;
    }
    $(document).ready(function () {
        var buildUrl = function (base, key, value) {
            var sep = (base.indexOf('?') > -1) ? '&' : '?';
            return base + sep + key + '=' + value;
        };
        $('#changeBookmark').click(function (e) {
            e.preventDefault();
            var $this = $(this),
                $remote = $this.attr('href'),
                id = $this.data('id');
            if ($('#ajaxSubModal').length) {
                $modal = '';
            } else {
                $modal = $('<div class="modal fade" id="ajaxSubModal" tabindex="-1" style="z-index:1051" role="dialog"></div>'),
                    now = new Date().getTime();
                if (msieversion())
                    $remote = buildUrl($remote, '_t', now);

                $modal.load($remote);
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
            $('body').append($modal);
            $('#ajaxSubModal').modal({
                show: true
            });

        })
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
        var checked = $('._check:checked');
        if (checked.length) {
            $('#resetLabel').show();
        } else {
            $('#resetLabel').hide();
        }

        $('#resetLabel').click(function (e) {
            $('.detail-label-own').hide();
            $('#LabelClear').val(1);
            $(this).hide();
        });

        //Upload image
        $('#TagImageUpload').on('change', function () {
            imageURL(this);
        });

        var canUpload = true;

        $('#TagEditForm').on('submit', function (e) {
            if (!canUpload) {
                e.preventDefault();
            }
        });

        function imageURL(input) {
            var url = input.value,
                ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase(),
                _validFileExtensions = ["jpg", "jpeg", "bmp", "gif", "png"];

            canUpload = true;

            if ($('#TagImageUploadError').length) {
                $('#TagImageUploadError').empty().remove();
            }

            if ($.inArray(ext, _validFileExtensions) == -1) {
                error = '<span id="TagImageUploadError" style="color: red;">* The selected file is not valid.</span>';

                $('.detail-image-content').append(error);

                canUpload = false;

                return;
            }


            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('.avatar')
                        .attr('src', e.target.result)
                        .width('200')
                        .height('100');
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

    });

</script>