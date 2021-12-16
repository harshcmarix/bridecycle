<?php

use app\modules\api\v1\models\User;

$fullName = '';
if ($user instanceof User && !empty($user->first_name)) {
    $fullName .= $user->first_name;
}
if ($user instanceof User && !empty($user->last_name)) {
    $fullName .= " " . $user->last_name;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <meta
            name="viewport"
            content="width=device-width, initial-scale=1, maximum-scale=1"
    />

    <title>Bride Cycle - Email </title>
    <style type="text/css">
        body {
            background: #ebf0f5;
            margin: 0;
            font-family: "verdana", Helvetica, Arial, sans-serif !important;
            color: #1b1b1b;
            font-size: 14px;
            line-height: 20px;
            font-weight: 400;
        }

        * {
            margin: 0;
            padding: 0;
        }

        a {
            text-decoration: none;
        }

        a,
        img {
            border: none;
            outline: none;
        }

        .strong {
            font-weight: bold;
        }

        p {
            margin: 0;
        }

        table {
            border-collapse: collapse !important;
        }
    </style>
</head>

<body>
<div style="background-color: #ebf0f5">
    <table
            style="
          margin: 0 auto;
          text-align: center;
          width: 100%;
          border: 0;
          padding: 0;
          background-color: #ebf0f5;
        "
            cellspacing="0"
            cellpadding="0"
    >
        <tr>
            <td
                    bgcolor="#ebf0f5"
                    style="padding: 30px 0; background-color: #ebf0f5"
            >
                <table
                        cellpadding="0"
                        cellspacing="0"
                        style="
                text-align: left;
                vertical-align: top;
                width: 640px;
                margin: 0 auto;
                background-color: #ffffff;
                max-width: 640px;
              "
                >
                    <tr>
                        <td
                                bgcolor="#899576"
                                style="
                    padding: 25px 20px;
                    text-align: center;
                    vertical-align: middle;
                    background: #899576;
                  "
                        >
                            <a
                                    href="https://bridecycle.com/" target="_blank"
                                    style="
                      display: inline-block;
                      text-decoration: none;
                      color: #fff;
                      outline: none;
                    "
                            ><img
                                        src="<?= Yii::$app->params['mail_image_base_path'] ?>/uploads/logo.png"
                                        style="
                        margin: 0;
                        vertical-align: middle;
                        border: none;
                        outline: none;
                        height: 48px;
                        width: 85px;
                      "
                                        alt="Bride Cycle"
                                        width="85"
                                        height="48"
                                /></a>
                        </td>
                    </tr>

                    <tr>
                        <td
                                align="left"
                                valign="top"
                                bgcolor="#ffffff"
                                style="
                    padding: 25px 25px 5px;
                    text-align: left;
                    vertical-align: top;
                    background-color: #ffffff;
                    font-family: 'verdana', Helvetica, Arial, sans-serif !important;
                    color: #1b1b1b;
                    font-size: 14px;
                    line-height: 22px;
                    font-weight: 400;
                  "
                        >
                            <strong style="font-size: 14px; font-weight: bold"
                            >Hello
                                <?php echo $user->first_name . " " . $user->last_name; ?> </strong
                            >,


                        </td>
                    </tr>

                    <tr>
                        <td
                                align="left"
                                valign="top"
                                width="100%"
                                style="
                          padding: 7px 25px;
                          text-align: left;
                          vertical-align: top;
                          font-family: 'verdana', Helvetica, Arial, sans-serif !important;
                          color: #1b1b1b;
                          font-size: 14px;
                          line-height: 22px;
                          font-weight: 400;
                          width: 100%;
                        "
                        >


                            <p
                                    style="
                      text-align: left;
                      vertical-align: top;
                      font-family: 'verdana', Helvetica, Arial, sans-serif !important;
                      color: #1b1b1b;
                      font-size: 14px;
                      line-height: 22px;
                      font-weight: 400;
                      margin: 0;
                    "
                            >
                                We've received a request to forgot your password.
                                If you didn't make the request, just ignore this
                                email.
                            </p>

                            <div
                                    style="
                      height: 15px;
                      width: 100%;
                      line-height: 15px;
                      clear: both;
                    "
                            ></div>

                            <p
                                    style="
                      text-align: left;
                      vertical-align: top;
                      font-family: 'verdana', Helvetica, Arial, sans-serif !important;
                      color: #1b1b1b;
                      font-size: 14px;
                      line-height: 22px;
                      font-weight: 400;
                      margin: 0;
                    "
                            >
                                Your temporary password is
                                <strong style="font-size: 13px; font-weight: bold"><?= $user->temporary_password ?></strong>
                            </p>


                        </td>
                    </tr>

                    <tr>
                        <td
                                align="left"
                                valign="top"
                                width="100%"
                                style="
                          padding: 20px 25px 25px 25px;
                          text-align: left;
                          vertical-align: top;
                          font-family: 'verdana', Helvetica, Arial, sans-serif !important;
                          color: #1b1b1b;
                          font-size: 14px;
                          line-height: 22px;
                          font-weight: 400;
                          width: 100%;
                        "
                        >
                            Regards,<br/>
                            <strong style="font-size: 13px; font-weight: bold"
                            >Bride Cycle</strong
                            >
                        </td>
                    </tr>
                    <tr>
                        <td
                                bgcolor="#27272A"
                                valign="top"
                                style="
                    padding: 20px 15px;
                    text-align: center;
                    vertical-align: top;
                    background-color: #1a1a1a;
                    font-family: 'verdana', Helvetica, Arial, sans-serif !important;
                    color: #fff;
                    font-size: 13px;
                    line-height: 20px;
                    font-weight: 400;
                    text-align: center;
                  "
                        >
                            Copyright &copy;
                            <?= date("Y"); ?>
                            <a href="https://bridecycle.com/" target="_blank">Bride Cycle</a>. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
