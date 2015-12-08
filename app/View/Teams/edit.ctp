<?php
/* Check action */
$action = $this->action;
$session = $this->Session->read('Auth.User');
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php
                if ($action == 'edit') {
                    echo __('プロジェクト編集');
                } else {
                    echo __('プロジェクト登録');
                }
                ?>
            </h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list modal-edit">
                <?php
                echo $this->Form->create('Team', array('type' => 'file', 'class' => 'form-horizontal'));
                ?>
                <div class="row">
                    <div class="l-block col-sm-6">
                        <div class="form-group">
                            <?php if ($action == 'edit') { ?>
                                <div class="control-label col-sm-4">
                                    <span><?php echo __('ID') ?>:</span>
                                </div>
                                <div class="detail-id-content col-sm-8">
                                    <div class="checkbox"><?php echo $team['Team']['id']; ?></div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('プロジェクト名') ?>:</span>
                            </div>
                            <div class="detail-name-content col-sm-8">
                                <?php
                                if ($action == 'edit') {
                                    echo $this->Form->input('Team.new_name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'data-toggle' => 'checklengh', 'value' => $team['Team']['name']));
                                    echo $this->Form->input('Team.name', array('div' => false, 'type' => 'hidden'));
                                } else {
                                    echo $this->Form->input('Team.name', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '128', 'data-toggle' => 'checklengh'));
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('コメント') ?>:</span>
                            </div>
                            <div class="detail-code-content col-sm-8">
                                <?php echo $this->Form->input('Team.comment', array('div' => false, 'type' => 'textarea', 'cols' => false, 'label' => FALSE, 'maxlength' => '512', 'data-toggle' => 'checklengh')); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('プラン') ?>:</span>
                            </div>
                            <div class="detail-code-content col-sm-8">
                                <?php echo $this->Form->input('Team.plan', array('div' => false, 'options' => $listPlan, 'label' => FALSE)); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('開始日') ?>:</span>
                            </div>
                            <div class="detail-code-content col-sm-8">
                                <?php echo $this->Form->input('Team.start_date', array('div' => false, 'type' => 'text', 'label' => FALSE, 'id' => 'start_date', 'value' => h(date('Y/m/d', strtotime($team['Team']['start_date']))))); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="control-label col-sm-4">
                                <span><?php echo __('Splash') ?>:</span>
                            </div>
                            <div class="detail-image-content col-sm-8">
                                <?php
                                if ($action == 'edit') {
                                    echo ($team['Team']['splash']) ? $this->Timthumb->image('/upload/splash/' . $team['Team']['splash'], array('width' => 200, 'height' => 100), array('class' => 'avatar')) : '<img class="avatar"/>';
                                    echo $this->Form->input('Team.splash', array('type' => 'file', 'id' => "TeamSplashUpload", 'div' => false, 'label' => false, 'class' => 'm-b-sm'));
                                } else {
                                    echo "<img class='avatar'/>";
                                    echo $this->Form->input('Team.splash', array('type' => 'file', 'id' => "TeamSplashUpload", 'div' => false, 'label' => false));
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="detail-visible-content col-sm-8 col-sm-offset-4">
                                <div class="hasDatepicker">
                                    <?php
                                    if ($action == 'edit') {
                                        echo $this->Form->input('Team.valid', array('div' => false, 'class' => 'm-n', 'type' => 'checkbox', 'label' => FALSE));
                                    } else {
                                        echo $this->Form->input('Team.valid', array('div' => false, 'class' => 'm-n', 'type' => 'checkbox', 'checked' => true, 'label' => FALSE));
                                    }
                                    ?>
                                    <span class="m-l-md"><?php echo __('有効') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="right-block col-sm-6" style="padding-left: 0px; display:none;">
                        <div class="control-label-title">
                            <span><?php echo __('ラベル') ?></span>
                        </div>
                        <div class="control-label-content content-detail-wrap p-sm">
                            <div class="row">
                                <div class="control-label-list col-sm-6">
                                    <div class="dropdown m-b-sm _strip">
                                        <a id="List_label" role="button" data-toggle="dropdown" class="btn form-control"
                                           data-target="#">
                                            <?php echo __('ラベルを選択') ?>
                                        </a>
                                        <span class="caret"></span>
                                        <?php echo $this->Label->renderDropdownLabels($list_lb); ?>
                                    </div>
                                </div>
                                <?php echo $this->Form->input("Label.new_label", array('label' => false, 'type' => 'hidden')); ?>
                                <?php // --------Add new Label----------- ?>
                                <div class="control-label-text col-sm-6" style="padding-left: 0;">
                                    <?php
                                    if ($session['authority'] == 3) {
                                        echo $this->Form->input('Label.add_new_text', array('div' => false, 'type' => 'text', 'label' => FALSE, 'maxlength' => '64'));
                                    } else {
                                        echo $this->Form->input('Label.add_new_text', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'maxlength' => '64'));
                                    }
                                    ?>
                                </div>
                                <?php // --------end----------- ?>
                            </div>
                            <?php if ($action == 'edit') { ?>
                                <div class="control-label-own col-sm-5">
                                    <?php foreach ($cr_label as $lb_id) { ?>
                                        <div class="row">
                                            <div class="dropdown m-b-sm _strip">
                                                <a id="Label_dropdowm_<?php echo $lb_id ?>" role="button"
                                                   data-toggle="dropdown" class="btn form-control" data-target="#"
                                                   href="#">
                                                    <?php echo __('ラベルを選択') ?>
                                                </a>
                                                <span class="caret"></span>
                                                <?php echo $this->Label->renderEditDropdownLabels($list_lb, $lb_id); ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer m-t-md">
                    <button id="submit_button" type="submit" class="btn btn-default"><?php echo __('OK') ?></button>
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo __('Cancel') ?></button>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.modal-content -->

<script type="text/javascript">
    $(document).ready(function () {
        $("#start_date").datepicker({
            defaultDate: "+1w",
            inline: true,
            numberOfMonths: 1,
            dateFormat: 'yy/mm/dd',
            maxDate: 0
        });
        <?php if ($action == 'edit') { ?>
        $('.label_edit').each(function () {
            var $this = $(this),
                value = $this.attr('data-value');

            if (value) {
                $('#Label_dropdowm_' + value).html($(".label_edit[data-id=" + value + "]").html());
            }
        });
        <?php } ?>
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

        // Upload Splash
        $('#TeamSplashUpload').on('change', function () {
            imageURL(this);
        });

        var canUpload = true;

        $('#TeamAddForm').on('submit', function (e) {
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
