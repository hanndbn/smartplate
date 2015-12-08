<?php ?>

<div id="main" class="list">
    <h2><?php echo __('言語選択') ?></h2>
    <?php echo $this->Session->flash() ?>
    <div class="m-t-md">
        <?php echo $this->Form->create('Language', array('url' => array('controller' => 'languages', 'action' => 'index'))) ?>
        <table id="languages" class="text-center">
            <thead>
                <tr>
                    <th class="typeB highlight"><?php echo __('言語') ?></th>
                    <th class="typeB highlight"><?php echo __('適用') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo __('日本語') ?>
                    </td>
                    <td><?php echo $this->Form->submit(__('申請'), array('class' => 'imgBtn wide m-sm', 'id' => 'jpnBtn', 'div' => false)) ?></td>
                </tr>
                <tr>
                    <td>
                        <?php echo __('英語') ?>
                    </td>
                    <td><?php echo $this->Form->submit(__('申請'), array('class' => 'imgBtn wide m-sm', 'id' => 'engBtn', 'div' => false)) ?></td>
                </tr>
                <tr>
                    <td>
                        <?php echo __('中国語-簡体字') ?>                   
                    </td>
                    <td><?php echo $this->Form->submit(__('申請'), array('class' => 'imgBtn wide m-sm', 'id' => 'cnsBtn', 'div' => false)) ?></td>
                </tr>
                <tr>
                    <td>
                        <?php echo __('中国語-繁体字') ?>
                    </td>
                    <td><?php echo $this->Form->submit(__('申請'), array('class' => 'imgBtn wide m-sm', 'id' => 'cntBtn', 'div' => false)) ?></td>
                </tr>              
                <?php echo $this->Form->input("langBtn", array('type' => 'hidden')); ?>
            </tbody>
        </table>
        <?php echo $this->Form->end() ?>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.imgBtn').click(function() {
            var btnId = $(this).attr('id');
            switch (btnId) {
                case 'engBtn':
                    $('#LanguageLangBtn').val('eng');
                    break;
                case 'cnsBtn':
                    $('#LanguageLangBtn').val('cns');
                    break;
                case 'cntBtn':
                    $('#LanguageLangBtn').val('cnt');
                    break;
                case 'jpnBtn':
                    $('#LanguageLangBtn').val('jpn');
                    break;
                default:
                    $('#LanguageLangBtn').val('');
                    break;
            }
        });
    });
</script>