<?php
require_once "includes/auth.php";

encerrarSessao();

header("Location: login.php");
exit;
