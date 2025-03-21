<?php

session_start();

if (isset($_POST['e-mail'])) {
  $all_OK = true;

  $username = $_POST['username'];

  if (ctype_alnum($username) == false) {
    $all_OK = false;
    $_SESSION['error_username'] = "Nazwa może się składać tylko z liter i cyfr (bez polskich znaków).";
  }

  $email = $_POST['e-mail'];

  $email_for_validation = filter_var($email, FILTER_SANITIZE_EMAIL);
  if(filter_var($email_for_validation, FILTER_VALIDATE_EMAIL) == false || $email_for_validation != $email){
    $all_OK = false;
    $_SESSION['error_email'] = "Niepoprawny adres e-mail.";
  }

  $password1 = $_POST['password1'];
  $password2 = $_POST['password2'];

  if ($password1 !== $password2) {
    $all_OK = false;
    $_SESSION['error_password1'] = "Podane hasła nie są identyczne.";
  }

  if(strlen($password1) < 8){
    $all_OK = false;
    $_SESSION['error_password2'] = "Hasło powinno zawierać przynajmniej 8 znaków.";
  }

  if(!preg_match('~[0-9]+~', $password1)){
    $all_OK = false;
    $_SESSION['error_password3'] = "Hasło powinno mieć przynajmniej jedną cyfrę.";
  }

  $password_hash = password_hash($password1, PASSWORD_DEFAULT);

  $secret_key = "6LeWdloqAAAAAB3GgdokbP8w9rT5cXizEVYE7M2y";
  $check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$_POST['g-recaptcha-response']);

  $answer = json_decode($check);
  if($answer->success == false){
      $all_OK = false;
      $_SESSION['error_bot'] = "Potwierdź, że nie jesteś botem.";
  }

  require_once "connect.php";
	mysqli_report(MYSQLI_REPORT_STRICT);
  
  try {

    $connection = new mysqli($host, $db_user, $db_password, $db_name);
    
    if ($connection->connect_errno != 0) {
        throw new Exception(mysqli_connect_errno());
    } else {
        $score_mails = $connection->query("SELECT id FROM users WHERE email = '$email'");
        if (!$score_mails) {
            throw new Exception($connection->error);
        }

        $how_many_mails = $score_mails->num_rows;
        if ($how_many_mails > 0) {
            $all_OK = false;
            $_SESSION['error_mail'] = "Istnieje już konto o podanym adresie e-mail.";
        }

        $result_id_expense = $connection->query("SELECT MAX(id) AS max_id FROM incomes_category_assigned_to_users");
        if (!$result_id_expense) {
            throw new Exception($connection->error);
        }

        $row = $result_id_expense->fetch_assoc();
        $max_id = $row['max_id'];
        
        if ($max_id !== null) {
            $next_id = $max_id + 1;
            $connection->query("ALTER TABLE incomes_category_assigned_to_users AUTO_INCREMENT = $next_id");
        }


        if ($all_OK == true) {
            if ($connection->query("INSERT INTO users VALUES(NULL, '$username', '$password_hash', '$email')")) {
                $user_id = $connection->insert_id; 

                if ($user_id) {

                    $insert_categories_expense_query = "INSERT INTO expenses_category_assigned_to_users (user_id, name) 
                                                 SELECT '$user_id', name FROM expenses_category_default";

                    if (!$connection->query($insert_categories_expense_query)) {
                        throw new Exception("Błąd podczas dodawania kategorii wydatków: " . $connection->error);
                    }

                    $insert_payment_query = "INSERT INTO payment_methods_assigned_to_users (user_id, name) 
                    SELECT '$user_id', name FROM payment_methods_default";

                    if (!$connection->query($insert_payment_query)) {
                        throw new Exception("Błąd podczas dodawania metody płatności: " . $connection->error);
                    }

                    $insert_categories_income_query = "INSERT INTO incomes_category_assigned_to_users (user_id, name)
                                                SELECT '$user_id', name FROM incomes_category_default";

                    if (!$connection->query($insert_categories_income_query)) {
                      throw new Exception("Błąd podczas dodawania kategorii przychodów: " . $connection->error);
                    }

                } else {
                    throw new Exception("Błąd: Użytkownik nie został dodany.");
                  }

                $_SESSION['success_message'] = 'Rejestracja przebiegła pomyślnie. Możesz zalogować się na swoje konto.&nbsp;<a href="strona_logowania.php" style="text-decoration: none;">[Zaloguj się.]</a>';
            } else {
                throw new Exception("Błąd: Użytkownik nie został dodany.");
            }
        }

        $connection->close();
    }
} catch (Exception $error) {

    echo '<span style="color: red">Błąd serwera! Przepraszamy za niedogodności i prosimy o rejestrację w innym możliwym terminie.</span>';
    echo $error->getMessage();
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
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

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
            <input type="text" name="username" class="form-control mb-2" class="floatingInput" placeholder="Name" mb-2>
            <label for="floatingInput">Imię</label>
          </div>
        </div>

        <div class="error">
          <?php

            if (isset($_SESSION['error_username'])){
              echo $_SESSION['error_username'];
              unset($_SESSION['error_username']);
            }

          ?>
        </div>

        <div class="input-group">
          <span class="input-group-text">
            <i class="icon-mail-alt"></i>
          </span>
          <div class="form-floating">
            <input type="text" name="e-mail" class="form-control mb-2" class="floatingInput" placeholder="E-mail">
            <label for="floatingInput">Adres e-mail</label>
          </div>
        </div>

        <div class="error">
          <?php

            if (isset($_SESSION['error_email'])){
              echo $_SESSION['error_email'];
              unset($_SESSION['error_email']);
            }

          ?>

        </div>

        <div class="input-group">
          <span class="input-group-text">
            <i class="icon-lock-filled"></i>
          </span>
          <div class="form-floating">
            <input type="password" name="password1" class="form-control mb-2" id="password1"
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

        <div class="error">
              <?php
                if (isset($_SESSION['error_password3'])) {
                  echo $_SESSION['error_password3'];
                  unset($_SESSION['error_password3']);
                }
              ?>
        </div>

        <div class="error">
              <?php
                if (isset($_SESSION['error_password1'])) {
                  echo $_SESSION['error_password1'];
                  unset($_SESSION['error_password1']);
                }
              ?>
        </div>

        <div class="g-recaptcha" data-sitekey="6LeWdloqAAAAAHZHEw0P1JfZ_wvMLXIvrjh0ZaaP" style = "margin-top: 16px"></div>
		
          <?php
          
            if(isset($_SESSION['error_bot'])){
              echo '<div class = "error">'.$_SESSION['error_bot'].'</div>';
              unset($_SESSION['error_bot']);
            }
            
          ?>
		
		<br />

        <button class="btn btn-primary w-100 py-2 mt-4" type="submit">Zarejestruj się</button>
      </div>

    </form>
    <div class="text-center mt-3">
      <?php
        if (isset($_SESSION['success_message'])) {
          echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
          unset($_SESSION['success_message']);
        }
      ?>
    </div>
  </main>
</body>