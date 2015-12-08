<?php $last_login = $this->requestAction(array('controller' => 'managements', 'action' => 'getLastLoggin')); ?>
<div id="main" class="list p-b-lg">
    <!--Show current datetime-->
    <p class="paging" style="text-align: right;">
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
//            echo "{$year}.年 {$mon}月 {$day}日 {$hour}時 {$min}分 {$sec}秒 時点";
            //echo $year . __('年') . ' ' . $mon . __('月') . ' ' . $day . __('日') . ' ' . $hour . __('時') . ' ' . $min . __('分') . ' ' . $sec . __('秒') . ' ' . __('時点');
            echo $year . '-' . $mon . '-' . $day . ' ' . $hour . ':' . $min . ':' . $sec;
        }
        ?>
    </p>

    <!--Line chart-->
    <?php echo $this->element('Dashboard' . DS . 'accesslog_status'); ?>
    <!--Status chart-->
    <?php echo $this->element('Dashboard' . DS . 'accesslog'); ?> 
    <!--Contents and Plates chart-->
    <?php
    if (!$this->Session->check('Access.screen')) {
        echo $this->element('Dashboard' . DS . 'accesslog_content');
        echo $this->element('Dashboard' . DS . 'plate_status');
    }
    ?>

    <?php
    if ($this->request->prefix == 'system') {
        if ($this->Session->check('Auth.User')) {
            ?>
            <div class="text-center m-lg db-btn">
                <a class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;" href="<?php echo $this->Html->url(array('controller' => 'tags')) ?>"><?php echo __('プレート一覧へ') ?></a>               
                <a class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;" href="<?php echo $this->Html->url(array('controller' => 'accounts')) ?>"><?php echo __('クラウドアカウント一覧へ') ?></a>
                <a class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;" href="<?php echo $this->Html->url(array('controller' => 'account_users')) ?>"><?php echo __('アカウント一覧へ') ?></a>
            </div>
            <?php
        }
    } else {
        if ($this->Session->check('Auth.User')) {
            $session = $this->Session->read('Auth.User');
            if ($session['authority'] == 3) {
                ?>
                <div class="text-center m-lg db-btn">
                    <a href="<?php echo $this->Html->url(array('controller' => 'rank/contents')) ?>" class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;"><?php echo __('コンテンツ') . ' ' . __('ランキング') ?></a>                   
                    <a href="<?php echo $this->Html->url(array('controller' => 'rank/plate')) ?>" class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;"><?php echo __('プレート')  . ' ' . __('ランキング') ?></a>
                </div>
            <?php } elseif ($session['authority'] == 2) { ?>
                <div class="text-center m-lg db-btn">
                    <a href="<?php echo $this->Html->url(array('controller' => 'rank/contents')) ?>" class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;"><?php echo __('コンテンツ') . ' ' . __('ランキング') ?></a>                   
                    <a href="<?php echo $this->Html->url(array('controller' => 'rank/plate')) ?>" class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;"><?php echo __('プレート')  . ' ' . __('ランキング') ?></a>
                    <a class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;" href="<?php echo $this->Html->url(array('controller' => 'accounts')) ?>"><?php echo __('アカウント一覧へ') ?></a>
                </div>
            <?php } else { ?>
                <div class="text-center m-lg db-btn">                    
                    <a class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;" href="<?php echo $this->Html->url(array('controller' => 'plate')) ?>"><?php echo __('プレート一覧へ') ?></a>                   
                    <a class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;" href="<?php echo $this->Html->url(array('controller' => 'accounts')) ?>"><?php echo __('アカウント一覧へ') ?></a>
                    <a class="imgBtn wide m-l-sm m-r-sm" style="padding: 15px;" href="<?php echo $this->Html->url(array('controller' => 'invoices')) ?>"><?php echo __('請求管理へ') ?></a>                    
                </div>
                <?php
            }
        }
    }
    ?>

</div>

