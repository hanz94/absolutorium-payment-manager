<?php
			//Variables

			$uid_length = 6;

			$participation_price = 15;
			$toga_price = 5;
			$biret_price = 16;

			//Functions

			function Value($path) {
				echo file_get_contents($path);
			}
			
			function test_input($input) {
				$input = trim($input);
				$input = stripslashes($input);
				$input = htmlspecialchars($input);
				return $input;
			}

			function db_do_query_return_obj($q) {
				global $db_host, $db_user, $db_pass, $db_name;

				$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
				$conn->set_charset("utf8");
				if ($conn->connect_error) { die('Connection failed: ' . $conn->connect_error);}

				$query_result = $conn->query($q);

				$conn->close();

				return $query_result;

			}

			function db_send($new_uid, $new_name_surname_value, $registration_toga, $registration_biret) {

				global $db_host, $db_user, $db_pass, $db_name, $current_time;

				$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
				$conn->set_charset("utf8");
				if ($conn->connect_error) { die('Connection failed: ' . $conn->connect_error);}

				$stmt = $conn->prepare('INSERT INTO `uid_db` (`id`, `uid`, `name_surname`, `registration_time`, `registration_participation`, `registration_toga`, `registration_biret`, `payment_participation`, `payment_toga`,`payment_biret`) VALUES (NULL, ?, ?, ?, 1, ?, ?, "0", "0", "0");');
				$stmt->bind_param("ssiii", $new_uid, $new_name_surname_value, $current_time, $registration_toga, $registration_biret);
				$stmt->execute();
				$stmt->close();
				$conn->close();
			}
			
			function SessionClosedMessage() {
				$session_auto_control = file_get_contents('session-auto-control.php');
				$session_opening_time = file_get_contents('session-opening-time.php');
				$session_closing_time = file_get_contents('session-closing-time.php');
				$current_time = time();
						if ($session_auto_control) {
							if ($session_opening_time >= $current_time && $session_opening_time < $session_closing_time) {
								echo '<p>Formularz zapisów nie jest jeszcze dostępny!</p>
								<br>
								<p>Aktualny czas: <b><span id="current-time">' . date('d M Y H:i:s', $current_time) . '</span></b></p>
								<br>
								<p>Formularz otwiera się:</p>
								<br>
								<p><b>' . date('d M Y H:i:s', $session_opening_time) . '</b></p>
								<br>
								<button style="margin-top:unset;" class="action-button enrollment-submit-button" onclick="location.reload();">Odśwież stronę</button>';
							}
							else if ($session_closing_time <= $current_time && $session_opening_time < $session_closing_time) {
								echo '<p>Formularz zapisów jest niedostępny!</p>
								<br>
								<p>Aktualny czas: <b><span id="current-time">' . date('d M Y H:i:s', $current_time) . '</span></b></p>
								<br>
								<p>Formularz został zamknięty:</p>
								<br>
								<p>' . date('d M Y H:i:s', $session_closing_time) . '</p>
								<button style="margin-top:25px;" class="action-button enrollment-submit-button" onclick="location.href=\'wyniki\';">Wyniki</button>';
							}
							else if ($session_opening_time > $session_closing_time) {
								echo '<p>Formularz zapisów jest niedostępny!</p>';
							}
						}
						else {
								echo '<p>Formularz zapisów jest niedostępny!</p>';
							}
			}
			
			function SessionClosed() {
					echo '<main>
			
				<header class="container-small knsa-kul">
					<img src="knsa-logo.png" alt="" class="img-knsa">
					<img src="kul.jpg" alt="" class="img-kul">
					<div>'; Value('institution-name.php'); echo '</div>
				</header>
				
					<div class="flag"></div>
				
				<section class="container-small container-event-name-date">
					<div class="event-name">'; Value('event-name.php'); echo '</div>
				</section>
				
				<section class="container-small result-form">
					<div>'; SessionClosedMessage(); echo '</div>
				</section>

				</main>';
				
			}
			
			function createRandomUID($length) {
				//min length: 6   Format 0XX0X0 (XX...)
				$characters = 'ABCDEFGHIJKLMNOPRSTUWVXYZ';
				$randomUID = rand(0,9);
				$randomUID .= $characters[rand(0, strlen($characters) - 1)];
				$randomUID .= $characters[rand(0, strlen($characters) - 1)];
				$randomUID .= rand(0,9);
				$randomUID .= $characters[rand(0, strlen($characters) - 1)];
				$randomUID .= rand(0,9);
				for ($a = 0; $a < $length - 6; $a++) {
					$randomUID .= $characters[rand(0, strlen($characters) - 1)];
				}
				return $randomUID;
			}
			
			function MainIndex() {
				$session_auto_control = file_get_contents('session-auto-control.php');
				$session_opening_time = file_get_contents('session-opening-time.php');
				$session_closing_time = file_get_contents('session-closing-time.php');
				$current_time = time();
				
				echo '<main>
			
				<header class="container-small knsa-kul">
					<img src="knsa-logo.png" alt="" class="img-knsa">
					<img src="kul.jpg" alt="" class="img-kul">
					<div>'; Value('institution-name.php'); echo '</div>
				</header>
				
					<div class="flag"></div>
				
				<section class="container-small container-event-name-date">
					<div class="event-name">'; Value('event-name.php'); echo '</div>
				</section>
				

				<nav id="input-solution" class="container-small result-form">
				
					<span id="enrollment-begin-button-container"><button class="action-button" id="btn-nav-begin">Wypełnij formularz</button></span>
					
					<button class="action-button" id="btn-nav-changes">Sprawdź status</button>
					
					<button class="action-button" id="btn-nav-results">Lista uczestników</button>
					
				</nav>

			</main>';

			}
			
			function InputNameSurname() {
				global $db_host, $db_user, $db_pass, $db_name, $uid_length, $current_time, $participation_price, $toga_price, $biret_price;
				
					echo '<main>
			
				<header class="container-small knsa-kul">
					<img src="knsa-logo.png" alt="" class="img-knsa">
					<img src="kul.jpg" alt="" class="img-kul">
					<div>'; Value('institution-name.php'); echo '</div>
				</header>
				
					<div class="flag"></div>
				
				<section class="container-small container-event-name-date">
					<div class="event-name">'; Value('event-name.php'); echo '</div>
				</section>

				<section id="input-solution" class="container-small result-form">
				
				<label for="name-surname-textarea">
					<p>Imię i nazwisko:</p><br />
				</label>
					<form method="post" class="name-surname-form">
						<input type="text" id="name-surname-textarea" name="name_surname_value" autocomplete="off" autofocus required>
							<div id="radio-participation-container">
								<div class="radio-participation">
									<input type="radio" id="participation-deny" name="participation" value="0" checked><label for="participation-deny">Nie biorę udziału w absolutorium</label>
								</div>
								<div class="radio-participation">
									<input type="radio" id="participation-accept" name="participation" value="1"><label for="participation-accept">Biorę udział w absolutorium (+' . $participation_price . ' zł)</label>
								</div>
							</div>

							<div id="checkbox-participation-container">
								<div class="checkbox-toga">
									<input type="checkbox" id="checkbox-toga" name="toga" value="1"><label for="checkbox-toga">Chcę wypożyczyć togę (+' . $toga_price . ' zł)</label>
								</div>
								<div class="checkbox-biret">
									<input type="checkbox" id="checkbox-biret" name="biret" value="1"><label for="checkbox-biret">Chcę kupić biret (+' . $biret_price . ' zł)</label>
								</div>
							</div>
						<button class="next-button">DALEJ</button>
					</form>
					
				</section>

			</main>';
			
				if (isset($_POST['name_surname_value']) && $_POST['name_surname_value'] != null && isset($_POST['participation']) && $_POST['participation'] == '1') {
					$new_name_surname_value = test_input($_POST['name_surname_value']);

					if (isset($_POST['toga']) && $_POST['toga'] == '1') {
						$registration_toga = '1';
					}
					else {
						$registration_toga = '0';
					}

					if (isset($_POST['biret']) && $_POST['biret'] == '1') {
						$registration_biret = '1';
					}
					else {
						$registration_biret = '0';
					}
					
					$new_uid_exists = true;
					
					while ($new_uid_exists) {
						//create new uID
						$new_uid = createRandomUID($uid_length);
						
						//check if new_uid has not been already registered in database
						$query = db_do_query_return_obj('SELECT uid FROM uid_db WHERE uid = "' . $new_uid . '";');

						$uid_found = $query->num_rows;
					
							if ($uid_found > 0) {
								$new_uid_exists = true;
							}
							else {
								$new_uid_exists = false;
							}
					}
					
						//check if name_surname has not been already registered in database
						$query = db_do_query_return_obj('SELECT name_surname FROM uid_db WHERE name_surname = "' . $new_name_surname_value . '";');
						
						$ns_found = $query->num_rows;
						
						if ($ns_found > 0) {
							//name_surname found in the database
							echo '<script> 
							window.addEventListener("load", function() {
								showModalLocate("Podane imię i nazwisko już istnieje.<br>Wprowadź inną wartość lub skontaktuj się z organizatorem.", "?uID=new");
							});
							</script>';
							file_put_contents('security.log', '[' . date("Y-m-d h:i:s A") . '] Issue: Name and surname already exists, IP: ' . $_SERVER['REMOTE_ADDR'] . ' Name and surname value: ' . $new_name_surname_value . PHP_EOL, FILE_APPEND);
						}
						else {
							//name_surname not found
							db_send($new_uid, $new_name_surname_value, $registration_toga, $registration_biret);
							echo '<script>window.location.replace("?uID=' . $new_uid . '")</script>';
						}
				}
		
				
			}
			
			function StudentEnrollmentForm() {
				
				$courses = unserialize(file_get_contents('courses-db.php'));
				$limits = unserialize(file_get_contents('limits.php'));
				global $db_host, $db_user, $db_pass, $db_name, $participation_price, $toga_price, $biret_price;
							
				$uid_input = test_input($_GET['uID']);

					//get name_surname where uID = $uid_input
					$query_check_ns = db_do_query_return_obj('SELECT name_surname FROM uid_db WHERE uid = "' . $uid_input . '";');

				$uid_input = strtoupper($uid_input);

					$name_surname = $query_check_ns->fetch_row();
					$name_surname = $name_surname[0];
				
				
				echo '<main>
			
				<header class="container-small knsa-kul">
					<img src="knsa-logo.png" alt="" class="img-knsa">
					<img src="kul.jpg" alt="" class="img-kul">
					<div>'; Value('institution-name.php'); echo '</div>
				</header>
				
					<div class="flag"></div>
				
				<section class="container-small container-event-name-date">
					<div class="event-name">'; Value('event-name.php'); echo '</div>
				</section>

				<section id="input-solution" class="container-small result-form">
					<div class="enrollment-name-surname-details">
							<p>Zapisujesz się jako</p>
							<p>' . $name_surname . '</p>
						</div>
						<div class="enrollment-uid-details">
							<p><b>Kod: ' . $uid_input . '</b></p>
							<p><i>Zapamiętaj kod, aby dokonać późniejszych zmian!</i></p><br>
								<details open>
									<summary><span style="cursor:pointer;">Więcej opcji</span></summary>
										<ul>
											<li style="margin-top:10px;list-style-type:none;" id="cookie-button"></li>
											<script>var currentUID ="' . $uid_input . '";var nameSurname ="' . $name_surname . '";</script>
										</ul>
								</details>

								<details style="margin-top:20px;">
									<summary><span class="pointer">Zmień wybór</span></summary>
										<ul>
											<li style="margin-top:10px;list-style-type:none;" id="toga-button">';
												
												$toga_declaration_status = db_do_query_return_obj('SELECT registration_toga FROM uid_db WHERE uid = "' . $uid_input . '";');
												$received_toga_declaration_status = $toga_declaration_status->fetch_row();
												$received_toga_declaration_status = $received_toga_declaration_status[0];

													echo '<button class="action-button enrollment-submit-button" id="'; echo ($received_toga_declaration_status) ? 'btn-toga-resign':'btn-toga-declare'; echo '">';

													echo ($received_toga_declaration_status) ? 'Rezygnuję z wypożyczenia togi':'Chcę wypożyczyć togę';

													echo '</button>';
											
											echo '</li>
											<li style="margin-top:10px;list-style-type:none;" id="biret-button">';
												
												$biret_declaration_status = db_do_query_return_obj('SELECT registration_biret FROM uid_db WHERE uid = "' . $uid_input . '";');
												$received_biret_declaration_status = $biret_declaration_status->fetch_row();
												$received_biret_declaration_status = $received_biret_declaration_status[0];

												echo '<button class="action-button enrollment-submit-button" id="'; echo ($received_biret_declaration_status) ? 'btn-biret-resign':'btn-biret-declare'; echo '">';

													echo ($received_biret_declaration_status) ? 'Rezygnuję z kupna biretu':'Chcę kupić biret';

													echo '</button>';

											echo '</li>
										</ul>
								</details>
						</div>
						
							<table class="enrollment-table">
								<tr>
									<th>Produkt</th>
									<th>Wybór</th>
									<th>Status płatności</th>
								</tr>';


									$payment_total_price = $participation_price;
								
									//key - product name, value - product declaration status (name of the column in the database)
									$items_to_check = array('Składka (' . $participation_price . ' zł)' => 'registration_participation', 'Toga (' . $toga_price . ' zł)' => 'registration_toga', 'Biret (' . $biret_price . ' zł)' => 'registration_biret');

									foreach ($items_to_check as $product_name => $product_declaration_status) {
										
										//get declaration status
										$query_declaration_status = db_do_query_return_obj('SELECT ' . $product_declaration_status . ' FROM uid_db WHERE uid = "' . $uid_input . '";');

										$received_declaration_status = $query_declaration_status->fetch_row();
										$received_declaration_status = $received_declaration_status[0];

										//get payment status participation składka
										$query_payment_status_participation = db_do_query_return_obj('SELECT payment_participation FROM uid_db WHERE uid = "' . $uid_input . '";');

										$received_payment_status_participation = $query_payment_status_participation->fetch_row();
										$received_payment_status_participation = $received_payment_status_participation[0];

										//get payment status toga
										$query_payment_status_toga = db_do_query_return_obj('SELECT payment_toga FROM uid_db WHERE uid = "' . $uid_input . '";');

										$received_payment_status_toga = $query_payment_status_toga->fetch_row();
										$received_payment_status_toga = $received_payment_status_toga[0];
	
										//get payment status biret
										$query_payment_status_biret = db_do_query_return_obj('SELECT payment_biret FROM uid_db WHERE uid = "' . $uid_input . '";');

										$received_payment_status_biret = $query_payment_status_biret->fetch_row();
										$received_payment_status_biret = $received_payment_status_biret[0];


											echo '<tr>
											<td>' . $product_name . '</td>
											<td>'; 

												if ($product_name === 'Składka (' . $participation_price . ' zł)') {
													echo 'Wybrano';
												}
												else {
													echo ($received_declaration_status) ? 'Wybrano':'Nie wybrano';
												}

											echo '</td>
											<td>';


												if ($product_name === 'Składka (' . $participation_price . ' zł)') {
													echo ($received_payment_status_participation) ? '<span class="payment-accepted">Płatność przyjęta</span>':'<span class="no-payment">Brak płatności</span>';
												}
												else if ($product_name === 'Toga (' . $toga_price . ' zł)') {

													if ($received_declaration_status) {
														//wybrano
														echo ($received_payment_status_toga) ? '<span class="payment-accepted">Płatność przyjęta</span>':'<span class="no-payment">Brak płatności</span>';
													}
													else {
														//nie wybrano
														echo 'Niewymagana';
													}	
												}
												else if ($product_name = 'Biret (' . $biret_price . ' zł)') {

													if ($received_declaration_status) {
														//wybrano
														echo ($received_payment_status_biret) ? '<span class="payment-accepted">Płatność przyjęta</span>':'<span class="no-payment">Brak płatności</span>';
													}
													else {
														//nie wybrano
														echo 'Niewymagana';
													}	
												}

											echo '</td>';

											if ($product_name === 'Toga (' . $toga_price . ' zł)' && $received_declaration_status === '1') {
												$payment_total_price += $toga_price;
											}
											if ($product_name === 'Biret (' . $biret_price . ' zł)' && $received_declaration_status === '1') {
												$payment_total_price += $biret_price;
											}
									
								}


							echo '</table>

								<div id="payment-total-price">Suma: ' . $payment_total_price . ' zł</div>
							
								<button class="action-button enrollment-submit-button" id="btn-do-payment">Przejdź do płatności</button>

									<div id="payment-details-info">
										<h4>Jak dokonać płatności?</h4><br>
										<h5>1. BLIK</h5>
										Odbiorca: Bartłomiej Pawłowski<br>
										Numer telefonu: +48 511 643 420<br>
										Tytuł przelewu: Absolutorium ' . $uid_input . '<br>
										<h5>2. Przelew tradycyjny</h5>
										Odbiorca: Bartłomiej Pawłowski<br>
										Numer rachunku bankowego:<br>
										PL24 1240 2500 1111 0010 8581 2711<br>
										Tytuł przelewu: Absolutorium ' . $uid_input . '<br>
										<h5>3. Gotówka</h5>
										Prywatna wiadomość do:
											<ul>
												<li>Maja Hordyjewicz</li>
												<li>Bartłomiej Pawłowski</li>
											</ul>
									</div>

								<button class="action-button enrollment-submit-button" onclick="location.href=\'wyniki\';">Lista uczestników</button>

					</section>

			</main>';
			}
			
			function EnrollmentResults() {
				
				$courses = unserialize(file_get_contents('../courses-db.php'));
				$limits = unserialize(file_get_contents('../limits.php'));
				$session_auto_control = file_get_contents('../session-auto-control.php');
				$session_closing_time = file_get_contents('../session-closing-time.php');
				global $db_host, $db_user, $db_pass, $db_name, $current_time, $participation_price, $toga_price, $biret_price;

				echo '<br><br>';
				
				
				echo '<main>
					
						<header class="container-small knsa-kul">
								<img src="../knsa-logo.png" alt="" class="img-knsa">
								<img src="../kul.jpg" alt="" class="img-kul">
								<div>'; Value('../institution-name.php'); echo '</div>
						</header>
							
								<div class="flag"></div>
							
						<section class="container-small container-event-name-date">
								<div class="event-name">'; Value('../event-name.php'); echo '</div>
						</section>

						<section id="input-solution" class="container-small result-form">
							
								<p>Wyniki:</p>';
								// if ($session_auto_control && $session_closing_time != 2145913200) {
								// 	echo '<div class="remaining-time"><b><p id="remaining-time-header">Do absolutorium pozostało</p><p id="remaining-time"></p></b></div>
								// 	<script>var closingTime = ' . $session_closing_time . ';</script><script src="remaining-time.js"></script>';
								// }
								
								echo '<div class="remaining-time"><b><p id="remaining-time-header">Do absolutorium pozostało</p><p id="remaining-time"></p></b></div>';

								

								//TABLE - LISTA UCZESTNIKOW, participation details

									echo '<table>
									<tr>
										<th colspan="3">
											<div class="table-header-container">
												<p>Lista uczestników</p>
												<p style="font-size:0.9em;margin-top:4px;">Składka: ' . $participation_price . ' zł</p>
											</div>
										</th>
									</tr>
									<tr>
										<th style="word-break:normal;">Lp</th>
										<th>Imię i nazwisko</th>
										<th>Status płatności</th>
									</tr>';
									
									$query_results_participation = db_do_query_return_obj('SELECT name_surname, payment_participation FROM uid_db WHERE registration_participation = 1 ORDER BY id;');
									$results_participation = $query_results_participation->fetch_all(MYSQLI_ASSOC);

									
											if (empty($results_participation)) {
												echo '<tr><td colspan="3">Lista jest pusta!</td></tr>';
											}
											else {
												
												$lp = 1;
												foreach ($results_participation as $key => $value_array) {
														echo 	'<tr>
																<td>' . $lp . '</td>';

													foreach ($value_array as $key => $value) {
														
														if ($key === 'name_surname') {
															echo 	'<td>' . $value . '</td>';
														}
														else if ($key === 'payment_participation') {
															echo ($value) ? '<td class="payment-accepted">Zaakceptowana</td>':'<td class="no-payment">Brak płatności</td>';
														}
													}


													echo '</tr>';
													$lp++;
												}
											}
									echo '</table>';


									//TABLE - TOGI

									echo '<table>
									<tr>
										<th colspan="3">
											<div class="table-header-container">
												<p>Wypożyczenie togi</p>
												<p style="font-size:0.9em;margin-top:4px;">Cena: ' . $toga_price . ' zł</p>

											</div>
										</th>
									</tr>
									<tr>
										<th style="word-break:normal;">Lp</th>
										<th>Imię i nazwisko</th>
										<th>Status płatności</th>
									</tr>';
									
									$query_results_participation = db_do_query_return_obj('SELECT name_surname, payment_toga FROM uid_db WHERE registration_toga = 1 ORDER BY id;');
									$results_participation = $query_results_participation->fetch_all(MYSQLI_ASSOC);

									
											if (empty($results_participation)) {
												echo '<tr><td colspan="3">Lista jest pusta!</td></tr>';
											}
											else {
												
												$lp = 1;
												foreach ($results_participation as $key => $value_array) {
														echo 	'<tr>
																<td>' . $lp . '</td>';

													foreach ($value_array as $key => $value) {
														
														if ($key === 'name_surname') {
															echo '<td>' . $value . '</td>';
														}
														else if ($key === 'payment_toga') {
															echo ($value) ? '<td class="payment-accepted">Zaakceptowana</td>':'<td class="no-payment">Brak płatności</td>';
														}
													}


													echo '</tr>';
													$lp++;
												}
											}
									echo '</table>';



									//TABLE - BIRET

									echo '<table>
									<tr>
										<th colspan="3">
											<div class="table-header-container">
												<p>Zakup biretu</p>
												<p style="font-size:0.9em;margin-top:4px;">Cena: ' . $biret_price . ' zł</p>
											</div>
										</th>
									</tr>
									<tr>
										<th style="word-break:normal;">Lp</th>
										<th>Imię i nazwisko</th>
										<th>Status płatności</th>
									</tr>';
									
									$query_results_participation = db_do_query_return_obj('SELECT name_surname, payment_biret FROM uid_db WHERE registration_biret = 1 ORDER BY id;');
									$results_participation = $query_results_participation->fetch_all(MYSQLI_ASSOC);

									
											if (empty($results_participation)) {
												echo '<tr><td colspan="3">Lista jest pusta!</td></tr>';
											}
											else {
												
												$lp = 1;
												foreach ($results_participation as $key => $value_array) {
														echo 	'<tr>
																<td>' . $lp . '</td>';

													foreach ($value_array as $key => $value) {
														
														if ($key === 'name_surname') {
															echo 	'<td>' . $value . '</td>';
														}
														else if ($key === 'payment_biret') {
															echo ($value) ? '<td class="payment-accepted">Zaakceptowana</td>':'<td class="no-payment">Brak płatności</td>';
														}
													}


													echo '</tr>';
													$lp++;
												}
											}
									echo '</table>';



						echo '
						<button style="margin-top:15px;" class="action-button enrollment-submit-button" id="btn-check-status-results" onclick="enrollmentChangesRedirect()">Sprawdź status</button>
						<button style="margin-top:15px;" class="action-button enrollment-submit-button" onclick="location.href=\'../\';">Powrót do menu</button>
						
						</section>

					</main>';
			}
?>