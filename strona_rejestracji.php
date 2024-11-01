<?php

session_start();

if (isset($_POST['e-mail'])) {
  $all_OK = true;

  $password1 = $_POST['password1'];
  $password2 = $_POST['password2'];

  if ($password1 !== $password2) {
    $all_OK = false;
    $_SESSION['error_password2'] = "Podane hasła nie są identyczne.";
  }
}

?>


<!DOCTYPE html>

<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Rejestracja</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <link rel="stylesheet" href="style_logowanie_rejestracja.css">
  <link rel="stylesheet" href="./css/cap.css">

</head>

<body class="d-flex align-items-center py-4 justify-content-center">

  <main class="form-signin w-100 m-auto" style="max-width: 400px;">
    <form method="post">
      <h1 class="text-center pb-4">Rejestracja</h1>

      <div class="container">
        <div class="input-group">
          <span class="input-group-text">
            <i class="icon-user"></i>
          </span>
          <div class="form-floating">
            <input type="text" class="form-control mb-1" class="floatingInput" placeholder="Name">
            <label for="floatingInput">Imię</label>
          </div>
        </div>
        <div class="input-group">
          <span class="input-group-text">
            <i class="icon-mail-alt"></i>
          </span>
          <div class="form-floating">
            <input type="text" name="e-mail" class="form-control mb-1" class="floatingInput" placeholder="E-mail">
            <label for="floatingInput">Adres e-mail</label>
          </div>
        </div>
        <div class="input-group">
          <span class="input-group-text">
            <i class="icon-lock-filled"></i>
          </span>
          <div class="form-floating">
            <input type="password" name="password1" class="form-control mb-1" id="password1"
              placeholder="Password">
            <label for="floatingPassword">Hasło</label>
          </div>
        </div>

        <div class="input-group">
          <span class="input-group-text">
            <i class="icon-lock-filled"></i>
          </span>
          <div class="form-floating">
            <input type="password" name="password2" class="form-control" id="password2" placeholder="Password">
            <label for="floatingPassword">Powtórz hasło</label>
          </div>
        </div>

        <div class="error">
              <?php
                if (isset($_SESSION['error_password2'])) {
                  echo $_SESSION['error_password2'];
                  unset($_SESSION['error_password2']);
                }
              ?>
        </div>


        <button class="btn btn-primary w-100 py-2 mt-4" type="submit">Zarejestruj się</button>
      </div>
    </form>
  </main>
</body>