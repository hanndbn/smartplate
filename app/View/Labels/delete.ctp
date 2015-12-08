<div class="modal-dialog">
    <div class="modal-content">
    <?php echo $this->Form->create('Label', array('controller' => 'labels', 'action' => 'delete/'. $id)); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo __('確認')?></h4>
        </div>
        <div class="modal-body">
            <?php echo __('選択した項目を削除してもよろしいですか？')?>
            <?php echo $this->Form->input('Label.type', array('type' => 'hidden', 'value' => $type, 'label' => FALSE)) ?>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-default">Yes</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>            
        </div>
    <?php echo $this->Form->end(); ?>
    </div>
</div>