<?php
include 'main.php';
check_loggedin($pdo);

$stmt = $pdo->prepare('SELECT * FROM Book');
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT * FROM Transaction WHERE PSID = ? AND Active = 1');
$stmt->execute([$_SESSION['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
/*
$stmt = $pdo->prepare('SELECT * FROM Balance WHERE PSID = ?');
$stmt->execute([$_SESSION['id']]);
$balance = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (sizeof($transactions)) {

	$currentDate = date("Y-m-d");
	
	echo "<br><br>";
	var_dump($transactions);

	$fee = 0;
	foreach ($transactions as $transaction) {
		$days = dateDiffInDays($currentDate, $transaction['ExpectedReturnDate']);
		if ($days > 0) {
			$fee = $fee + $days;
		}
	}

	if (sizeof($balance)) {
		$update = "
			UPDATE Balance
			SET Owed_Amount = ?, balanceUpdateTime = ?
			WHERE PSID = ?
		";
		$stmt = $pdo->prepare($update);
		$stmt->execute([$fee, $currentDate, $_SESSION['id']]);
	} else {
		$insert = "
			INSERT INTO Balance (PSID, Owed_Amount, Funds_Available, balanceUpdateTime)
			VALUES(?, ?, ?, ?)
		";
		$stmt = $pdo->prepare($insert);
		$stmt->execute([ $_SESSION['id'], $fee, 0, $currentDate ]);
	}
} 

// $date1 - $date2
function dateDiffInDays($date1, $date2) 
{
	// Calculating the difference in timestamps
	$diff = strtotime($date1) - strtotime($date2);

	// 1 day = 24 hours
	// 24 * 60 * 60 = 86400 seconds
	return round($diff / 86400);
}
*/


if (isset($_GET['select'])) {
    if (strcmp($_POST['category'], "dvd") == 0) {
        header('Location: home-dvd.php');
    } elseif (strcmp($_POST['category'], "laptop") == 0) {
        header('Location: home-laptop.php');
    } elseif (strcmp($_POST['category'], "meetingRoom") == 0) {
        header('Location: home-room.php');
    } elseif (strcmp($_POST['category'], "book") == 0) {
        header('Location: home.php');
    }
}

if (isset($_GET['borrow'])) {
    //$stmt =  $pdo->prepare('SELECT * FROM Book WHERE ItemId = ?');
    //$stmt->execute([ $_GET['borrow'] ]);
	//$borrowBook = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

	//$stmt =  $pdo->prepare('UPDATE Book SET NumberAvailable = ? WHERE ItemId = ?');
	//$stmt->execute([$borrowBook['NumberAvailable'] - 1, $_GET['borrow'] ]);

	$stmt = $pdo->prepare('SELECT * FROM Book');
	$stmt->execute();
	$books = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $transactionId = (time()+ rand(1,1000));
	$date   = new DateTime(); //this returns the current date time
	$expDate   = new DateTime(); //this returns the current date time
	if ($_SESSION['role'] == 'Student') {
		$expDate->modify('+3 day');
	} else {
		$expDate->modify('+5 day');
	}

	try{
    $stmt = $pdo->prepare('INSERT INTO Transaction (TransactionID, TransactionType, TransactionDate, ItemID, ItemType, PSID, ExpectedReturnDate, Active) VALUES(?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$transactionId, "Borrow", $date->format('Y-m-d'), $_GET['borrow'], "BOOK", $_SESSION['id'], $expDate->format('Y-m-d'), 1]);

	//header('Location: home.php');
    //exit;
	}
	catch(Exception $error){
		$message = $error->getMessage();
		if ($message == "SQLSTATE[45000]: <<Unknown error>>: 1644 You have fees due. Cannot check out items.") {
			echo "<script type='text/javascript'>alert('You have unpaid overdue fees. Cannot check out items until all fees are paid.');</script>";
		}
		elseif ($message == "SQLSTATE[45000]: <<Unknown error>>: 1644 Maximum limit of items reached. Cannot check out more items.") {
			echo "<script type='text/javascript'>alert('Maximum limit of borrowed items reached. Cannot check out more items.');</script>";
		}
		header("Refresh:0; url=home.php");
		exit;
	}

	$stmt =  $pdo->prepare('SELECT * FROM Book WHERE ItemId = ?');
    $stmt->execute([ $_GET['borrow'] ]);
	$borrowBook = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

	$stmt =  $pdo->prepare('UPDATE Book SET NumberAvailable = ? WHERE ItemId = ?');
	$stmt->execute([$borrowBook['NumberAvailable'] - 1, $_GET['borrow'] ]);
	

	header('Location: home.php');
    exit;
}

if (isset($_GET['hold'])) {

    //$stmt =  $pdo->prepare('SELECT * FROM Book WHERE ItemId = ?');
    //$stmt->execute([ $_GET['hold'] ]);
	//$borrowBook = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

	//$stmt =  $pdo->prepare('UPDATE Book SET NumberAvailable = ? WHERE ItemId = ?');
	//$stmt->execute([$borrowBook['NumberAvailable'] - 1, $_GET['hold'] ]);

    $transactionId = (time()+ rand(1,1000));
	$startDate = new DateTime();
	$expDate   = new DateTime(); //this returns the current date time
	if ($_SESSION['role'] == 'Student') {
		$expDate->modify('+2 day');
	} else {
		$expDate->modify('+3 day');
	}

	try{
    $stmt = $pdo->prepare('INSERT INTO HoldRequest (HoldRequestId, ItemId, PSID, RequestTime, ExpirationTime, Active) VALUES(?, ?, ?, ?, ?, ?)');
    $stmt->execute([$transactionId, $_GET['hold'], $_SESSION['id'], $startDate->format('Y-m-d H:i:s'), $expDate->format('Y-m-d H:i:s'), 1]);
	}
	catch(Exception $error) {
		$message = $error->getMessage();
		if ($message == "SQLSTATE[45000]: <<Unknown error>>: 1644 You have fees due. Cannot check out items.") {
			echo "<script type='text/javascript'>alert('You have unpaid overdue fees. Cannot check out items until all fees are paid.');</script>";
		}
		elseif ($message == "SQLSTATE[45000]: <<Unknown error>>: 1644 Maximum limit of items reached. Cannot check out more items.") {
			echo "<script type='text/javascript'>alert('Maximum limit of borrowed items reached. Cannot check out more items.');</script>";
		}
		header("Refresh:0; url=home.php");
		exit;
	}

	$stmt =  $pdo->prepare('SELECT * FROM Book WHERE ItemId = ?');
    $stmt->execute([ $_GET['hold'] ]);
	$borrowBook = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

	$stmt =  $pdo->prepare('UPDATE Book SET NumberAvailable = ? WHERE ItemId = ?');
	$stmt->execute([$borrowBook['NumberAvailable'] - 1, $_GET['hold'] ]);

	header('Location: home.php');
    exit;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Home Page</title>
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
				<?php if ($_SESSION['role'] !== 'Admin'): ?>
				<a href="user-report.php"><i class="fas fa-file"></i>User Reports</a>
				<?php endif; ?>
				<?php if ($_SESSION['role'] == 'Admin'): ?>
				<a href="admin/index.php" target="_blank"><i class="fas fa-user-cog"></i>Admin</a>
				<?php endif; ?>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>
		<div class="content">

			<h2>Home Page</h2>

			<p class="block">Welcome back, <?=$_SESSION['fname']?> <?=$_SESSION['lname']?>!</p>
			
			
		</div>

		<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
		<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript">
			$(document).ready(function () {
				$('#mytable').DataTable({
					pagingType: 'full_numbers',
				});
			});
		</script>

		<div class="content">
			
			<form action="home.php?select" method="post">
				<label for="category">Item Category</label>
				<select id="category" name="category">
					<option value="book" selected>Book</option>
					<option value="dvd">DVD</option>
					<option value="laptop">Laptop</option>
					<option value="meetingRoom">Meeting Room</option>
				</select>
				<input type="submit" value="Show">
			</form>

			<div class="content-block">
				<div class="table">
					<table table id="mytable" class="display" style="width:100%">
						<thead>
							<tr>
								<td>ItemID</td>
								<td>ISBN10</td>
								<td>Title</td>
								<td>Author</td>
								<td>Publication Year</td>
								<td>Available</td>
								<td>Total</td>
								<?php 
									if ($_SESSION['role'] !== 'Admin') {
										echo "<td>Action</td>";
									}
								
								?>
							</tr>
						</thead>
						<tbody>
							<?php if (!$books): ?>
							<tr>
								<td colspan="8" style="text-align:center;">There are no books</td>
							</tr>
							<?php endif; ?>
							<?php foreach ($books as $book): ?>
							<tr>
								<td><?=$book['ItemId']?></td>
								<td><?=$book['ISBN10']?></td>
								<td><?=$book['Title']?></td>
								<td><?=$book['Author']?></td>
								<td><?=$book['PublicationYear']?></td>
								<td><?=$book['NumberAvailable']?></td>
								<td><?=$book['TotalQuantity']?></td>
								<?php 
									if ($_SESSION['role'] !== 'Admin') {
										$itemId = $book['ItemId'];
										$disabled = "";
										if ($book['NumberAvailable'] == 0) {
											$disabled = "disabled";
										}
										echo "<td>";
										echo "<a href=\"home.php?borrow=$itemId\" onclick=\"return confirm('Continue to borrow?')\" >";
										echo "<input type='button' $disabled style=";
										if($book['NumberAvailable'] == 0) {
											echo "\"color:#FCFCFC; background-color: grey;\"";
										} else {
											echo "\"color:#FCFCFC; background-color: #1F61D5;\"";
										}
										echo "value='Borrow'>";
										echo "</a>";

										echo "<a href=\"home.php?hold=$itemId\" onclick=\"return confirm('Continue to hold?')\" >";
										echo "<input type='button' $disabled style=";
										if($book['NumberAvailable'] == 0) {
											echo "\"color:#FCFCFC; background-color: grey;\"";
										} else {
											echo "\"color:#FCFCFC; background-color: #FF0000;\"";
										}
										echo "value='Hold'>";
										echo "</a>";

										echo "</td>";
									}
								?>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</body>
</html>