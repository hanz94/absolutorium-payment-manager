<?php 	require_once 'db-connect.php';
		include 'functions.php'; 
?>

<!DOCTYPE html>

<html lang="pl-PL">
    <head>
		<meta charset="UTF-8">
        <meta name="robots" content="noindex, nofollow">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="author" content="Koło Naukowe Studentów Anglistyki KUL">
		<title><?php Value('institution-name.php');?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,300;0,600;1,300;1,600&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="styles.css">
		<script src="js/jquery.min.js" defer></script>
		<script src="js/script-new.min.js" defer></script>
    </head>

    <body>
		<aside>
			<div class="modal-container" id="modal-con">
				<div class="modal-window">
					<div class="modal-header">knsa.pl</div>
					<div class="modal-text" id="modal-text"></div>
						<div class="modal-form-container">
							<form method="get" id="modal-form">
								<input type="text" name="uID" id="modal-input-text" style="text-transform:uppercase;" autocomplete="off" autofocus required>
							</form>
						</div>
							<div class="modal-button-container">
								<button class="action-button modal-button-accept" id="modal-accept">Akceptuj</button>
								<button class="action-button modal-button-decline" id="modal-decline">Anuluj</button>
							</div>
				<img src="x_icon.png" class="modal-close-icon" id="modal-close" alt="" width="16" height="16">
				<img src="kotek.png" alt="" class="modal-cat" width="64" height="64">
				</div>
			</div>
		</aside>
		<?php 
		//php error display
		ini_set('display_errors', '1');
		ini_set('display_startup_errors', '1');
		error_reporting(E_ALL);

		//this value should be changed only if database is empty

		$session_opening_time = file_get_contents('session-opening-time.php');
		$session_closing_time = file_get_contents('session-closing-time.php');
		$current_time = time();
		
		if ($session_opening_time != '0' && $session_closing_time != '0') {
			if ($session_opening_time < $current_time && $session_closing_time > $current_time) {
				file_put_contents('session-status.php', 1);
			}
			else {
				file_put_contents('session-status.php', 0);
			}
		}
		
		$session = file_get_contents('session-status.php');
		

		
		if ($session) {
			
			if (isset($_GET['uID']) && $_GET['uID'] != null) {
				$uid_input = test_input($_GET['uID']);
				
				//security check - uID must contain only numbers and letters
				if (!preg_match("#^[a-zA-Z0-9]+$#", $uid_input)) {
				    file_put_contents('security.log', '[' . date("Y-m-d h:i:s A") . '] SECURITY Issue: Incorrect uID input (characters), IP: ' . $_SERVER['REMOTE_ADDR'] . ' uID: ' . $_GET['uID'] . PHP_EOL, FILE_APPEND);
					echo '<script>
						window.addEventListener("load", function() {
							showModalLocate("Podany kod nie spełnia wymagań.<br>Skontaktuj się z organizatorem.", "?");
						});
						</script>';
					die();
				}
				
				//security check - uid must contain uid_length number of characters
				if (strlen($uid_input) != $uid_length && $uid_input != 'new') {
				    file_put_contents('security.log', '[' . date("Y-m-d h:i:s A") . '] SECURITY Issue: Incorrect uID input (length), IP: ' . $_SERVER['REMOTE_ADDR'] . ' uID: ' . $_GET['uID'] . PHP_EOL, FILE_APPEND);
					echo '<script>
						window.addEventListener("load", function() {
							showModalLocate("Podany kod nie spełnia wymagań.<br>Skontaktuj się z organizatorem.", "?");
						});
						</script>';
					die();
				}

					//check if uID exists in database
					$query_check_uid = db_do_query_return_obj('SELECT * FROM uid_db WHERE uid = "' . $uid_input . '";');
					$check_uid_result = $query_check_uid->num_rows;
					
					if ($check_uid_result > 0) {
						$uid_exists = true;
					}
					else {
						$uid_exists = false;
					}

				if($_GET['uID'] === 'new') {
					InputNameSurname();
				}
				else if ($uid_exists) {
					StudentEnrollmentForm();
				}
				else {
					echo '
						<script>
						window.addEventListener("load", function() {
							showModalLocate("Podany kod nie istnieje.<br>Skontaktuj się z organizatorem.", "?");
						});
						</script>';
				}
			}
			else {
				MainIndex();
			}
		}
		else {
			SessionClosed();
		}
		?>
    </body>
</html>