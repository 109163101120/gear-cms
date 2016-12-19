<section id="install">

    <div class="form">

        <a href="http://gearcms.org" class="logo" target="_blank">
            <img src="<?=url::assets('img/logo.svg'); ?>" alt="Gear CMS Logo">
        </a>

        <div class="content box">

            <nav>
                <ul class="unstyled">
                    <?php
                        foreach($steps as $name) {
                            $active = ($name == $step) ? 'class="active"' : '';
                            echo '<li '.$active.'>'.lang::get($name).'</li>';
                        }
                    ?>
                </ul>
            </nav>

            <?=config::get('system'); ?>

            <div id="messages"></div>

            <?php
                if($step == 'database') {

                    $form = new form();

                    $field = $form->addTextField('host', 'localhost');
                    $field->fieldName(lang::get('host'));
                    $field->fieldValidate();

                    $field = $form->addTextField('user', '');
                    $field->fieldName(lang::get('user'));
                    $field->fieldValidate();

                    $field = $form->addPasswordField('password', '');
                    $field->fieldName(lang::get('password'));
                    $field->fieldValidate();

                    $field = $form->addTextField('database', '');
                    $field->fieldName(lang::get('database'));
                    $field->fieldValidate();

                    $field = $form->addTextField('prefix', '');
                    $field->fieldName(lang::get('prefix'));

                    if($form->isSubmit()) {

                        if($form->validation()) {

                            $array = $form->getAll();

                            $prefix = filter::prefix($array['prefix']);

                            if(sql::connect($array['host'], $array['user'], $array['password'], $array['database'], $prefix)) {

                                sql::run(true)->query("
                                    CREATE TABLE IF NOT EXISTS `".$prefix."entry` (
                                        `id` int(20) NOT NULL AUTO_INCREMENT,
                                        `type` varchar(255) NOT NULL,
                                        `name` varchar(255) NOT NULL,
                                    PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;
                                    CREATE TABLE IF NOT EXISTS `".$prefix."entry_meta` (
                                        `meta_id` int(20) NOT NULL AUTO_INCREMENT,
                                        `entry_id` int(20) NOT NULL,
                                        `meta_key` varchar(255) NOT NULL,
                                        `meta_value` longtext NOT NULL,
                                    PRIMARY KEY (`meta_id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=latin1;
                                    CREATE TABLE IF NOT EXISTS `".$prefix."logs` (
                                        `log_id` int(20) NOT NULL AUTO_INCREMENT,
                                        `log_entry_type` varchar(255) NOT NULL,
                                        `log_entry_id` int(20) NOT NULL,
                                        `log_user_id` int(20) NOT NULL,
                                        `log_action` varchar(255) NOT NULL,
                                        `log_datetime` datetime NOT NULL,
                                    PRIMARY KEY (`log_id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=latin1;
                                    CREATE TABLE IF NOT EXISTS `".$prefix."options` (
                                        `option_id` int(20) NOT NULL AUTO_INCREMENT,
                                        `option_key` varchar(255) NOT NULL,
                                        `option_value` longtext NOT NULL,
                                    PRIMARY KEY (`option_id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
                                    CREATE TABLE IF NOT EXISTS `".$prefix."user` (
                                        `id` int(20) NOT NULL AUTO_INCREMENT,
                                        `email` varchar(255) NOT NULL,
                                        `password` varchar(255) NOT NULL,
                                        `status` int(11) NOT NULL DEFAULT '0',
                                    PRIMARY KEY (`id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
                                    CREATE TABLE IF NOT EXISTS `".$prefix."user_meta` (
                                        `meta_id` int(20) NOT NULL AUTO_INCREMENT,
                                        `user_id` int(20) NOT NULL,
                                        `meta_key` varchar(255) NOT NULL,
                                        `meta_value` longtext NOT NULL,
                                    PRIMARY KEY (`meta_id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=latin1;
                                    CREATE TABLE IF NOT EXISTS `".$prefix."visits` (
                                        `visit_id` int(20) NOT NULL AUTO_INCREMENT,
                                        `visit_ip` varchar(39) NOT NULL,
                                        `visit_hits` int(20) NOT NULL,
                                        `visit_date` date NOT NULL,
                                    PRIMARY KEY (`visit_id`)
                                    ) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
                                ");

                                config::add('DB', [
                                    'host' => $array['host'],
                                    'user' => $array['user'],
                                    'password' => $array['password'],
                                    'database' => $array['database'],
                                    'prefix' => $prefix
                                ], true);
                                config::save();

                                header('Location: ?step=informations');
                                exit();

                            }

                        } else {
                            $form->getErrors();
                        }

                    }

                    echo $form->show();

                } elseif($step == 'informations') {

                    $form = new form();

                    $field = $form->addTextField('sitename', '');
                    $field->fieldName(lang::get('sitename'));
                    $field->fieldValidate();

                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';

                    $field = $form->addTextField('siteurl', $protocol.$_SERVER['HTTP_HOST'].'/');
                    $field->fieldName(lang::get('siteurl'));
                    $field->fieldValidate();

                    $field = $form->addTextField('timezone', config::get('timezone'));
                    $field->fieldName(lang::get('timezone'));
                    $field->fieldValidate();

                    $form->addRawField('<hr>');

                    $field = $form->addTextField('username', '');
                    $field->fieldName(lang::get('username'));
                    $field->fieldValidate();

                    $field = $form->addTextField('email', '');
                    $field->fieldName(lang::get('email'));
                	$field->fieldValidate('valid_email|required');

                    $field = $form->addPasswordField('password', '');
                    $field->fieldName(lang::get('password'));
                    $field->fieldValidate();

                    if($form->isSubmit()) {

                        if($form->validation()) {

                            $array = $form->getAll();

                            $DB = config::get('DB');

                            sql::connect($DB['host'], $DB['user'], $DB['password'], $DB['database'], $DB['prefix']);

                            $siteurl = (substr($array['siteurl'], -1) == '/') ? $array['siteurl'] : $array['siteurl'].'/';

                            config::add('url', $siteurl, true);
                            config::add('timezone', $array['timezone'], true);
                            config::add('install', false, true);
                            config::save();

                            option::set('sitename', $array['sitename']);

                            $model = new UserModel();

                            $model->insert([
                                'username' => $array['username'],
                                'email' => $array['email'],
                                'password' => password_hash($array['password'], PASSWORD_DEFAULT),
                                'status' => 1
                            ], true);

                            header('Location: ?step=finished');
                            exit();

                        } else {
                            $form->getErrors();
                        }

                    }

                    echo $form->show();

                } elseif($step == 'finished') {

                    echo '
                        <p>'.lang::get('install_success').'</p>
                        <a href="../admin">'.lang::get('admin_login').'</a> -
                        <a href="http://gearcms.org" target="_blank">'.lang::get('gear_website').'</a> -
                        <a href="http://forum.gearcms.org" target="_blank">'.lang::get('gear_forum').'</a>
                    ';

                } else {

                    $form = new form();

                    $field = $form->addSelectField('lang', '');
                    $field->fieldName(lang::get('language'));
                    $field->fieldValidate();
                    $field->add(null, '-');
                    foreach(lang::getAll() as $key => $val) {
                        $field->add($key, $val);
                    }

                    if($form->isSubmit()) {

                        if($form->validation()) {

                            $array = $form->getAll();

                            config::add('lang', $array['lang'], true);
                            config::save();

                            header('Location: ?step=database');
                            exit();

                        } else {
                            $form->getErrors();
                        }

                    }

                    echo $form->show();

                }

            ?>

        </div>

        <span><?=sprintf(lang::get('version'), config::get('version')); ?></span>

    </div>

</section>
