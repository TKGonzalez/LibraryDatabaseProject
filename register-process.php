<?php
include 'main.php';
// Now we check if the data was submitted, isset() function will check if the data exists.
if (!isset($_POST['fname'], $_POST['lname'], $_POST['psid'], $_POST['password'], $_POST['cpassword'], $_POST['email'])) {
	// Could not get the data that should have been sent.
	exit('Please complete the registration form!');
}
// Make sure the submitted registration values are not empty.
if (empty($_POST['fname']) || empty($_POST['lname']) || empty($_POST['psid']) || empty($_POST['password']) || empty($_POST['cpassword']) || empty($_POST['email'])) {
	// One or more values are empty.
	exit('Please complete the registration form!');
}
// First name must contain only letters
if (!preg_match('/^[a-zA-Z]+$/', $_POST['fname'])) 
{
	exit('First name must contain only letters!');
}
// Last name must contain only letters
if (!preg_match('/^[a-zA-Z]+$/', $_POST['lname'])) 
{
	exit('Last name must contain only letters!');
}
// Check to see if the email is valid.
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit('Please provide a valid email address!');
}
// PSID must contain only numbers.
if (!preg_match('/^[0-9]{7}+$/', $_POST['psid'])) {
    exit('PSID must contain only numbers, and must be seven digits long!');
}
// Password must be between 5 and 20 characters long.
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	exit('Password must be between 5 and 20 characters long!');
}
// Check if both the password and confirm password fields match
if ($_POST['cpassword'] != $_POST['password']) {
	exit('Passwords do not match!');
}
// Check if the account with that username already exists
$stmt = $pdo->prepare('SELECT * FROM User WHERE PSID = ? OR Email = ?');
$stmt->execute([ $_POST['psid'], $_POST['email'] ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
// Store the result, so we can check if the account exists in the database.
if ($account) {
	// PSID already exists
	echo 'PSID and/or email exists!';
} else {
	// PSID doesn't exist, insert new account
	// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	// Generate unique activation code
	$uniqid = account_activation ? uniqid() : 'activated';
	// Default role
	$role = 'Student';
	// Current date
	$date = date('Y-m-d\TH:i:s');
	// Prepare query; prevents SQL injection
	$stmt = $pdo->prepare('INSERT INTO User (Fname, Lname, PSID, Password, Email, activation_code, role, registered, last_seen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
	$stmt->execute([$_POST['fname'], $_POST['lname'], $_POST['psid'], $password, $_POST['email'], $uniqid, $role, $date, $date ]);
	// If account activation is required, send activation email
	if (account_activation) {
		// Account activation required, send the user the activation email with the "send_activation_email" function from the "main.php" file
		send_activation_email($_POST['email'], $uniqid);
		echo 'Please check your email to activate your account!';
	} else {
		// Automatically authenticate the user if the option is enabled
		if (auto_login_after_register) {
			// Regenerate session ID
			session_regenerate_id();
			// Declare session variables
			$_SESSION['loggedin'] = TRUE;
			$_SESSION['fname'] = $_POST['fname'];
			$_SESSION['lname'] = $_POST['lname'];
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['id'] = $pdo->lastInsertId();
			$_SESSION['role'] = $role;		
			echo 'autologin';
		} else {
			echo 'You have successfully registered! You can now login!';
		}
	}
}
?>