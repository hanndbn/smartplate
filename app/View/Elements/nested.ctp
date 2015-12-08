<div class="dd nestable">
    <li class="dd-item dd3-item base hide" id="newLabel" data-id="0">
        <?php echo $this->Form->create('Label', array('controller' => 'labels', 'action' => 'add')); ?>
        <div class="dd-handle dd3-handle"></div>
        <div class="dd3-content">
            <?php echo $this->Form->input('Label.type', array('type' => 'hidden', 'value' => $type, 'label' => FALSE)) ?>
            <?php echo $this->Form->input('Label.label', array('type' => 'text', 'maxlength' => 64, 'label' => FALSE, 'data-toggle' => 'checklengh')) ?>
        </div>
        <?php echo $this->Form->end(); ?>
    </li>
    <?php echo $this->Label->renderNestedLabels($labels, $count) ?>                
</div>