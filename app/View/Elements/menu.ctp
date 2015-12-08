<?php
$session = $this->Session->read('Auth.User');
$authority = $session['authority'];
if ($this->request->prefix != 'system' && $authority == 1)
    $accountUserID = $this->requestAction(array('controller' => 'account_users', 'action' => 'getAccountUser'));
?>
<div id="nav">
    <?php if ($this->request->prefix == 'system') { ?>
        <ul class="parent">
            <li class="parent shadow">
                <h2><?php echo __('ダッシュボード'); ?></h2>
                <ul>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'pages', 'action' => 'index')) ?>"><?php echo __('ダッシュボード') ?></a>
                    </li>
                </ul>
            </li>
            <li class="parent shadow">
                <h2><?php echo __('コンテンツ管理'); ?></h2>
                <ul>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'bookmarks', 'action' => 'index')) ?>"><?php echo __('コンテンツ一覧') ?></a>
                    </li>
                </ul>
            </li>
            <li class="parent shadow">
                <h2><?php echo __('プレート管理'); ?></h2>
                <ul>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'tags', 'action' => 'index')) ?>"><?php echo __('プレート一覧') ?></a>
                    </li>
                </ul>
            </li>
            <li class="parent shadow">
                <h2><?php echo __('クラウド管理'); ?></h2>
                <ul>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'accounts', 'action' => 'index')) ?>"><?php echo __('アカウント一覧') ?></a>
                    </li>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'managements', 'action' => 'changePassword')) ?>"><?php echo __('パスワード変更') ?></a>
                    </li>
                </ul>
            </li>
            <li class="parent shadow">
                <h2><?php echo __('プレート発行'); ?></h2>
                <ul>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'orders', 'action' => 'index')) ?>"><?php echo __('発注一覧') ?></a>
                    </li>
                </ul>
            </li>
            <li class="parent shadow">
                <h2><?php echo __('お取り引き状況の管理'); ?></h2>
                <ul>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'invoices', 'action' => 'index')) ?>"><?php echo __('請求管理') ?></a>
                    </li>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'account_users')) ?>"><?php echo __('アカウント一覧') ?></a>
                    </li>
                </ul>
            </li>
            <li class="parent shadow">
                <h2><?php echo __('Languages'); ?></h2>
                <ul>
                    <li>
                        <a href="<?php echo $this->Html->url(array('controller' => 'languages', 'action' => 'index')) ?>"><?php echo __('Apply Language') ?></a>
                    </li>
                </ul>
            </li>         
        </ul>
    <?php } else { ?>
        <?php if ($authority == 3) { ?> <!-- Editor -->
            <ul class="parent">
                <li class="parent shadow">
                    <h2><?php echo __('ダッシュボード'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'pages', 'action' => 'index')) ?>"><?php echo __('ダッシュボード') ?></a>
                        </li>
                    </ul>
                </li>               
                <li class="parent shadow">
                    <h2><?php echo __('コンテンツ管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'bookmarks', 'action' => 'index')) ?>"><?php echo __('コンテンツ一覧') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('プレート管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'tags', 'action' => 'index')) ?>"><?php echo __('プレート一覧') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('アプリ管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'apps', 'action' => 'index')) ?>"><?php echo __('アカウント一覧') ?></a>
                        </li>
                    </ul>
                </li>                
                <li class="parent shadow">
                    <h2><?php echo __('クラウド管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'managements', 'action' => 'changePassword')) ?>"><?php echo __('パスワード変更') ?></a>
                        </li>
                    </ul>
                </li>
            </ul>
        <?php } elseif ($authority == 2) { ?> <!-- Manager -->
            <ul class="parent">
                <li class="parent shadow">
                    <h2><?php echo __('ダッシュボード'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'pages', 'action' => 'index')) ?>"><?php echo __('ダッシュボード') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('プロジェクト管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'teams', 'action' => 'index')) ?>"><?php echo __('プロジェクト一覧') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('コンテンツ管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'bookmarks', 'action' => 'index')) ?>"><?php echo __('コンテンツ一覧') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('プレート管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'tags', 'action' => 'index')) ?>"><?php echo __('プレート一覧') ?></a>
                        </li>
                    </ul>
                </li> 
                <li class="parent shadow">
                    <h2><?php echo __('プレート発行'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'orders', 'action' => 'index')) ?>"><?php echo __('発注一覧') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('クラウド管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'accounts', 'action' => 'index')) ?>"><?php echo __('アカウント一覧') ?></a>
                        </li>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'managements', 'action' => 'changePassword')) ?>"><?php echo __('パスワード変更') ?></a>
                        </li>
                    </ul>
                </li>            
            </ul>            
        <?php } else { ?> <!-- Admin -->
            <ul class="parent">
                <li class="parent shadow">
                    <h2><?php echo __('ダッシュボード'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'pages', 'action' => 'index')) ?>"><?php echo __('ダッシュボード') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('コンテンツ管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'bookmarks', 'action' => 'index')) ?>"><?php echo __('コンテンツ一覧') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('プレート管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'tags', 'action' => 'index')) ?>"><?php echo __('プレート一覧') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('プレート発行'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'orders', 'action' => 'index')) ?>"><?php echo __('発注一覧') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('クラウド管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'accounts', 'action' => 'index')) ?>"><?php echo __('アカウント一覧') ?></a>
                        </li>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'managements', 'action' => 'changePassword')) ?>"><?php echo __('パスワード変更') ?></a>
                        </li>
                    </ul>
                </li>
                <li class="parent shadow">
                    <h2><?php echo __('お取り引き状況の管理'); ?></h2>
                    <ul>
                        <li>
                            <a href="<?php echo $this->Html->url(array('controller' => 'invoices', 'action' => 'index')) ?>"><?php echo __('請求管理') ?></a>
                        </li>
                        <li>
                            <?php if ($accountUserID == NULL) { ?>
                                <a href="<?php echo $this->Html->url(array('controller' => 'account_users', 'action' => 'add')) ?>"><?php echo __('登録情報') ?></a>
                            <?php } else { ?>
                                <a href="<?php echo $this->Html->url(array('controller' => 'account_users', 'action' => 'edit', $accountUserID)) ?>"><?php echo __('登録情報') ?></a>
                            <?php } ?>
                        </li>
                    </ul>
                </li>      
            </ul>   
            <?php
        }
    }
    ?>
</div>
