<?php
include 'main.php';
// Check logged-in
check_loggedin($pdo);

$query = "
    SELECT
        us.PSID,
        Fname,
        Lname,
        Email,
        role,
        Funds_Available,
        Owed_Amount,
        MAX(balanceUpdateTime) balanceUpdateTime
    FROM Balance ba JOIN User us USING(PSID) 
    WHERE PSID = ?
    GROUP BY 1, 2, 3, 4,5, 6, 7
";

$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['id']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $query = "
//     SELECT
//         *
//     FROM 
//         Transaction ta JOIN User us USING(PSID) 
//         JOIN HoldRequest USING(PSID)
//     WHERE
//         ta.Active = 1
//         AND PSID = ?
// ";

// $stmt = $pdo->prepare($query);
// $stmt->execute([2169348]);
// $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// var_dump($transactions);

?>


<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Profile Page</title>
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
            <h2>User Report Page</h2>
            <?php echo '<a href="borrow-history.php"><button>Go To All Transaction History</button></a>'; ?>
            <button onclick="exportTableToExcel('mytable')">Export Table Data To Excel File</button>
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

<script type="text/javascript">
            function exportTableToExcel(tableID, filename = ''){
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableID);
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            
            // Specify file name
            filename = filename?filename+'.xls':'excel_data.xls';
            
            // Create download link element
            downloadLink = document.createElement("a");
            
            document.body.appendChild(downloadLink);
            
            if(navigator.msSaveOrOpenBlob){
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob( blob, filename);
            }else{
                // Create a link to the file
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
            
                // Setting the file name
                downloadLink.download = filename;
                
                //triggering the function
                downloadLink.click();
            }
        }
        </script>

        <div class="content">

            <div class="content-block">
                    <div class="table">
                        <table table id="mytable" class="display" style="width:100%">
                        <h3>Balance Report</h3>
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>User Email</th>
                                    <th>User Name</th>
                                    <th>User Role</th>
                                    <th>Funds Available</th>
                                    <th>Amount Owed</th>
                                    <th>Last Update Time</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?=$row['PSID']?></td>
                                    <td><?=$row['Email']?></td>
                                    <td><?=$row['Fname'] . " " . $row['Lname']?></td>
                                    <td><?=$row['role']?></td>
                                    <td><?=$row['Funds_Available']?></td>
                                    <td><?=$row['Owed_Amount']?></td>
                                    <td><?=$row['balanceUpdateTime']?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
             </div>



        </div>

	</body>
</html>