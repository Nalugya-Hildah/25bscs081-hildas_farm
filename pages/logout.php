<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /hildas_farm/index.php');
exit;
