<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$cakeDescription = __d('cake_dev', 'SmartPlate');
$role = null;
if ($this->request->prefix != 'system') {
    if (AuthComponent::user()) {
        $user_name = AuthComponent::user('name');
        switch (intval(AuthComponent::user('authority'))) {
            case 1:
                $role = 'admin : '.$user_name;
                break;

            case 2:
                $role = 'manager : '.$user_name;
                break;

            case 3:
                $role = 'editor : '.$user_name;
                break;
        }
    }
} else {
    $role = 'system';
}
$name = $role ? 'for ' . $role : '';
?>
<!-- Header-->
<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>
            <?php echo $cakeDescription ?>
            <?php //echo $title_for_layout; ?>
        </title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <?php
        $ua = $_SERVER["HTTP_USER_AGENT"];
        echo $this->Html->meta('icon');
        echo $this->Html->css(array('bootstrap.min', 'non-responsive', 'datepicker', 'style', 'custom'));
        if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0; rv:11.0') !== false)) {
            echo $this->Html->css('ie');
        }
        if (strpos($ua, 'Safari'))
            echo $this->Html->css('safari');
        echo $this->Html->script(array('jquery.min', 'jquery-ui.min', 'bootstrap.min', 'jquery.confirm.min', 'zingchart.min', 'main'));

        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->webroot . 'css/font-awesome-4.2.0/css/font-awesome.min.css' ?>"/>

    </head>
    <body class="admin">
        <div id="container">
            <div class="page-border pull-left"></div>
            <div class="page-border pull-right"></div>
            <div id="header" class="clearfix">
                <h1><img src="<?php echo $this->webroot; ?>img/logo-smartplate-cloud-header.png" alt="SmartPlate Cloud Manager" style=""></h1>

                <p class="w-user">
                    【 SmartPlate Cloud Manager 】<?php echo $name ?><br>
                </p>
                <p class="language">
                    <?php
                    echo $this->Html->link('', array('language' => 'jpn'), array('class' => 'jpn m-r-sm p-l-ml', 'title' => 'Japan'));
                    echo $this->Html->link('', array('language' => 'eng'), array('class' => 'eng m-r-sm p-l-ml', 'title' => 'English'));
                    //echo $this->Html->link('', array('language' => 'cns'), array('class' => 'cns m-r-sm p-l-ml', 'title' => 'Simplified Chinese'));
                    //echo $this->Html->link('', array('language' => 'cnt'), array('class' => 'cnt m-r-sm p-l-ml', 'title' => 'Traditional Chinese'));
                    ?>
                </p>
                <ul>
                    <a class="policy" href="/policy"><?php echo __('ご利用規約とプライバシーポリシー') ?></a>　
                    <?php if ($this->Session->check('Auth.User')) { ?>
                        <a class="logout" href="<?php echo ($this->request->prefix == 'system') ? $this->Html->url(array('prefix' => 'system', 'controller' => 'managements', 'action' => 'logout')) : $this->Html->url(array('controller' => 'managements', 'action' => 'logout')) ?>" title="<?php echo __('ログアウト') ?>"><?php echo __('ログアウト') ?></a>
                        <?php
                    } else {
                        echo $this->Html->link('Login', array('controller' => 'managements', 'action' => 'login'), array('id' => 'login', 'data-toggle' => 'ajaxModal', 'title' => __('User Login')));
                    }
                    ?>
                </ul>
            </div>

            <div id="main_container">
                <?php if ($this->Session->check('Auth.User')) { ?>
                    <div class="col-sm-2 p-xs">
                        <?php echo $this->element('menu'); ?>
                    </div>
                    <div class="col-sm-10">
                        <?php echo $this->fetch('content'); ?>
                    </div>
                    <?php } else {
                    ?>
                    <div class="container-fluid">
                        <?php echo $this->fetch('content'); ?>
                    </div>
                <?php }
                ?>

            </div>
            <!-- Footer-->
            <div id="footer" class="clearfix">
                <address><?php echo __('Copyright © 2015 Aquabit Spirals Inc.') ?></address>
                <div class="aquabit">Powered by <a href="http://spirals.co.jp/smartplate">Plate.ID™</a> Engine</div>
            </div>
            <!-- End Footer-->
            <?php if (class_exists('JsHelper') && method_exists($this->Js, 'writeBuffer')) echo $this->Js->writeBuffer(); ?>
    </body>
</html>