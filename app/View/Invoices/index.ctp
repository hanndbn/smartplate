<?php
$last_login = $this->requestAction(array('controller' => 'managements', 'action' => 'getLastLoggin'));
$prev_year = date('Y', strtotime('-1 year', strtotime($cr_year)));
$next_year = date('Y', strtotime('+1 year', strtotime($cr_year)));

if (isset($_GET['year'])) {
    $cr_year = $_GET['year'];
    $prev_year = $cr_year -1 ;
    $next_year = $cr_year + 1;   
}

?>
<div id="main" class="list">
    <h2><?php echo __('請求管理') ?></h2>
    <?php echo $this->Session->flash() ?>
    <p class="paging">
        <?php
        if (isset($last_login['last_login'])) {
            $dates = $last_login['last_login']['Management']['last_login_date'];
            $date = new DateTime($dates);
            $year = $date->format('Y');
            $mon = $date->format('m');
            $day = $date->format('d');
            $hour = $date->format('H');
            $min = $date->format('i');
            $sec = $date->format('s');
            //echo "{$year}年 {$mon}月 {$day}日 {$hour}時 {$min}分 {$sec}秒 時点";
            echo "{$year}-{$mon}-{$day} {$hour}:{$min}:{$sec}";
        }
        ?>
    </p>
    <!--Filter by Year-->
    <p class="paging">
        <a class="prev" href="<?php echo $this->Html->url(array('action' => 'index?year=' . $prev_year)) ?>">«</a>
        <span class="current numbers year"></span>
        <a class="next" href="<?php echo $this->Html->url(array('action' => 'index?year=' . $next_year)) ?>">»</a>
    </p>

    <div class="table-hover">
        <table id="accountUser-list">
            <thead>
                <tr>                    
                    <th class="typeB"><?php echo $this->Paginator->sort('id', 'No.'); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('regist_date', __('請求年月')); ?></th>
                    <th class="typeB"><?php echo $this->Paginator->sort('price', __('金額')); ?></th>
                    <th class="typeB highlight"><?php echo __('プロジェクト数'); ?></th>
                    <th class="typeB highlight"><?php echo __('稼働プレート'); ?></th>
                    <th class="typeB highlight"><?php echo __('稼働コンテンツ'); ?></th>                
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr class="text-center modal-detail" id="<?php echo 'toggle' . $invoice['Invoice']['id'] ?>" class="modal-detail" data-modal="<?php echo $this->webroot . 'invoices/detail/' . $invoice['Invoice']['id'] ?>">                     
                        <td><?php echo $invoice['Invoice']['id']; ?></td>
                        <td><?php echo date('Y/m', strtotime($invoice['Invoice']['regist_date'])) ?></td>         
                        <td><?php echo $invoice['Invoice']['price'] ?></td>                        
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

    <!--Filter by Year-->
    <p class="paging">
        <a class="prev" href="<?php echo $this->Html->url(array('action' => 'index?year=' . $prev_year)) ?>">«</a>
        <span class="current numbers year"></span>
        <a class="next" href="<?php echo $this->Html->url(array('action' => 'index?year=' . $next_year)) ?>">»</a>
    </p>
</div>
<?php echo $this->Html->link('Detail', array(), array('class' => 'invisible', 'id' => 'detailModal', 'data-toggle' => 'ajaxModal')); ?>
<script type="text/javascript">
    // Get GET filter parameter
        $.urlParam = function(name) {
            var results = new RegExp('[?]' + name + "=([^&#/]*)").exec(window.location.href);
            if (results === null) {
                return null;
            }
            else {
                return results[1] || 0;
            }
        };
        
        var year = new Date();
        var y = year.getFullYear();
        if ($.urlParam('year') !== null) {
            $('.year').html($.urlParam('year'));
        }else{
            $('.year').html(y);
        }
    $(document).ready(function() {
        $('th a').append(' <i class="fa fa-sort"></i>');
        $('th a.asc i').attr('class', 'fa fa-sort-down');
        $('th a.desc i').attr('class', 'fa fa-sort-up');



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
