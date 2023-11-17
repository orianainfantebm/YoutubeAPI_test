<?php
session_start();

unset($_SESSION['access_token']);

session_destroy();

header('Location: ' . filter_var('index.php', FILTER_SANITIZE_URL));
