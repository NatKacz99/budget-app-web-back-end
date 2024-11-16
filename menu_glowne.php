<?php

  session_start();

  if(!isset($_SESSION['logged']))
  {
    header('Location: strona_logowania.php');
    exit();
  }

?>


<!DOCTYPE html>

<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Menu główne</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
  <link rel="stylesheet" href="style_menu_glowne.css">
  <link rel="stylesheet" href="./css/cap.css">
</head>

<body>
<div class="text-center mt-3 mb-5">
  <div class="alert alert-success px-1 pt-2 pb-0 mt-0"> <p style = "color: rgb(93, 93, 95)">Hej <?php echo $_SESSION['username']; ?>! Zalogowałeś się na swoje konto.</p></div>
</div>

  <h1>Menu główne</h1>

  <nav id="container-menu" class="py-3">
    <a class="nav-link" href="index.html">
      <div class="nav-item"><i class="icon-home"></i> Strona główna </div>
    </a>
    <a class="nav-link" href="dodaj_przychod.php">
      <div class="nav-item"><i class="icon-dollar"></i> Dodaj przychód</div>
    </a>
    <a class="nav-link" href="dodaj_wydatek.php">
      <div class="nav-item"><i class="icon-basket"></i> Dodaj wydatek </div>
    </a>
    <a class="nav-link" href="przegladaj_bilans.php">
      <div class="nav-item"><i class="icon-calc"></i> Przeglądaj bilans</div>
    </a>
    <a class="nav-link" href="#">
      <div class="nav-item"><i class="icon-cog"></i> Ustawienia</div>
    </a>
    <a class="nav-link" href="wyloguj.php">
      <div class="nav-item"><i class="icon-logout"></i> Wyloguj się</div>
    </a>
</div>
</nav>
</body>