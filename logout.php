<?php

@include 'config.php';

session_start();
session_unset();
session_destroy();

header('Location: http://localhost/Groco/Groco/grocery%20store/');

?>