<?php
// partials/header_auth.php
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'TicKeep'; ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/auth.css" />
</head>

<body>
<header class="tk-header py-3">
  <div class="container">
    <a class="tk-logo" href="index.php">TicKeep</a>
  </div>
</header>

<main class="tk-auth d-flex align-items-center">
  <div class="container px-3">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-6 col-xl-5">
        <section class="card tk-card shadow-lg border-0">
          <div class="card-body p-4 p-sm-5"></div>