<?php

    session_start();

    if(!isset($_SESSION['logged']))
	{
		header('Location: strona_logowania.php');
		exit();
	}

    require_once "connect.php";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db_name", $db_user, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $all_OK = true;
        $selected_period = isset($_POST['time-slot']) ? $_POST['time-slot'] : '';

        if($all_OK == true){
            $user_id = $_SESSION['user_id'];

            $results_expenses = [];
            $results_incomes = [];

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['time-slot'])) {

                
            $selected_period = $_POST['time-slot'];

            $start_day = "";
            $end_day = "";

            switch($selected_period){
                case 'bieżący_miesiąc':
                    $start_day = date('Y-m-01');
                    $end_day = date('Y-m-d');

                break;
                case 'poprzedni_miesiąc':
                    $start_day = date('Y-m-01', strtotime('-1 month'));
                    $end_day = date('Y-m-t', strtotime('-1 month'));

                break;
                case 'bieżący_rok':
                    $start_day = date('Y-01-01');
                    $end_day = date('Y-m-d');

                break;
                case 'niestandardowy':
                    if (isset($_POST['start_day']) && isset($_POST['end_day'])) {
                        $start_day = $_POST['start_day'];
                        $end_day = $_POST['end_day'];
                    };

                break;
                default:
                    echo "Brak odpowiedniego okresu czasu.";
                    break;
            }

            $stmt = $pdo->prepare("SELECT name AS kategoria_wydatku, SUM(amount) AS kwota_wydatku  FROM expenses
                                JOIN expenses_category_assigned_to_users ON expenses_category_assigned_to_users.id = expenses.expense_category_assigned_to_user_id
                                WHERE expenses.user_id = :user_id 
                                AND date_of_expense BETWEEN :start_day AND :end_day
                                GROUP BY expense_category_assigned_to_user_id
                                ORDER BY kwota_wydatku DESC");

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_day', $start_day);
            $stmt->bindParam(':end_day', $end_day);

            $stmt->execute();
            $results_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $how_many_categories_expenses = $stmt->rowCount();


            $stmt = $pdo->prepare("SELECT name AS kategoria_przychodu, SUM(amount) AS kwota_przychodu FROM incomes
                                JOIN incomes_category_assigned_to_users ON incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id
                                WHERE incomes.user_id = :user_id 
                                AND date_of_income BETWEEN :start_day AND :end_day
                                GROUP BY income_category_assigned_to_user_id
                                ORDER BY kwota_przychodu DESC");

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_day', $start_day);
            $stmt->bindParam(':end_day', $end_day);

            $stmt->execute();
            $results_incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $how_many_categories_incomes = $stmt->rowCount();

        }

        else{
            $stmt = $pdo->prepare("SELECT name AS kategoria_wydatku, SUM(amount) AS kwota_wydatku, date_of_expense, expense_comment    FROM expenses
                                    JOIN expenses_category_assigned_to_users ON expenses_category_assigned_to_users.id = expenses.expense_category_assigned_to_user_id
                                    WHERE expenses.user_id = :user_id 
                                    GROUP BY expense_category_assigned_to_user_id
                                    ORDER BY kwota_wydatku DESC");
    
                $stmt->bindParam(':user_id', $user_id);
    
                $stmt->execute();
                $results_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                $how_many_categories_expenses = $stmt->rowCount();
    
    
                $stmt = $pdo->prepare("SELECT name AS kategoria_przychodu, SUM(amount) AS kwota_przychodu, 
                date_of_income, income_comment FROM incomes
                                    JOIN incomes_category_assigned_to_users ON incomes_category_assigned_to_users.id = incomes.income_category_assigned_to_user_id
                                    WHERE incomes.user_id = :user_id 
                                    GROUP BY income_category_assigned_to_user_id
                                    ORDER BY kwota_przychodu DESC");
    
                $stmt->bindParam(':user_id', $user_id);
    
                $stmt->execute();
                $results_incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
                $how_many_categories_incomes = $stmt->rowCount();
            }

            $dataPointsIncomes = [];
            $total_sum_incomes = 0;

            foreach ($results_incomes as $row) {
                $total_sum_incomes += $row['kwota_przychodu'];
            }

            foreach ($results_incomes as $row) {
                if ($total_sum_incomes > 0) {
                    $dataPointsIncomes[] = array(
                        "label" => $row['kategoria_przychodu'],
                        "y" => ($row['kwota_przychodu'] / $total_sum_incomes) * 100
                    );
                }
            }

            $dataPointsExpenses = [];
            $total_sum_expenses = 0;

            foreach ($results_expenses as $row) {
                $total_sum_expenses += $row['kwota_wydatku'];
            }

            foreach ($results_expenses as $row) {
                if ($total_sum_expenses > 0) {
                    $dataPointsExpenses[] = array(
                        "label" => $row['kategoria_wydatku'],
                        "y" => ($row['kwota_wydatku'] / $total_sum_expenses) * 100
                    );
                }
            }

    } 
    }   catch(PDOException $error) {
        echo '<span style="color: red">Błąd serwera! Przepraszamy za niedogodności i zapraszamy do wizyty w innym terminie!</span>';
        echo $error->getMessage();
      }

    
	
?>


<!DOCTYPE html>

<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Przeglądaj bilans</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <script src="./js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="./style_przegladaj_bilans.css">
    <link rel="stylesheet" href="./css/cap.css">


    <script>

        window.onload = function() {
        
            var chartIncomes = new CanvasJS.Chart("pie-chart-incomes-container", {
                animationEnabled: true,
                backgroundColor: "rgba(155, 224, 224, 0.5)",

                title: {
                    text: "Przychody"
                },

                data: [{
                    type: "pie",
                    yValueFormatString: "#,##0.00\"%\"",
                    indexLabel: "{label} ({y})",
                    dataPoints: <?php echo json_encode($dataPointsIncomes, JSON_NUMERIC_CHECK); ?>
                }]
            });
            chartIncomes.render();
    
    
            var chartExpenses = new CanvasJS.Chart("pie-chart-expenses-container", {
                animationEnabled: true,
                backgroundColor: "rgba(155, 224, 224, 0.5)",

                title: {
                    text: "Wydatki"
                },

                data: [{
                    type: "pie",
                    yValueFormatString: "#,##0.00\"%\"",
                    indexLabel: "{label} ({y})",
                    dataPoints: <?php echo json_encode($dataPointsExpenses, JSON_NUMERIC_CHECK); ?>
                }]
            });
            chartExpenses.render();
            
            }

        document.addEventListener("DOMContentLoaded", function () {
            const resultsIncomes = <?php echo json_encode($results_incomes); ?>;
            const resultsExpenses = <?php echo json_encode($results_expenses); ?>;
            const chartExpensesDiv = document.getElementById("pie-chart-expenses-container");
            const chartIncomesDiv = document.getElementById("pie-chart-incomes-container");
            const divTables = document.getElementsByClassName("tables-incomes-expenses")[0];
            const calculationDiv = document.getElementById("calculation");
            if((!resultsIncomes || resultsIncomes.length === 0) && (!resultsExpenses || resultsExpenses.length === 0))  {
                calculationDiv.style.display = "none";
                chartExpensesDiv.style.display = "none";
                chartIncomesDiv.style.display = "none";
                divTables.style.marginBottom = "30px";
            }
            else if(!resultsIncomes || resultsIncomes.length === 0){
                chartIncomesDiv.style.display = "none";
            }
            else if(!resultsExpenses || resultsExpenses.length === 0){
                chartExpensesDiv.style.display = "none";
            }
        });

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

        function handleTimeSlotChange(select) {
            if (select.value !== "niestandardowy") {
                select.form.submit();
            } else {
                $('#myModal').modal('show');
            }

        }


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
        <div class="container-outside">
            <div class="container-inside">
                <section>
                    <form id="form_balance" method="post">
                        <div class="check-period">
                            <div>
                                <label for="time-slot">
                                    <select id="time-slot" name="time-slot" onchange="handleTimeSlotChange(this)">
                                        <option selected disabled>Wybierz okres czasu</option>
                                        <option value="bieżący_miesiąc" <?php echo $selected_period === 'bieżący_miesiąc' ? 'selected' : ''; ?>>bieżący miesiąc</option>
                                        <option value="poprzedni_miesiąc" <?php echo $selected_period === 'poprzedni_miesiąc' ? 'selected' : ''; ?>>poprzedni miesiąc</option>
                                        <option value="bieżący_rok" <?php echo $selected_period === 'bieżący_rok' ? 'selected' : ''; ?>>bieżący rok</option>
                                        <option value="niestandardowy" <?php echo $selected_period === 'niestandardowy' ? 'selected' : ''; ?>>niestandardowy</option>
                                    </select>
                                </label>
                            </div>

                            <?php $current_day = date('Y-m-d'); ?>
                            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                    <div class="modal-body">
                                        Zakres od:
                                            <div class="input-group">
                                                <span class="icon-container"><i class="icon-calendar"></i></span>
                                                <input type="text" name="start_day" class="datepicker form-control" value="<?php echo $current_day ?>">
                                            </div>
                                        do: 
                                            <div class="input-group">
                                                <span class="icon-container"><i class="icon-calendar"></i></span>
                                                    <input type="text" name="end_day" class="datepicker form-control" value="<?php echo $current_day ?>">
                                            </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Zamknij</button>
                                        <button type="submit" class="btn btn-success">Zapisz zmiany</button>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                <section>
                    <div class="balance">
                        <div class="tables-incomes-expenses">
                            <div>
                                <h3>Przychody</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="header-category">Kategoria</th>
                                            <th class="header-amount">Kwota (zł)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (empty($results_incomes)) {
                                                echo "<tr><td colspan='2'>Brak wyników</td></tr>";
                                                $total_sum_incomes = 0;
                                            } else {
                                                $total_sum_incomes = 0;
                                                foreach ($results_incomes as $row) {
                                                    echo "<tr>
                                                        <td>{$row['kategoria_przychodu']}</td>
                                                        <td>{$row['kwota_przychodu']}</td>
                                                        </tr>";
                                                    $total_sum_incomes += $row['kwota_przychodu'];
                                                }
                                                
                                                if($how_many_categories_incomes > 1){
                                                    echo "<tr><td><b>Suma całkowita<b/></td><td>{$total_sum_incomes}</td></tr>";
                                                }
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                <h3>Wydatki</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="header-category">Kategoria</th>
                                            <th class="header-amount">Kwota (zł)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (empty($results_expenses)) {
                                                echo "<tr><td colspan='2'>Brak wyników</td></tr>";
                                                $total_sum_expenses = 0;
                                            } else {
                                                $total_sum_expenses = 0;
                                                foreach ($results_expenses as $row) {
                                                    echo "<tr>
                                                            <td>{$row['kategoria_wydatku']}</td>
                                                            <td>{$row['kwota_wydatku']}</td>                                   
                                                        </tr>";
                                                    $total_sum_expenses += $row['kwota_wydatku'];
                                                }
                                                
                                                if($how_many_categories_expenses > 1){
                                                    echo "<tr><td><b>Suma całkowita<b/></td><td>{$total_sum_expenses}</td></tr>";
                                                }
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                            <div id="pie-chart-incomes-container" style="height: 300px; width: 60%;"></div>
                            <div id="pie-chart-expenses-container" style="height: 300px; width: 60%;"></div>

                        <div id="calculation">
                                <?php
                                    $balance = $total_sum_incomes - $total_sum_expenses;
                                    $balance_sheet = $balance." zł";
                                ?>
                            <span>
                                <h3>Bilans</h3>
                                    <?php if ($balance < 0) { ?>
                                        <h3 style = "color: red"><?php echo $balance_sheet; ?></h3> 
                                        <div id="balance-negative-message"><?php echo "Uważaj, wpadasz w długi!"; ?></div>
                                    <?php } else if ($balance > 0) { ?>
                                        <h3 style = "color: green"><?php echo $balance_sheet; ?></h3> 
                                        <div id="balance-positive-message"><?php echo "Gratulacje. Świetnie zarządzasz finansami!"; ?></div>
                                    <?php } ?>
                            </span>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </article>
    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</body>

</html>