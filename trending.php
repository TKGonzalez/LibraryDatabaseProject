<?php 
include 'main.php';
check_loggedin($pdo);

$msg = '';

/*select count(Book.ISBN), Book.ISBN, Book.Title, Book.Author
from Transaction
left JOIN Book 
on Transaction.ItemID=Book.ItemId
where DATE(Transaction.TransactionDate) >= (DATE(NOW()) - INTERVAL 15 DAY) and ISBN is not NULL 
GROUP BY Book.ISBN
Having Count(Book.ISBN) > 4;
*/

$stmt = $pdo->prepare('select count(Book.ISBN), Book.ISBN, Book.Title, Book.Author from Transaction left join Book on Transaction.ItemID = Book.ItemID where DATE(Transaction.TransactionDate) >= (DATE(NOW()) - INTERVAL 15 DAY) and ISBN is not NULL Group by Book.ISBN having Count(Book.ISBN) > 4');
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Books Trending Page</title>
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
    <div class="content">
			<h2>Books Trending Right Now</h2>
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
			<div class="content-block">
				<div class="table">
					<table table id="mytable" class="display" style="width:100%">
						<thead>
							<tr>
								<td>ISBN</td>
								<td>Title</td>
								<td>Author</td>
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
								<td><?=$book['ISBN']?></td>
								<td><?=$book['Title']?></td>
								<td><?=$book['Author']?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

    </body>
</html>