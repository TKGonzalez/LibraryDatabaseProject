<?php
include 'main.php';
// Check logged-in
check_loggedin($pdo);
// output message (errors, etc)
$msg = '';
// Retrieve additional account info from the database because we don't have them stored in sessions
$stmt = $pdo->prepare('SELECT * FROM User WHERE PSID = ?');
// In this case, we can use the account ID to retrieve the account info.
$stmt->execute([ $_SESSION['id'] ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
// Handle edit profile post data
//if (isset($_POST['username'], $_POST['password'], $_POST['cpassword'], $_POST['email'])) {
if (isset($_POST['fname'], $_POST['lname'], $_POST['password'], $_POST['cpassword'], $_POST['email'])) {
	// Make sure the submitted registration values are not empty.
	if (empty($_POST['fname']) || empty($_POST['lname']) || empty($_POST['email'])) {
		$msg = 'Values must not be empty!';
	} else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$msg = 'Please provide a valid email address!';
	} else if (!preg_match('/^[a-zA-Z]+$/', $_POST['fname'])) {
	    $msg = 'First name must contain only letters!';
	} else if (!preg_match('/^[a-zA-Z]+$/', $_POST['fname'])) {
		$msg = 'Last name must contain only letters!';
	}else if (!empty($_POST['password']) && (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5)) {
		$msg = 'Password must be between 5 and 20 characters long!';
	} else if ($_POST['cpassword'] != $_POST['password']) {
		$msg = 'Passwords do not match!';
	}
	// No validation errors... Process update
	if (empty($msg)) {
		// Check if new username or email already exists in database
		$stmt = $pdo->prepare('SELECT COUNT(*) FROM User WHERE (PSID = ? OR Email = ?) AND PSID != ? AND Email != ?');
		$stmt->execute([ $_POST['psid'], $_POST['email'], $_SESSION['id'], $User['Email'] ]);
		// Account exists? Output error...
		if ($result = $stmt->fetchColumn()) {
			$msg = 'Account already exists with that username and/or email!';
		} else {
			// No errors occured, update the account...
			// If email has changed, generate a new activation code
			$uniqid = account_activation && $User['Email'] != $_POST['email'] ? uniqid() : $User['activation_code'];
			//$stmt = $pdo->prepare('UPDATE User SET Fname = ?, Lname = ?, PSID = ?, Password = ?, Email = ?, activation_code = ? WHERE PSID = ?');
			// We do not want to expose passwords in our database, so hash the password and use password_verify when a user logs in.
			$password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $User['Password'];
			if (empty($_POST['password']))
			{
				if ($account['Email'] == $_POST['email'])
				{
					$stmt = $pdo->prepare('UPDATE User SET Fname = ?, Lname = ?, Email = ?, activation_code = ? WHERE PSID = ?');
					$stmt->execute([  $_POST['fname'], $_POST['lname'], $_POST['email'], "activated", $_SESSION['id'] ]);
				}
				else
				{
					$stmt = $pdo->prepare('UPDATE User SET Fname = ?, Lname = ?, Email = ?, activation_code = ? WHERE PSID = ?');
					$stmt->execute([  $_POST['fname'], $_POST['lname'], $_POST['email'], $uniqid, $_SESSION['id'] ]);
				}
			}
			else if ($account['Email'] == $_POST['email'])
			{
				$stmt = $pdo->prepare('UPDATE User SET Fname = ?, Lname = ?, Password = ?, Email = ?, activation_code = ? WHERE PSID = ?');
				$stmt->execute([  $_POST['fname'], $_POST['lname'], $password, $_POST['email'], "activated", $_SESSION['id'] ]);
			}
			else 
			{
				$stmt = $pdo->prepare('UPDATE User SET Fname = ?, Lname = ?, Password = ?, Email = ?, activation_code = ? WHERE PSID = ?');
				$stmt->execute([  $_POST['fname'], $_POST['lname'], $password, $_POST['email'], $uniqid, $_SESSION['id'] ]);
			}
			// Update the session variables
			$_SESSION['fname'] = $_POST['fname'];
			$_SESSION['lname'] = $_POST['lname'];
			$_SESSION['email'] = $_POST['email'];

			if(!account_activation)
			{
				$stmt = $pdo->prepare('UPDATE User SET activation_code = ? WHERE PSID = ?');
				$stmt->execute([ 'activated', $_SESSION['id'] ]);
			}
			
			if (account_activation && $account['Email'] != $_POST['email']) {
				// Account activation required, send the user the activation email with the "send_activation_email" function from the "main.php" file
				send_activation_email($_POST['email'], $uniqid);
				// Logout the user
				unset($_SESSION['loggedin']);
				$msg = 'You have changed your email address! You need to re-activate your account!';
			} else {
				// Profile updated successfully, redirect the user back to the profile page
				header('Location: profile.php');
				exit;
			}
		}
	}
}
//$msg = 'isset() failed!';

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>User Reports Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>RamamurthyLibrary</h1>
				<a href="home.php"><i class="fas fa-home"></i>Home</a>
				<a href="trending.php"><i class="fas fa-poll"></i>Trending</a>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
				<a href="user-report.php"><i class="fas fa-file"></i>User Reports</a>
				
				<?php if ($_SESSION['role'] == 'Admin'): ?>
				<a href="admin/index.php" target="_blank"><i class="fas fa-user-cog"></i>Admin</a>
				<?php endif; ?>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<?php if (!isset($_GET['action'])): ?>
		<div class="content profile">

			<h2>Profile Page</h2>

			<div class="block">

				<p>Your account details are below.</p>

				<div class="profile-detail">
					<strong>First Name</strong>
					<?=$_SESSION['fname']?>
				</div>

				<div class="profile-detail">
					<strong>Last Name</strong>
					<?=$_SESSION['lname']?>
				</div>

				<div class="profile-detail">
					<strong>PSID</strong>
					<?=$_SESSION['id']?>
				</div>

				<div class="profile-detail">
					<strong>Email</strong>
					<?=$account['Email']?>
				</div>

				<div class="profile-detail">
					<strong>Role</strong>
					<?=$account['role']?>
				</div>

				<div class="profile-detail">
					<strong>Registered</strong>
					<?=$account['registered']?>
				</div>

				<a class="profile-btn" href="profile.php?action=edit">Edit Details</a>

			</div>

		</div>
		<?php elseif ($_GET['action'] == 'edit'): ?>
		<div class="content profile">

			<h2>Edit Profile Page</h2>

			<div class="block">

				<form action="profile.php?action=edit" method="post">

					<label for="fname">First Name</label>
					<input type="text" value="<?=$_SESSION['fname']?>" name="fname" id="fname" placeholder="First Name">

					<label for="lname">Last Name</label>
					<input type="text" value="<?=$_SESSION['lname']?>" name="lname" id="lname" placeholder="Last Name">

					<label for="password">New Password</label>
					<input type="password" name="password" id="password" placeholder="New Password">

					<label for="cpassword">Confirm Password</label>
					<input type="password" name="cpassword" id="cpassword" placeholder="Confirm Password">

					<label for="email">Email</label>
					<input type="email" value="<?=$_SESSION['email']?>" name="email" id="email" placeholder="Email">

					<div>
						<input class="profile-btn" type="submit" value="Save">
					</div>

					<p><?=$msg?></p>

				</form>

			</div>

		</div>
		<?php endif; ?>
	</body>
</html>