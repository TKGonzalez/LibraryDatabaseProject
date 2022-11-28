<?php
include 'main.php';
// No need for the user to see the login form if they're logged-in, so redirect them to the home page
if (isset($_SESSION['loggedin'])) {
	// If the user is not logged in, redirect to the home page.
    header('Location: home.php');
    exit;
}
// Also check if they are "remembered"
if (isset($_COOKIE['rememberme']) && !empty($_COOKIE['rememberme'])) {
	// If the remember me cookie matches one in the database then we can update the session variables and the user will be logged-in.
	$stmt = $pdo->prepare('SELECT * FROM User WHERE rememberme = ?');
	$stmt->execute([ $_COOKIE['rememberme'] ]);
	$User = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($User) {
		// Authenticate the user
		session_regenerate_id();
		$_SESSION['loggedin'] = TRUE;
		$_SESSION['fname'] = $User['Fname'];
		$_SESSION['lname'] = $User['Lname'];
		$_SESSION['id'] = $User['PSID'];
        $_SESSION['role'] = $User['role'];
		$_SESSION['email'] = $User['Email'];
		// Update last seen date
		$date = date('Y-m-d\TH:i:s');
		$stmt = $pdo->prepare('UPDATE User SET last_seen = ? WHERE PSID = ?');
		$stmt->execute([ $date, $User['PSID'] ]);
		// Redirect to home page
        header('Location: home.php');
		exit;
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Login</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body>
		<div class="login">

			<h1>Login</h1>

			<div class="links">
				<a href="index.php" class="active">Login</a>
				<a href="register.php">Register</a>
			</div>

			<form action="authenticate.php" method="post">

				<label for="psid">
					<i class="fas fa-user"></i>
				</label>
				<input type="text" name="psid" placeholder="PSID" id="psid" required>

				<label for="password">
					<i class="fas fa-lock"></i>
				</label>
				<input type="password" name="password" placeholder="Password" id="password" required>

				<!--<label id="rememberme">
					<input type="checkbox" name="rememberme">Remember me
				</label>-->

				<div class="msg"></div>

				<input type="submit" value="Login">

			</form>

		</div>

		<script>
		// AJAX code
		let loginForm = document.querySelector(".login form");
		loginForm.onsubmit = event => {
			event.preventDefault();
			fetch(loginForm.action, { method: 'POST', body: new FormData(loginForm) }).then(response => response.text()).then(result => {
				if (result.toLowerCase().includes("success")) {
					window.location.href = "home.php";
				} else {
					document.querySelector(".msg").innerHTML = result;
				}
			});
		};
		</script>
	</body>
</html>