<?php
include 'main.php';
check_loggedin($pdo);

$stmt = $pdo->prepare('SELECT * FROM MeetingRoom');
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
	//$stmt =  $pdo->prepare('UPDATE MeetingRoom SET Status = 0 WHERE ItemId = ?');
	//$stmt->execute([ $_GET['borrow'] ]);

	$stmt = $pdo->prepare('SELECT * FROM MeetingRoom');
	$stmt->execute();
	$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    $stmt->execute([$transactionId, "Reserve", $date->format('Y-m-d'), $_GET['borrow'], "MEETINGROOM", $_SESSION['id'], $expDate->format('Y-m-d'), 1]);
	}
	catch(Exception $error){
		$message = $error->getMessage();
		if ($message == "SQLSTATE[45000]: <<Unknown error>>: 1644 You have fees due. Cannot check out items.") {
			echo "<script type='text/javascript'>alert('You have unpaid overdue fees. Cannot check out items until all fees are paid.');</script>";
		}
		elseif ($message == "SQLSTATE[45000]: <<Unknown error>>: 1644 Maximum limit of items reached. Cannot check out more items.") {
			echo "<script type='text/javascript'>alert('Maximum limit of borrowed items reached. Cannot check out more items.');</script>";
		}
		elseif ($message == "SQLSTATE[45000]: <<Unknown error>>: 1644 You already have a meeting room. Cannot reserve another one."){
			echo "<script type='text/javascript'>alert('You already have a meeting room. Cannot reserve another one.');</script>";
		}
		header("Refresh:0; url=home-room.php");
		exit;
	}

	$stmt =  $pdo->prepare('UPDATE MeetingRoom SET Status = 0 WHERE ItemId = ?');
	$stmt->execute([ $_GET['borrow'] ]);

	header('Location: home-room.php');
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
		<!-- Linking Bootstrap CSS to the project for styling -->
        <link   crossorigin="anonymous"
                href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
                integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
                rel="stylesheet">
        <link href="search.css" rel="stylesheet" />
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
			<form action="home-room.php?select" method="post">
				<label for="category">Item Category</label>
				<select id="category" name="category">
					<option value="book">Book</option>
					<option value="dvd">DVD</option>
					<option value="laptop">Laptop</option>
					<option value="meetingRoom" selected>Meeting Room</option>
				</select>
				<input type="submit" value="Show">
			</from>


            <div class="content-block">
                <div class="table">
                    <table table id="mytable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <td>ItemID</td>
                                <td>Room Number</td>
								<?php 
									if ($_SESSION['role'] !== 'Admin') {
										echo "<td>Action</td>";
									}
								
								?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$rooms): ?>
                            <tr>
                                <td colspan="8" style="text-align:center;">There are no rooms</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?=$room['ItemId']?></td>
                                <td><?=$room['RoomNumber']?></td>
								<?php 
									if ($_SESSION['role'] !== 'Admin') {
										$itemId = $room['ItemId'];
										$disabled = "";
										if ($room['Status'] == 0) {
											$disabled = "disabled";
										}
										echo "<td>";
										echo "<a href=\"home-room.php?borrow=$itemId\" onclick=\"return confirm('Continue to borrow?')\" >";
										echo "<input type='button' $disabled style=";
										if($room['Status'] == 0) {
											echo "\"color:#FCFCFC; background-color: grey;\"";
										} else {
											echo "\"color:#FCFCFC; background-color: #078E03;\"";
										}
										echo "value='Reserve'>";
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