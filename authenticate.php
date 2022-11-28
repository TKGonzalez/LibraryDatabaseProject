<?php
include 'main.php';
// Now we check if the data from the login form was submitted, isset() will check if the data exists.
if (!isset($_POST['psid'], $_POST['password'])) {
	// Could not retrieve the data that should have been sent
	exit('Please fill both the PSID and password field!');
}
// Prepare our SQL query and find the account associated with the login details
// Preparing the SQL statement will prevent SQL injection
$stmt = $pdo->prepare('SELECT * FROM User WHERE PSID = ?');
$stmt->execute([ $_POST['psid'] ]);
$User = $stmt->fetch(PDO::FETCH_ASSOC);
// Check if the account exists
if ($User) {
	// Account exists... Verify the password
	if (password_verify($_POST['password'], $User['Password'])) {
		// Check if the account is activated
		if (account_activation && $User['activation_code'] != 'activated') {
			// User has not activated their account, output the message
			echo 'Please activate your account to login! Click <a href="resendactivation.php">here</a> to resend the activation email.';
		} else {
			// Verification success! User has loggedin!
			// Declare the session variables, which will basically act like cookies, but will store the data on the server as opposed to the client
			session_regenerate_id();
			$_SESSION['loggedin'] = TRUE;
			$_SESSION['fname'] = $User['Fname'];
			$_SESSION['lname'] = $User['Lname'];
			$_SESSION['email'] = $User['Email'];
			$_SESSION['id'] = $User['PSID'];
			$_SESSION['role'] = $User['role'];
			// IF the user checked the remember me checkbox...
			if (isset($_POST['rememberme'])) {
				// Generate a hash that will be stored as a cookie and in the database. It will be used to identify the user.
				$cookiehash = !empty($User['rememberme']) ? $User['rememberme'] : password_hash($User['PSID'] . $User['Fname'] . 'yoursecretkey', PASSWORD_DEFAULT);
				// The number of days a user will be remembered
				$days = 30;
				// Create the cookie
				setcookie('rememberme', $cookiehash, (int)(time()+60*60*24*$days));
				// Update the "rememberme" field in the accounts table with the new hash
				$stmt = $pdo->prepare('UPDATE User SET rememberme = ? WHERE PSID = ?');
				$stmt->execute([ $cookiehash, $User['PSID'] ]);
			}
			// Update last seen date
			$date = date('Y-m-d\TH:i:s');
			$stmt = $pdo->prepare('UPDATE User SET last_seen = ? WHERE PSID = ?');
			$stmt->execute([ $date, $User['PSID'] ]);
			// Output msg; do not change this line as the AJAX code depends on it
			echo 'Success';
		}
	} else {
		// Incorrect password
		// echo 'Incorrect Username and/or Password!';
		echo 'Incorrect Password!';
		// echo $_POST['password'];
		// echo $User['Password'];
	}
} else {
	// Incorrect PSID
	echo 'Incorrect PSID!';
}
?>