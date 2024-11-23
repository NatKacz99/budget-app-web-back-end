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
            $price = str_replace(',','.',$_POST['price']);
    
            if (!is_numeric($price) || empty($price)) {
                $_SESSION['error_price'] = 'Podaj kwotę w formacie liczbowym.';
                $all_OK = false;
            }

            if (empty($_POST['category'])) {
                $_SESSION['error_category'] = 'Wybierz kategorię przychodu.';
                $all_OK = false;
            } else {
                $income_category = $_POST['category'];
            }
    
            if ($all_OK) {
                $date_income = $_POST['date'];
                $user_id = $_SESSION['user_id']; 
                $comment = $_POST['comment'];
            
                $stmt_category = $connect->prepare("SELECT id FROM incomes_category_assigned_to_users WHERE user_id = :user_id AND name = :income_category");
                $stmt_category->bindParam(':user_id', $user_id);
                $stmt_category->bindParam(':income_category', $income_category);
                $stmt_category->execute();
                $category_result = $stmt_category->fetch(PDO::FETCH_ASSOC);
                $income_category_id = $category_result['id'];
            
                if ($income_category_id) {
                    $stmt = $connect->prepare("INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment) VALUES (:user_id, :income_category_id, :price, :date_income, :comment)");
            
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':income_category_id', $income_category_id);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':date_income', $date_income);
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

    <title>Dodaj przychód</title>

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
                <li><a href="dodaj_przychod.php" class="add-incoming"><i class="icon-dollar"></i>Dodaj przychód</a></li>
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
                <li><a href="dodaj_przychod.php" class="add-incoming"><i class="icon-dollar"></i>Dodaj przychód</a></li>
                <li><a href="dodaj_wydatek.php" class="add-expense"><i class="icon-basket"></i>Dodaj wydatek</a></li>
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

        <div class="error" style = "color: red">
            <?php
                
                if (isset($_SESSION['error_price'])) {
                echo $_SESSION['error_price'];
                unset($_SESSION['error_price']); 
                }

            ?>
        </div>

                <div class="input-group">
                    <span class="icon-container"><i class="icon-calendar"></i></span>
                    <input type="text" name="date" class="datepicker form-control" placeholder="Data">
                </div>


                <div>
                    <label for="category">
                        <select class="category" name="category">
                            <option selected disabled>Wybierz kategorię przychodu</option>
                            <option>Wynagrodzenie</option>
                            <option>Odsetki bankowe</option>
                            <option>Sprzedaż na allegro</option>
                            <option>Inne</option>
                        </select>
                    </label>
                    <div class="error" style = "color: red">
                        <?php
                            
                            if (isset($_SESSION['error_category'])) {
                                echo $_SESSION['error_category'];
                                unset($_SESSION['error_category']); 
                            }

                        ?>
                    </div>
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