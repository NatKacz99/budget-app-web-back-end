<?php

    session_start();

    if(!isset($_SESSION['logged']))
	{
		header('Location: strona_logowania.php');
		exit();
	}

    require_once "connect.php";

    try {
        $connect = new PDO("mysql:host=$host;dbname=$db_name", $db_user, $db_password);
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $all_OK = true;
    
        if (isset($_POST['price'])) {
            $price = $_POST['price'];
    
            if (!is_numeric($price)) {
                $_SESSION['error_price'] = 'Podaj kwotę w formacie liczbowym.';
                $all_OK = false;
            }
    
            if ($all_OK) {
                $expense_category = $_POST['category'];
                $payment_method = $_POST['paymentMethod'];
                $date_expense = $_POST['date'];
                $user_id = $_SESSION['user_id']; 
                $comment = $_POST['comment'];
            
                $stmt_category = $connect->prepare("SELECT id FROM expenses_category_assigned_to_users WHERE user_id = :user_id AND name = :expense_category");
                $stmt_category->bindParam(':user_id', $user_id);
                $stmt_category->bindParam(':expense_category', $expense_category);
                $stmt_category->execute();
                $category_result = $stmt_category->fetch(PDO::FETCH_ASSOC);
                $expense_category_id = $category_result['id'];
            
                $stmt_payment = $connect->prepare("SELECT id FROM payment_methods_assigned_to_users WHERE user_id = :user_id AND name = :payment_method");
                $stmt_payment->bindParam(':user_id', $user_id);
                $stmt_payment->bindParam(':payment_method', $payment_method);
                $stmt_payment->execute();
                $payment_result = $stmt_payment->fetch(PDO::FETCH_ASSOC);
                $payment_method_id = $payment_result['id'];
            
                if ($expense_category_id && $payment_method_id) {
                    $stmt = $connect->prepare("INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment) VALUES (:user_id, :expense_category_id, :payment_method_id, :price, :date_expense, :comment)");
            
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':expense_category_id', $expense_category_id);
                    $stmt->bindParam(':payment_method_id', $payment_method_id);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':date_expense', $date_expense);
                    $stmt->bindParam(':comment', $comment);
            
                    $stmt->execute();
                } else {
                    echo "Błąd: nie znaleziono odpowiedniej kategorii lub metody płatności.";
                    print_r($stmt_payment->errorInfo());
                }
            }
            
        }
    } catch (PDOException $error) {
        echo '<span style="color: red">Błąd serwera! Przepraszamy za niedogodności i zapraszamy do wizyty w innym terminie!</span>';
      }
	
?>


<!DOCTYPE html>

<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dodaj wydatek</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    

    <link rel="stylesheet" href="style_dodaj_wydatek.css">
    <link rel="stylesheet" href="./css/cap.css">

    <script>
        $(function () {
            $(".datepicker").datepicker({
                dateFormat: "yy-mm-dd"
            });

            $(".menu-mobile__collapsible").on("click", function () {
                let $menuList = $(".menu-mobile__list");

                if ($menuList.hasClass("hidden")) {
                    $menuList.removeClass("hidden");
                } else {
                    $menuList.addClass("hidden");
                }
            });
        });
    </script>
</head>

<body>
    <section>
        <nav id="menu-desktop">
            <ul class="menu-desktop__list">
                <li><a href="index.html" class="main-page"><i class="icon-home"></i>Strona główna</a></li>
                <li><a href="#" class="add-incoming"><i class="icon-dollar"></i>Dodaj przychód</a></li>
                <li><a href="dodaj_wydatek.php" class="add-expense"><i class="icon-basket"></i>Dodaj wydatek</a></li>
                <li><a href="przegladaj_bilans.php" class="review-balance"><i class="icon-calc"></i>Przeglądaj bilans</a></li>
                <li><a href="#" class="settings"><i class="icon-cog"></i>Ustawienia</a></li>
                <li><a href="wyloguj.php" class="log-out"><i class="icon-logout"></i>Wyloguj się</a></li>
            </ul>
        </nav>

        <nav id="menu-mobile">
            <button class="menu-mobile__collapsible">
                <img src="menu_ikona.svg" alt="hamburger menu">
            </button>
            <ul class="menu-mobile__list hidden">
                <li><a href="index.html" class="main-page"><i class="icon-home"></i>Strona główna</a></li>
                <li><a href="#" class="add-incoming"><i class="icon-dollar"></i>Dodaj przychód</a></li>
                <li><a href="dodaj_wydatek.html" class="add-expense"><i class="icon-basket"></i>Dodaj wydatek</a></li>
                <li><a href="przegladaj_bilans.php" class="review-balance"><i class="icon-calc"></i>Przeglądaj bilans</a></li>
                <li><a href="#" class="settings"><i class="icon-cog"></i>Ustawienia</a></li>
                <li><a href="wyloguj.php" class="log-out"><i class="icon-logout"></i>Wyloguj się</a></li>
            </ul>
        </nav>
    </section>

    <article>
        <form method="post">
            <h2>Wprowadź dane</h2>
            <div class="container-outside">
            <div class="container-inside">

                <div class="input-group">
                    <span class="icon-container"><i class="icon-pencil"></i></span>
                    <input type="text" name="price" class="form-control" placeholder="Kwota">
                </div>

            <?php
                
                if (isset($_SESSION['error_price'])) {
                echo $_SESSION['error_price'];
                unset($_SESSION['error_price']); 
                }

            ?>

                <div class="input-group">
                    <span class="icon-container"><i class="icon-calendar"></i></span>
                    <input type="text" name="date" class="datepicker form-control" placeholder="Data">
                </div>

                <div>
                    <label for="payment-method">
                        <select class="payment-method" name="paymentMethod">
                            <option selected disabled>Wybierz sposób płatności</option>
                            <option>Gotówka</option>
                            <option>Karta debetowa</option>
                            <option>Karta kredytowa</option>
                        </select>
                    </label>
                </div>

                <div>
                    <label for="category">
                        <select class="category" name="category">
                            <option selected disabled>Wybierz kategorię wydatku</option>
                            <option>Jedzenie</option>
                            <option>Mieszkanie</option>
                            <option>Transport</option>
                            <option>Telekomunikacja</option>
                            <option>Opieka zdrowotna</option>
                            <option>Ubranie</option>
                            <option>Higiena</option>
                            <option>Dzieci</option>
                            <option>Rozrywka</option>
                            <option>Wycieczka</option>
                            <option>Szkolenia</option>
                            <option>Książki</option>
                            <option>Oszczędności</option>
                            <option>Na złotą jesień, czyli emeryturę</option>
                            <option>Spłata długów</option>
                            <option>Darowizna</option>
                            <option>Inne wydatki</option>
                        </select>
                    </label>
                </div>

                <div class="input-group">
                    <span class="icon-container"><i class="icon-pencil"></i></span>
                    <input type="text" name="comment" class="form-control" placeholder="Komentarz (opcjonalnie)">
                </div>

                <div class="buttons">
                    <input type="submit" value="Dodaj">
                    <input type="submit" value="Anuluj">
                </div>

            </div>
        </div>
        </form>

    </article>

</body>

</html>