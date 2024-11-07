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
  <link rel="stylesheet" href="style_menu_glowne.css">
  <link rel="stylesheet" href="./css/cap.css">
</head>

<body>
  <p>Hej <?php echo $_SESSION['username']; ?>! Zalogowałeś się na swoje konto.</p>

  <h1>Menu główne</h1>

  <div id="container-menu">
    <nav>
      <a href="index.html" class="main-page">
        <div class="option"><i class="icon-home"></i>Strona główna</div>
      </a>
      <a href="dodaj_przychod.php" class="add-incoming">
        <div class="option"><i class="icon-dollar"></i>Dodaj przychód</div>
      </a>
      <a href="dodaj_wydatek.php" class="add-expense">
        <div class="option">
          <i class="icon-basket"></i>Dodaj wydatek
        </div>
      </a>
      <a href="przegladaj_bilans.php" class="review-balance">
        <div class="option"><i class="icon-calc">
          </i>Przeglądaj bilans</div>
      </a>
      <a href="#" class="settings">
          <div class="option"><i class="icon-cog"></i>Ustawienia</div>
       </a>
       <a href="wyloguj.php" class="log-out">
         <div class="option"><i class="icon-logout"></i>Wyloguj się</div>
       </a>
    </nav>
  </div>
</body>