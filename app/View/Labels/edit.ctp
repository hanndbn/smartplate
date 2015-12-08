<?php
/* Check action */
$action = $this->action;
?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('ラベル編集')?></h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list">         
                <div class="table-responsive">             
                    <?php
                    echo $this->Form->create('Label');
                    ?>
                    <table style="width: 100%;">
                        <tbody>   
                            <tr>
                                <th class="typeB">ID</th>
                                <td>
                                    <?php echo $label['Label']['id']/*$this->Form->input('Label.id', array('type' => 'text', 'label' => FALSE, 'readonly' => 'readonly'))*/; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="typeB"><?php echo __('ラベル名')?></th>
                                <td><?php echo $this->Form->input('Label.label', array('type' => 'text', 'label' => FALSE)); ?></td>
                            </tr>
                            
                            <tr>
                                <th class="typeB"><?php echo __('有効')?></th>
                                <td>
                                  <?php
                                    if ($action == 'edit') {
                                      echo $this->Form->input('Label.status', array('type' => 'checkbox', 'div' => FALSE, 'label' => FALSE)); 
                                    } else {
                                      echo $this->Form->input('Label.status', array('type' => 'checkbox', 'checked' => true, 'div' => FALSE, 'label' => FALSE)); 
                                    }
                                  ?>
                                  </td>
                            </tr>
                                                                        
                        </tbody>

                    </table>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-default">OK</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>           
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>