<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

$nombreUsuario = $_SESSION['nombreUsuario'] ?? 'Usuario';
$nombreCargo   = $_SESSION['nombreCargo'] ?? 'Sin cargo';
$nombreArea    = $_SESSION['nombreArea'] ?? 'Sin área';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?? 'Dashboard - Gestión de Practicantes' ?></title>
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          onerror="this.onerror=null; this.href='<?= BASE_URL ?>assets/css/fontawesome/css/all.min.css'">
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" 
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" 
          crossorigin="anonymous"
          onerror="this.onerror=null; this.href='<?=  BASE_URL ?>assets/css/bootstrap/css/bootstrap.min.css'">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/inicio.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/practicantes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/documentos.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/asistencias.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/reportes.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/certificados.css">
    
</head>
<body>
