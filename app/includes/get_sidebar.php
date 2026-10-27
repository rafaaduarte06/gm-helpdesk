<?php
require_once('sidebar.php');

$userType = isset($_POST['userType']) ? $_POST['userType'] : 'tecnico';
$userName = isset($_POST['userName']) ? $_POST['userName'] : 'Usuário';
$userPhoto = isset($_POST['userPhoto']) ? $_POST['userPhoto'] : null;
$activePage = isset($_POST['activePage']) ? $_POST['activePage'] : '';

echo getSidebarContent($userType, $userName, $userPhoto, $activePage);
?> 