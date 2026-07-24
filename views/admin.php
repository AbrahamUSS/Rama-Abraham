<?php
Security::verificarRol(['Director', 'Administrador']);

$pageTitle = "Portal Administrativo - IEP Corazón de Jesús College";
require_once "views/components/head.php";
?>
<body>

  <div id="app-layout">
    <!-- overlay móviles -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- sidebar -->
    <?php 
    $activeRole = 'admin';
    require_once "views/components/sidebar.php"; 
    ?>

    <!-- contenido principal -->
    <div class="main-wrapper">
      <!-- navbar -->
      <?php 
      $userRoleLabel = "Director";
      $badgeCount = 0;
      require_once "views/components/navbar.php"; 
      ?>

      <!-- Router JS -->
      <main class="main-content" id="main-content"></main>
    </div>
  </div>

  <!-- modal -->
  <?php require_once "views/components/modal.php"; ?>

  <!-- scripts -->
  <?php 
  $moduleScript = "admin.js";
  require_once "views/components/scripts.php"; 
  ?>
</body>
</html>
