<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __("Edit Image") ?></h4>
        </div>
        <div class="modal-body">
            <?php
            echo $this->Form->create('ChangeImages', array('action' => 'quickedit_img', 'class' => 'fixedDropdownWidth', 'style' => 'padding-top: 10px;border: 1px solid #ccc;', 'type' => 'file'));
            ?>

            <!--image title-->
            <div class="col-sm-12">
                <div class="form-group">
                    <div class="control-label col-sm-2">
                                    <span><?php echo __('画像')
                                        ?>:</span>
                    </div>
                    <div class="detail-header-image-content col-sm-10">
                        <?php
                        echo $this->Form->input('icon', array('type' => 'file', 'id' => "imgId", 'div' => false, 'label' => false, 'class' => 'm-b-sm'));
                        ?>
                        <div class="image_header_contenner">
                            <img src="" class="avatarHeader"/><img id="image_header_delete" src="/img/delete.png" height="20px" style="display: none"/>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $this->Form->input('strtable', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'value' => $strtable, 'id' => '$strtable')); ?>
            <?php echo $this->Form->input('selectall', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'value' => $selectall, 'id' => 'selectall')); ?>
            <?php echo $this->Form->input('target_id', array('div' => false, 'type' => 'hidden', 'label' => FALSE, 'value' => $target_id, 'id' => 'target_id')); ?>
            <!--image title END-->
            <div class="modal-footer" style="border: none;">
                <button id="submitBTN" type="submit" class="btn btn-default"><?php echo __('OK') ?></button>
                <button id="cancelBtn" type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel') ?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
<script type="text/javascript">
    // Return an unique array
    function unique(list) {
        var result = [];
        $.each(list, function (i, e) {
            if ($.inArray(e, result) == -1) result.push(e);
        });
        return result;
    }
    $(document).ready(function () {
        $('#submitBTN').click(function (e) {
            e.preventDefault();
            var imgval = $('#imgId').val();
            if (imgval != '') {
                $('form').submit();
            }
            $("#cancelBtn").trigger('click');
        });

        $('#imgId').on('change', function () {
            imageHeaderURL(this);
        });

        function imageHeaderURL(input) {
            var url = input.value,
                ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase(),
                _validFileExtensions = ["jpg", "jpeg", "bmp", "gif", "png"];

            canUpload = true;

            if ($.inArray(ext, _validFileExtensions) == -1) {
                error = '<span id="imgError" style="color: red;">* The selected file is not valid.</span>';

                $('.detail-header-image-content').append(error);

                canUpload = false;

                return;
            }
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('.avatarHeader')
                        .attr('src', e.target.result)
                        .width('200')
                        .height('200');
                    $('#image_header_delete').attr('style', "display:inline-block");
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        $('#image_header_delete').click(function (e) {
            $('.avatarHeader').attr('src', "").attr('style', "");
            $('#imgId').val("");
            $('#image_header_delete').attr('style', "display:none");
        });
    });

</script>