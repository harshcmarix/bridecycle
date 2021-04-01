<?php
$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['admin/site/reset-password', 'token' => $user->password_reset_token]);
?>
Hello <?= $user->first_name ?>,
Follow the link below to reset your password:
<?= $resetLink ?>