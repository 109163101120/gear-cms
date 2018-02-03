<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $app->view->global('title'); ?></title>

    <script>
        var $gear = <?= json_encode((array)$app->config->get('system')); ?>;
    </script>

    <?= $app->assets->getCSS(); ?>

</head>
<body>

    <div id="gear">
        <?= $app->view->get('content') ?>
    </div>

    <?= $app->assets->getJS(); ?>
    <?= $app->assets->getJS('vue'); ?>
    <?= $app->assets->getJS('afterVue'); ?>

</body>
</html>
