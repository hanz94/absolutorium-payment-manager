<?php 	require_once '../db-connect.php';
		include '../functions.php';
		$uid_db = unserialize(file_get_contents('../.htuiddb'));
		$session = file_get_contents('../session-status.php');
?>

<!DOCTYPE html>

<html lang="pl-PL">
    <head>
		<meta charset="UTF-8">
        <meta name="robots" content="noindex, nofollow" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="author" content="Koło Naukowe Studentów Anglistyki KUL">
		<title><?php Value('../institution-name.php');?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,300;0,600;1,300;1,600&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="../styles.css">
		<link rel="stylesheet" href="wyniki.css">
		<script src="../js/HackTimer.min.js" defer></script>
		<script src="../js/jquery.min.js" defer></script>
		<script src="../js/script-new.min.js" defer></script>
    </head>

    <body>
		<?php
		//php error display
		ini_set('display_errors', '1');
		ini_set('display_startup_errors', '1');
		error_reporting(E_ALL);

			EnrollmentResults();
		?>
    </body>
</html>