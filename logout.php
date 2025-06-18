<?php
require_once 'PHP.php';

Sessao::RemoverCookiesLogin();
Sessao::FinalizarSessao();
header('Location: login');
exit;
?>
