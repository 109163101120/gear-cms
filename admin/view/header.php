<header id="head">

    <div class="container clear">
        <div class="inner">

            <div id="messages"></div>

            <a href="http://gearcms.org" class="logo" target="_blank">
                <img src="<?=url::assets('img/logoPrimary.svg'); ?>" alt="Gear Logo">
            </a>

            <?php
                $menu = admin::getMenu();
                if($menu):
            ?>
            <nav class="nav">
                <ul>
                <?php
                    foreach($menu as $url => $array):

                        $class = ($array['class']) ? ' class="'.$array['class'].'"' : '';

                        echo '
                        <li'.$class.'>
                            <a href="'.url::admin($url).'">'.$array['name'].'</a>
                        ';

                        $sub = admin::getSubmenu($url);
                        if($sub):
                            echo '<ul>';

                            foreach($sub as $url => $array):

                                $class = ($array['class']) ? ' class="'.$array['class'].'"' : '';

                                echo '
                                <li'.$class.'>
                                    <a href="'.url::admin($url).'">'.$array['name'].'</a>
                                </li>
                                ';

                            endforeach;

                            echo '</ul>';
                        endif;

                        echo '
                        </li>
                        ';

                    endforeach;
                ?>
                </ul>
            </nav>
            <?php
                endif;
            ?>

            <div class="expand">
                <i class="icon icon-navicon-round"></i>
            </div>

            <div class="user clear">
                <nav>
                    <ul>
                        <li>
                            <a href="?logout=1">
                                <i class="icon icon-log-out"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <a href="<?=url::admin('user', ['index', 'edit']); ?>" class="profile">
                    <?=user::getAvatar(36, true); ?>
                    <span><?=user::current()->username; ?></span>
                </a>
            </div>
        </div>
    </div>
</header>

<?php
    $submenu = admin::getSubmenu(admin::$url);
    if($submenu):
?>
<div id="subHead">
    <div class="container clear">
        <nav>
            <ul>
            <?php
                foreach($submenu as $url => $array):

                    $class = ($array['class']) ? ' class="'.$array['class'].'"' : '';

                    echo '
                    <li'.$class.'>
                        <a href="'.url::admin($url).'">'.$array['name'].'</a>
                    </li>
                    ';

                endforeach;
            ?>
            </ul>
        </nav>
    </div>
</div>
<?php
    endif;
?>

<div class="container">
    <main>

        <?php echo config::get('system'); ?>
