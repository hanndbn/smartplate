<?php $id = $user['id']; ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title"><?php echo __('アカウント詳細')?></h4>
        </div>
        <div class="modal-body">
            <div id="main" class="list">
                <table class="table table-striped">
                    <tbody>   
                        <tr>
                            <th class="typeB text-left w-md"><?php echo __('氏')?></th>
                            <td><?php echo Utility_Str::escapehtml($user['family_name']) ?></td>
                        </tr>
                        <tr>
                            <th class="typeB text-left w-md"><?php echo __('名')?></th>
                            <td><?php echo Utility_Str::escapehtml($user['given_name']); ?></td>
                        </tr>
                        <tr>
                            <th class="typeB text-left w-md"><?php echo __('会社名')?></th>
                            <td><?php echo Utility_Str::escapehtml($user['company']); ?></td>
                        </tr>
                        <tr>
                            <th class="typeB text-left w-md"><?php echo __('国')?></th>
                            <td><?php echo Utility_Str::escapehtml($user['country']); ?></td>
                        </tr>
                        <tr>
                            <th class="typeB text-left w-md"><?php echo __('地域')?></th>
                            <td><?php echo Utility_Str::escapehtml($user['region']); ?></td>
                        </tr>
                        <tr>
                            <th class="typeB text-left w-md"><?php echo __('有効')?></th>
                            <td><?php echo $user['status'] ? '<i class="fa fa-circle-thin"></i>' : '<i class="fa fa-times"></i>'; ?></td>
                        </tr>  
                        <tr>
                            <th class="typeB text-left w-md"><?php echo __('登録更新日')?></th>
                            <td><?php echo ($user['update_date']) ? date('Y/m/d H:i:s', strtotime($user['update_date'])) : "" ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

