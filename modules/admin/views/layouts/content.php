<?php

use yii\widgets\Breadcrumbs;
use dmstr\widgets\Alert;

?>

<style>
    button.growl-close {
        margin-right: -313px;
        margin-left: 290px;
        color: white;
        background: none;
        border: none;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <?php if (isset($this->blocks['content-header'])) { ?>
            <h1><?= $this->blocks['content-header'] ?></h1>
        <?php } else { ?>
            <h1>
                <?php
                if ($this->title !== null) {
                    echo \yii\helpers\Html::encode($this->title);
                }
                ?>
            </h1>
        <?php } ?>

        <?=
        Breadcrumbs::widget(
            [
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]
        )
        ?>

    </section>

    <section class="content">
        <?php //echo Alert::widget(); ?>
        <?php
        $flash_messages = Yii::$app->session->getAllFlashes();
        if (!empty($flash_messages)) {
            foreach ($flash_messages as $flash_message_type => $message) {
                if ($flash_message_type == 'danger') {
                    $icon = "fa fa-times-circle";
                } elseif ($flash_message_type == 'info') {
                    $icon = "fa fa-info-circle";
                } elseif ($flash_message_type == 'warning') {
                    $icon = "fa fa-exclamation-circle";
                } else {
                    $icon = "glyphicon glyphicon-ok-sign";
                }

                echo \kartik\growl\Growl::widget([
                    'type' => $flash_message_type,
                    'icon' => $icon,
                    'title' => ($flash_message_type == 'danger') ? ucfirst($flash_message_type = "error") : ucfirst($flash_message_type),
                    'showSeparator' => true,
                    'body' => $message,
                    'pluginOptions' => [
                        'showProgressbar' => false,
                        //'timer' => 5000,
                        'placement' => [
                            'from' => 'top',
                            'align' => 'right',
                        ],
                    ],
                ]);
            }
        }
        ?>
        <?= $content ?>
    </section>
</div>

<footer class="main-footer text-right">
    <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="javascript:void(0);"><?= Yii::$app->name ?></a>.</strong>
</footer>