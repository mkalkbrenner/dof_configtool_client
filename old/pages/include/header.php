<!doctype html>
<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="DOF Configtool Client">
  <meta name="author" content="mk47">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

  <style>
    .bd-placeholder-img {
      font-size: 1.125rem;
      text-anchor: middle;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }

    @media (min-width: 768px) {
      .bd-placeholder-img-lg {
        font-size: 3.5rem;
      }
    }
  </style>

  <title>DOF Configtool Client 0.1.0 by mk47</title>
</head>
<?php $route = basename($_SERVER['SCRIPT_FILENAME'], '.php'); ?>
<body class="bg-light">
<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
  <a class="navbar-brand" href="#">DOF Configtool Client</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarCollapse">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item<?php strpos($route, 'index') !== 0 ? print ' active' : ''; ?>">
        <a class="nav-link" href="/index.php">Welcome</a>
      </li>
        <li class="nav-item<?php strpos($route, 'download') !== 0 ? print ' active' : ''; ?>">
        <a class="nav-link" href="/pages/download.php">Download</a>
      </li>
      <li class="nav-item<?php strpos($route, 'tweak') !== 0 ? print ' active' : ''; ?>">
        <a class="nav-link" href="/pages/tweak.php">Tweak</a>
      </li>
      <li class="nav-item<?php strpos($route, 'settings') !== 0 ? print ' active' : ''; ?>">
        <a class="nav-link" href="/pages/settings.php">Settings</a>
      </li>
    </ul>
  </div>
</nav>
<div class="container">
<?php
