<?php

session_start();

if ((!isset($_POST['e-mail'])) || (!isset($_POST['password']))) {
    header('Location: index.html');
    exit();
}

require_once "connect.php";

try {
    $connect = new mysqli($host, $db_user, $db_password, $db_name);

    if ($connect->connect_errno != 0) {
        echo "Error: " . $connect->connect_errno;
    } else {
        $email = $_POST['e-mail'];
        $password = $_POST['password'];

        $email = htmlentities($email, ENT_QUOTES, "UTF-8");
        $password = htmlentities($password, ENT_QUOTES, "UTF-8");

        if ($result = $connect->query(
            sprintf("SELECT * FROM users WHERE email='%s'",
            mysqli_real_escape_string($connect, $email)
            )
        )) {
            $how_many_users = $result->num_rows;

            if ($how_many_users > 0) {
                $row = $result->fetch_assoc();

                if (password_verify($password, $row['password'])) {
                    $_SESSION['logged'] = true;
                    unset($_SESSION['error_log_in']);
                    $result->free_result();
                    header('Location: menu_glowne.html');
                    exit();
                } else {
                    $_SESSION['error_log_in'] = '<span style="color: red">Nieprawidłowy e-mail lub hasło!</span>';
                    header('Location: strona_logowania.php');
                    exit();
                }
            } else {
                $_SESSION['error_log_in'] = '<span style="color: red">Nie ma użytkownika o podanym adresie e-mail!</span>';
                header('Location: strona_logowania.php');
                exit();
            }
        } else {
            throw new Exception($connect->error);
        }

        $connect->close();
    }
} catch (Exception $error) {
    echo '<span style = "color: red">Błąd serwera! Przepraszamy za niedogodności i prosimy o logowanie w innym możliwym terminie.</span>';
}
