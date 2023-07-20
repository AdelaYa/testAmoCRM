<?php

use Classes\LeadForm;
use Classes\AmoCRM;

spl_autoload_register(function ($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

$form = new LeadForm();

if ($_POST && $form->validate($_POST)) {
    $amoCrm = new AmoCRM();
    $amoCrm->amoIntegration($_POST);
    $successResult = "Заявка успешно отправлена!";
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <link rel='stylesheet' href='css/style.css'/>
    <title>Заявка</title>
</head>

<body>
<?php
if (!empty($form->getErrors())): ?>
    <div class="alert">
        <?php
        foreach ($form->getErrors() as $error) {
            echo $error;
        } ?>
    </div>
<?php
endif ?>

<?php
if (!empty($successResult)): ?>
    <div class="alert">
        <?= $successResult ?>
    </div>
<?php
endif ?>

<form method="post">
    <h3>Новая заявка</h3>
    <input type="text" id="user_name" name="user_name" placeholder="Имя">
    <input type="email" id="user_email" name="user_email" placeholder="Email">

    <input type="tel" id="user_phone" name="user_phone" placeholder="Телефон">

    <input type="text" id="lead_price" name="lead_price" placeholder="Цена">

    <input type="submit" value="Отправить">
</form>

</body>
</html>

