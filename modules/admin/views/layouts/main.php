<?php

use yii\helpers\Html;

if (Yii::$app->controller->action->id === 'login') {
    echo $this->render(
        'main-login', ['content' => $content]
    );
} else {

    if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        app\modules\admin\assets\AdminAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-NXLFFXY4HG"></script>
        <script type="text/javascript" src="https://code.highcharts.com/highcharts.js"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-NXLFFXY4HG');
        </script>


        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="<?php echo Yii::$app->request->baseUrl; ?>/theme/admin/images/favicon.jpg" type="image/x-icon" />
        <?= Html::csrfMetaTags() ?>
        <title>Admin | <?= Html::encode($this->title) ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.min.css" crossorigin="anonymous">
        <?php $this->head() ?>
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?=
        $this->render(
            '_header.php', ['directoryAsset' => $directoryAsset]
        )
        ?>


        <?=
        $this->render(
            '_sidebar.php', ['directoryAsset' => $directoryAsset]
        )
        ?>


        <?=
        $this->render(
            'content.php', ['content' => $content, 'directoryAsset' => $directoryAsset]
        )
        ?>

    </div>
    <?php $this->endBody() ?>
    <script>
        $('document').ready(function () {
            $('.main-sidebar .sidebar .left-scroll ul li').on('click', function () {
                localStorage.setItem('sidebarScrollTop', $('.main-sidebar .left-scroll').scrollTop());
            });

            if (localStorage.getItem('sidebarScrollTop')) {
                $('.main-sidebar .sidebar .left-scroll').scrollTop(localStorage.getItem('sidebarScrollTop'));
                setTimeout(function () {
                    localStorage.removeItem('sidebarScrollTop');
                }, 500);
            }
        })
    </script>
    </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>
