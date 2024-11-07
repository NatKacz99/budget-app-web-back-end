<?php

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

    <title>Przeglądaj bilans</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="style_przegladaj_bilans.css">
    <link rel="stylesheet" href="./css/cap.css">

    <script>
        $(function () {
            $(".menu-mobile__collapsible").on("click", function () {
                let $menuList = $(".menu-mobile__list");

                if ($menuList.hasClass("hidden")) {
                    $menuList.removeClass("hidden");
                } else {
                    $menuList.addClass("hidden");
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            showPieChartIncomings();
            showPieChartExpenses();
        });

        function showPieChartIncomings() {
            let sectionA = { size: 6000, color: "pink" };
            let sectionB = { size: 100, color: "yellow" };

            const values = [sectionA.size, sectionB.size];
            const total = values.reduce((acc, val) => acc + val, 0);

            let startAngle = 0;
            const canvas = document.getElementById("pie-chart-incomings");
            const ctx = canvas.getContext("2d");

            values.forEach((value, index) => {
                const angle = (value / total) * Math.PI * 2;

                ctx.beginPath();
                ctx.moveTo(canvas.width / 2, canvas.height / 2);
                ctx.arc(canvas.width / 2, canvas.height / 2, canvas.width / 2, startAngle, startAngle + angle);
                ctx.closePath();

                ctx.fillStyle = index === 0 ? sectionA.color : sectionB.color;
                ctx.fill();

                startAngle += angle;
            });

            const legend = document.getElementById("pie-chart-legend__incomings");
            legend.innerHTML = `
        <div class="legend-item">
            <div class="legend-color" style="background-color:${sectionA.color}"></div>
            <div class="legend-label">wynagrodzenie: ${((sectionA.size / total) * 100).toFixed(2)} %</div>
        </div> 
        <div class="legend-item">
            <div class="legend-color" style="background-color:${sectionB.color}"></div>
            <div class="legend-label">sprzedaż na allegro: ${((sectionB.size / total) * 100).toFixed(2)} %</div>
        </div> 
    `;
        }

        function showPieChartExpenses() {
            let sectionA = { size: 500, color: "violet" };
            let sectionB = { size: 200, color: "gray" };
            let sectionC = { size: 400, color: "orange" };

            const values = [sectionA.size, sectionB.size, sectionC.size];
            const total = values.reduce((acc, val) => acc + val, 0);

            let startAngle = 0;
            const canvas = document.getElementById("pie-chart-expenses");
            const ctx = canvas.getContext("2d");

            values.forEach((value, index) => {
                const angle = (value / total) * Math.PI * 2;

                ctx.beginPath();
                ctx.moveTo(canvas.width / 2, canvas.height / 2);
                ctx.arc(canvas.width / 2, canvas.height / 2, canvas.width / 2, startAngle, startAngle + angle);
                ctx.closePath();

                ctx.fillStyle = index === 0 ? sectionA.color : index === 1 ? sectionB.color : sectionC.color;
                ctx.fill();

                startAngle += angle;
            });

            const legend = document.getElementById("pie-chart-legend__expenses");
            legend.innerHTML = `
        <div class="legend-item">
            <div class="legend-color" style="background-color:${sectionA.color}"></div>
            <div class="legend-label">jedzenie: ${((sectionA.size / total) * 100).toFixed(2)} %</div>
        </div> 
        <div class="legend-item">
            <div class="legend-color" style="background-color:${sectionB.color}"></div>
            <div class="legend-label">rozrywka: ${((sectionB.size / total) * 100).toFixed(2)} %</div>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color:${sectionC.color}"></div>
            <div class="legend-label">transport: ${((sectionC.size / total) * 100).toFixed(2)} %</div>
        </div>
    `;
        }

        document.addEventListener("DOMContentLoaded", function () {
            showStatementAboutBilanceScore()
        });

        function showStatementAboutBilanceScore() {
            let amountBalance = 5000;
            if (amountBalance > 0) {
                alert("Gratulacje. Świetnie zarządzasz finansami!");
            }
            else if (bilans < 0) {
                alert("Uważaj, wpadasz w długi!");
            }
        }

    </script>

</head>

<body>
    <section>
        <nav id="menu-desktop">
            <ul class="menu-desktop__list">
                <li><a href="index.html" class="main-page"><i class="icon-home"></i>Strona główna</a></li>
                <li><a href="#" class="add-incoming"><i class="icon-dollar"></i>Dodaj przychód</a></li>
                <li><a href="dodaj_wydatek.html" class="add-expense"><i class="icon-basket"></i>Dodaj wydatek</a></li>
                <li><a href="przegladaj_bilans.html" class="review-balance"><i class="icon-calc"></i>Przeglądaj bilans</a></li>
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
                <li><a href="przegladaj_bilans.html" class="review-balance"><i class="icon-calc"></i>Przeglądaj bilans</a></li>
                <li><a href="#" class="settings"><i class="icon-cog"></i>Ustawienia</a></li>
                <li><a href="wyloguj.php" class="log-out"><i class="icon-logout"></i>Wyloguj się</a></li>
            </ul>
        </nav>
    </section>

    <article>
        <div class="container-outside">
            <div class="container-inside">
                <section>
                    <form id="form_balance">
                        <div class="check-period">
                            <div>
                                <label for="time-slot">Wybierz przedział czasowy:
                                    <select id="time-slot">
                                        <option>bieżący miesiąc</option>
                                        <option>poprzedni miesiąc</option>
                                        <option>bieżący rok</option>
                                        <option id="non-standard">niestandardowy</option>
                                    </select>
                                </label>
                            </div>
                            <div class="differnet-term">
                                <label for="range">Podaj interesujący Cię zakres dat:
                                    <input type="text" id="range">
                                </label>
                                <input type="button" value="Wybierz okres">
                            </div>
                        </div>
                    </form>
                </section>

                <section>
                    <div class="balance">
                        <div class="tables-incomings-expenses">
                            <div>
                                <h3>Przychody</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="header-category">Kategoria</th>
                                            <th class="header-amount">Kwota</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="category">wynagrodzenie</td>
                                            <td>6000</td>
                                        </tr>
                                        <tr>
                                            <td class="category">sprzedaż na allegro</td>
                                            <td>100</td>
                                        </tr>
                                        <tr>
                                            <td class="sum">Suma</td>
                                            <td>6100</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                <h3>Wydatki</h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="header-category">Kategoria</th>
                                            <th class="header-amount">Kwota</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="category">jedzenie</td>
                                            <td>500</td>
                                        </tr>
                                        <tr>
                                            <td class="category">rozrywka</td>
                                            <td>200</td>
                                        </tr>
                                        <tr>
                                            <td class="category">transport</td>
                                            <td>400</td>
                                        </tr>
                                        <tr>
                                            <td class="sum">Suma</td>
                                            <td>1100</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="charts">
                            <div class="pie-chart-incomings-container">
                                <canvas id="pie-chart-incomings" width="200" height="200">
                                </canvas>
                                <ul id="pie-chart-legend__incomings"></ul>
                            </div>

                            <div class="pie-chart-expenses-container">
                                <canvas id="pie-chart-expenses" width="200" height="200">
                                </canvas>
                                <ul id="pie-chart-legend__expenses"></ul>
                            </div>
                        </div>

                        <div>
                            <h3>Bilans</h3>
                            <span>
                                <h3>5000</h3>
                            </span>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </article>
</body>

</html>