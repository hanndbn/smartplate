<?php
$base_url = array('controller' => 'invoices', 'action' => 'index');
?>
<div id="main" class="list">
    <h2><?php echo __('請求管理') ?></h2>
    <?php echo $this->Session->flash() ?>
    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>

    <div class="table-hover">
        <table id="accountUser-list">
            <thead>
                <tr>
                    <td class="search" colspan="12">
                        <?php
                        // The base url is the url where we'll pass the filter parameters
                        echo $this->Form->create("Filter", array('url' => $base_url, 'role' => "form"));
                        // add a select input for each filter. It's a good idea to add a empty value and set
                        // the default option to that.
                        ?>
                        <?php echo __('絞り込み条件'); ?>:
                        <?php echo $this->element('country', array('country' => '', 'name' => 'data[Filter][country]')) ?>
                        <span class="plus">＋</span>
                        <select id="FilterYear" class="" name="data[Filter][year]">
                            <option value=""><?php echo __('年を選択') ?></option>
                            <?php for ($i = 2010; $i <= 2020; $i++) { ?>
                                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                            <?php } ?>
                        </select>
                        <span class="plus">＋</span>
                        <select id="FilterMonth" class="" name="data[Filter][month]">
                            <option value=""><?php echo __('月を選択') ?></option>
                            <?php for ($i = 1; $i <= 12; $i++) { ?>
                                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                            <?php } ?>
                        </select>
                        <span class="plus">＋</span>
                        <select id="FilterStatus" class="" name="data[Filter][status]">
                            <option value=""><?php echo __('状態を選択') ?></option>                           
                            <option value="0"><?php echo __('請求済') ?></option>
                            <option value="1"><?php echo __('入金済') ?></option>
                        </select>
                        <br><span class="plus">＋</span>
                        <?php
                        // Add a basic search 
                        echo $this->Form->input("name", array('label' => false, 'placeholder' => "検索キー", 'autocomplete' => 'off'));
                        ?>
                        <span class="m-sm">
                            <a href="javascript:;" class="imgBtn wide formSubmit m-r-xs"><?php echo __('検索') ?></a>
                            <?php echo $this->Html->link('リセット', array('action' => 'index'), array('class' => 'imgBtn wide subFilter')) ?>
                        </span>
                        <?php
                        echo $this->Form->end();
                        ?>
                    </td>
                </tr>
                <tr>                    
                    <th class="typeB"><?php echo $this->Paginator->sort('id', 'No.'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('regist_date', '請求年月'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('AccountUser.country', '国'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('AccountUser.company', '社名'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('AccountUser.family_name', '担当者'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('price', __('金額')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('status', __('状態')); ?></th>
                    <th class="typeB highlight"><?php echo __('プロジェクト数'); ?></th>
                    <th class="typeB highlight"><?php echo __('稼働プレート'); ?></th>
                    <th class="typeB highlight"><?php echo __('稼働コンテンツ'); ?></th>                
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr class="text-center modal-detail" id="<?php echo 'toggle' . $invoice['Invoice']['id'] ?>" class="modal-detail" data-modal="<?php echo $this->webroot . 'system/invoices/detail/' . $invoice['Invoice']['id'] ?>">                     
                        <td><?php echo $invoice['Invoice']['id']; ?></td>
                        <td><?php echo date('Y/m', strtotime($invoice['Invoice']['regist_date'])) ?></td>   
                        <td><?php echo $invoice['AccountUser']['country'] ?></td>        
                        <td><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($invoice['AccountUser']['company']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($invoice['AccountUser']['company']), 30) . '</span>' ?></td>        
                        <td><?php echo '<span data-toggle="tooltip" data-placement="bottom" title="' . Utility_Str::escapehtml($invoice['AccountUser']['family_name']) . '">' . Utility_Str::wordTrim(Utility_Str::escapehtml($invoice['AccountUser']['family_name']), 20) . '</span>' ?></td>        
                        <td><?php echo $invoice['Invoice']['price'] ?></td>  
                        <td class="ignore-modal" data-id="<?php echo $invoice['Invoice']['id'] ?>" data-status="<?php echo $invoice['Invoice']['status'] ?>">
                            <?php if($invoice['Invoice']['status'] == 1){
                                echo "<span class='status'>".__('未請求')."</span>";
                            }elseif($invoice['Invoice']['status'] == 2){
                                echo "<span class='status'>".__('請求済')."</span>";
                            }else{
                                echo __('完了');
                            }?>
                        </td>
                        <td>
                            <?php
                            if (isset($invoice['Invoice']['team_id']))
                                echo implode(', ', $invoice['Invoice']['team_id']);
                            ?>
                        </td>
                        <td>
                            <?php
                            if (isset($invoice['Invoice']['tag']))
                                echo implode(', ', $invoice['Invoice']['tag']);
                            ?>
                        </td>
                        <td>
                            <?php
                            if (isset($invoice['Invoice']['contents'])) {
                                $contents = array_filter($invoice['Invoice']['contents']);
                                if (!empty($contents))
                                    echo implode(', ', $contents);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php unset($invoices); ?>
            </tbody>
        </table>
    </div>

    <!--Paginator-->
    <?php echo $this->element('pagination'); ?>
</div>
<?php echo $this->Html->link('Detail', array(), array('class' => 'invisible', 'id' => 'detailModal', 'data-toggle' => 'ajaxModal')); ?>
<script type="text/javascript">
    // Get GET filter parameter
    $.urlParam = function(name) {
        var results = new RegExp('[/]' + name + ":([^&#/]*)").exec(window.location.href);
        if (results === null) {
            return null;
        }
        else {
            return results[1] || 0;
        }
    };
    if ($.urlParam('country') !== null) {
        $('#country').val($.urlParam('country').toString().replace(/\%20/g, ' '));
    }
    if ($.urlParam('year') !== null) {
        $('#FilterYear').val($.urlParam('year'));
    }
    if ($.urlParam('month') !== null) {
        $('#FilterMonth').val($.urlParam('month'));
    }
    if ($.urlParam('status') !== null) {
        $('#FilterStatus').val($.urlParam('status'));
    }
    var buildUrl = function(base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    };
    $(document).ready(function() {
        $('th a').append(' <i class="fa fa-sort"></i>');
        $('th a.asc i').attr('class', 'fa fa-sort-down');
        $('th a.desc i').attr('class', 'fa fa-sort-up');

        //ajax change status
        $('.status').click(function() {
            var id = $(this).closest('td').data('id'),
                    status = $(this).closest('td').data('status');
            var now = new Date().getTime();
            var url = buildUrl('<?php echo $this->Html->url(array('controller' => 'invoices', 'action' => 'changeStatus'), true); ?>', '_t', now);
            $.confirm({
                text: '<?php echo ($invoice['Invoice']['status'] == 1) ? __('ステータスを請求済に変更します。') : __('ステータスを完了に変更します。') ?>',
                title: '<?php echo __("確認") ?>',
                confirm: function(confirmButton) {
                    var data = {id: id, status: status};
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: url,
                        data: data,
                        success: function(rs) {
                            location.reload();
                        }
                    });
                },
                cancelButton: "Cancel",
                confirmButton: "OK",
                confirmButtonClass: 'btn-default',
                post: true
            });

        });

        $('.modal-detail').find('td').on('click', function() {
            var $this = $(this),
                    $detail = $('#detailModal'),
                    href = $this.closest('.modal-detail').data('modal');

            if ($this.hasClass('ignore-modal'))
            {
                return;
            }

            if (href)
            {
                $detail.attr('href', href).trigger('click');
            }
        });

    });
</script>
