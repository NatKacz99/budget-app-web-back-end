<?php

	if((!isset($_POST['e-mail'])) || (!isset($_POST['password'])))
	{
		header('Location: index.html');
		exit();
	}

	require_once "connect.php";
	
	try{
		$connect = new mysqli($host, $db_user, $db_password, $db_name);
	
		if ($connect->connect_errno!=0)
		{
			echo "Error: ".$connect->connect_errno;
		}

		else{
			$email = $_POST['e-mail'];
			$password = $_POST['password'];

			$connect->close();
		}
  }
  catch(Exception $error){
      echo '<span style = "color: red">Błąd serwera! Przepraszamy za niedogodności i prosimy o logowanie w innym możliwym terminie.</span>';
  }