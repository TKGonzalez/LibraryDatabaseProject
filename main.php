<?php
// The main file contains the database connection, session initializing, and functions, other PHP files will depend on this file.
// Include the configuration file
include_once 'config.php';
// We need to use sessions, so you should always start sessions using the below code.
session_start();

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

// Connect to the MySQL database using the PDO interface
try {
	$pdo = new PDO('mysql:host=' . db_host . ';dbname=' . db_name . ';charset=' . db_charset, db_user, db_pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
	// If there is an error with the connection, stop the script and display the error.
	exit('Failed to connect to database!');
}
// The below function will check if the user is logged-in and also check the remember me cookie
function check_loggedin($pdo, $redirect_file = 'index.php') {
	// If you want to update the "last seen" column on every page load, you can uncomment the below code
	/*
	if (isset($_SESSION['loggedin'])) {
		$date = date('Y-m-d\TH:i:s');
		$stmt = $pdo->prepare('UPDATE accounts SET last_seen = ? WHERE id = ?');
		$stmt->execute([ $date, $_SESSION['id'] ]);
	}
	*/
	// Check for remember me cookie variable and loggedin session variable
    if (isset($_COOKIE['rememberme']) && !empty($_COOKIE['rememberme']) && !isset($_SESSION['loggedin'])) {
    	// If the remember me cookie matches one in the database then we can update the session variables.
    	$stmt = $pdo->prepare('SELECT * FROM User WHERE rememberme = ?');
    	$stmt->execute([ $_COOKIE['rememberme'] ]);
    	$User = $stmt->fetch(PDO::FETCH_ASSOC);
		// If account exists...
    	if ($User) {
    		// Found a match, update the session variables and keep the user logged-in
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
    	} else {
    		// If the user is not remembered redirect to the login page.
    		header('Location: ' . $redirect_file);
    		exit;
    	}
    } else if (!isset($_SESSION['loggedin'])) {
    	// If the user is not logged in redirect to the login page.
    	header('Location: ' . $redirect_file);
    	exit;
    }
}

/*
// Send activation email function
function send_activation_email($email, $code) {
	// Email Subject
	$subject = 'Account Activation Required';
	// Email Headers
	$headers = 'From: ' . mail_from . "\r\n" . 'Reply-To: ' . mail_from . "\r\n" . 'Return-Path: ' . mail_from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
	// Activation link
	$activate_link = activation_link . '?email=' . $email . '&code=' . $code;
	// Read the template contents and replace the "%link" placeholder with the above variable
	$email_template = str_replace('%link%', $activate_link, file_get_contents('activation-email-template.html'));
	// Send email to user
	mail($email, $subject, $email_template, $headers);
}
*/

// Send activation email function - PHPMailer
function send_activation_email($email, $code) 
{
	$mail = new PHPMailer(true);

	try 
	{
		//Server settings
		//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
		$mail->isSMTP();                                            //Send using SMTP
		$mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
		$mail->Username   = 'umalibraryservices@gmail.com';         //SMTP username
		$mail->Password   = 'kusqmtgegpstpdeg';                     //SMTP password
		//$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;          //Enable implicit TLS encryption
		$mail->SMTPSecure = 'ssl'; 
		$mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

		//Recipients
		$mail->setFrom('noreply@librarydb.tk', 'RamamurthyLibrary');
		$mail->addAddress($email);     //Add a recipient

		//Content
		$mail->isHTML(true);                                  //Set email format to HTML

		// Activation link
		$activate_link = activation_link . '?email=' . $email . '&code=' . $code;
		// Read the template contents and replace the "%link" placeholder with the above variable
		$email_template = str_replace('%link%', $activate_link, file_get_contents('activation-email-template.html'));

		$mail->Subject = 'Account Activation Required';
		//$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		$mail->Body = $email_template;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		$mail->send();
		echo 'Message has been sent';
	} 
	catch (Exception $e) 
	{
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}

?>