<?php

use yii\helpers\Html;
use yii\web\UnauthorizedHttpException;
?>
<center>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="error-template">
                    <img src="<?= Yii::getAlias("@images") . '/logo-50height.png' ?>" style="margin-top: 5%;" >
                    <h1>Oops!</h1>                    
                    <div class="error-details">
                        <h2><?= (!empty($exception) && $exception instanceof UnauthorizedHttpException) ? $exception->statusCode . " You are not authorised to perform this action!." : "404 Not Found!."; ?></h2>
                        <?php if (!empty($exception) && $exception instanceof UnauthorizedHttpException && $exception->statusCode == 401) { ?>

                            Sorry, an error has occured, Requested page access denied!.

                        <?php } else { ?>

                            Sorry, an error has occured, Requested page not found!.

                        <?php } ?>
                    </div>
                    <br>
                    <div class="error-actions">
                        <h5>
                            <?= Html::a('Go To Home Page', ['dashboard/index',], ['style' => 'text-decoration:none;']) ?>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</center>
