<?php

  session_start();

  if((isset($_SESSION['logged'])) && ($_SESSION['logged'] == true))
	{
    if(isset($_SESSION['username'])){
      header('Location: menu_glowne.php');
      exit();
    }

		header('Location: menu_glowne.php');
		exit();
	}

?>


<!DOCTYPE html>

<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Logowanie</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <link rel="stylesheet" href="style_logowanie_rejestracja.css">
  <link rel="stylesheet" href="./css/cap.css">

</head>

<body class="d-flex align-items-center py-4 justify-content-center">

  <main class="form-signin w-100 m-auto" style="max-width: 400px;">
    <form action="zaloguj.php" method="post">
      <h1 class="text-center pb-4">Logowanie</h1>

      <div class="container">
        <div class="input-group">
          <span class="input-group-text">
            <i class="icon-mail-alt"></i>
          </span>
          <div class="form-floating">
            <input type="text" name="e-mail" class="form-control mb-1" id="floatingInput" placeholder="E-mail">
            <label for="floatingInput">Adres e-mail</label>
          </div>
        </div>
        <div class="input-group">
        <span class="input-group-text">
          <i class="icon-lock-filled"></i>
        </span>
        <div class="form-floating">
          <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password">
          <label for="floatingPassword">Hasło</label>
        </div>
      </div>

      <div class="form-check text-start my-3">
        <input class="form-check-input justify-content-center" type="checkbox" value="remember-me"
          id="flexCheckDefault">
        <label class="form-check-label" for="flexCheckDefault">
          Zapamiętaj mnie
        </label>
      </div>
      <button class="btn btn-primary w-100 py-2" type="submit">Zaloguj</button>
      </div>
    </form>
    
    <?php
	
    if (isset($_SESSION['error_log_in'])) {
      echo $_SESSION['error_log_in'];
      unset($_SESSION['error_log_in']); 
    }

   ?>

  </main>

</body>

</html>