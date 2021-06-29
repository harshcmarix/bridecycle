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

Hello <?= $fullName ?>,<br>

<p>We've received a request to forgot your password.
    If you didn't make the request, just ignore this email.</p>
<p>Your temporary password is <strong><?= $user->temporary_password ?></strong>.</p>

Thanks,
<?php echo "<strong> $appname </strong>" ?>
