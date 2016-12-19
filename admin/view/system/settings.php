<?php

    $form = new form();

    $form->addTab(lang::get('general'));

    $field = $form->addTextField('sitename', option::get('sitename'));
    $field->fieldName(lang::get('sitename'));
    $field->fieldValidate();

    $field = $form->addSelectField('lang', config::get('lang'));
    $field->fieldName(lang::get('language'));
    foreach(lang::getAll() as $key => $val) {
        $field->add($key, $val);
    }

    $form->addTab(lang::get('advanced'));

    $field = $form->addTextField('siteurl', config::get('url'));
    $field->fieldName(lang::get('siteurl'));
    $field->fieldValidate();

    $field = $form->addSwitchField('cache', config::get('cache'));
    $field->fieldName(lang::get('cache'));
    $field->add(true, lang::get('yes'));

    $field = $form->addSwitchField('debug', config::get('debug'));
    $field->fieldName(lang::get('debug'));
    $field->add(true, lang::get('yes'));

    if($form->isSubmit()) {

        if($form->validation()) {

            $array = $form->getAll();

            $cache = ($array['cache']) ? true : false;
            $debug = ($array['debug']) ? true : false;

            $siteurl = (substr($array['siteurl'], -1) == '/') ? $array['siteurl'] : $array['siteurl'].'/';

            config::add('url', $siteurl, true);
            config::add('lang', $array['lang'], true);
            config::add('cache', $cache, true);
            config::add('debug', $debug, true);
            config::save();

            option::set('sitename', $array['sitename']);

            message::success(lang::get('settings_edited'));

            url::refresh();

        } else {
            $form->getErrors();
        }

    }

?>

<div class="columns">
    <div class="md-9 lg-8">
        <div class="box">
            <h3><?=option::get('sitename'); ?></h3>
            <?=$form->show(); ?>
        </div>
    </div>
    <div class="md-3 lg-4">
        <div class="box">
            <strong>PHP</strong> <?=config::getPHPVersion(); ?><br>
            <strong>MySQL</strong> <?=config::getMySQLVersion(); ?>
        </div>
    </div>
</div>

<?php
theme::addJSCode('
    new Vue({
        el: "#app",
        data: {
            headline: "'.lang::get('settings').'"
        }
    });
');
?>
