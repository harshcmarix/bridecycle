<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $email string user email */
/* @var $body  string the review */
?>


<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600">
</head>
<body style=" background: #F5F5F5; line-height: 18px; margin: 0;font-family: 'Poppins', Helvetica, Arial, sans-serif !important; color: #1b1b1b;font-size: 14px;line-height: 20px;font-weight: 400">
<table width="70%" align="center" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td align="center" valign="center" style="background:#8A9673 none repeat scroll 0 0;height:80px">
            <table width="90%" align="center">
                <tbody>
                <tr>
                    <td><img src="<?= Yii::$app->params['mail_image_base_path'] ?>/uploads/logo.png"
                             class="CToWUd"></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td align="left" valign="top" style="border:1px solid #cecece">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>
                        <table align="center" cellpadding="0" cellspacing="0" border="0" width="90%">
                            <tbody>
                            <tr>
                                <td align="left" valign="top" style="font-family:'verdana';font-size:14px;color:#000">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                        <tr>
                                            <td style="border:0px;padding:20px 28px;background-color:#fff" valign="top">
                                                <table border="0" cellpadding="0" cellspacing="2" width="100%">
                                                    <tbody style="line-height:25px;font-size:14px">
                                                    <tr>
                                                        <td colspan="2">
                                                            <b>Hello <?= Html::encode($receiver->first_name) ?> <?= Html::encode($receiver->last_name) ?>
                                                                ,</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <p><?= Html::encode($message) ?></p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">Regards,<br/>Bride Cycle</td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td align="left" height="50" valign="center" bgcolor="#0054a5"
            style="font-size:12px;color:#ffffff;font-family:'verdana';text-align:center;background-color:#000">
            Copyright © <?= date("Y"); ?> Bride Cycle. All rights reserved.
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>