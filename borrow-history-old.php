<?php
include 'main.php';
// Check logged-in
check_loggedin($pdo);

if ($_GET['StartDate']>"0000-00-00" && $_GET['EndDate']>0000-00-00) {
    $query = "
        SELECT
            PSID,
            Lname,
            TransactionID,
            ExpectedReturnDate,
            ActualReturnDate,
            TransactionDate,
            ItemID,
            ItemType,
            Active
        FROM Transaction JOIN User USING(PSID)
        WHERE PSID = ? AND TransactionDate >= ? AND TransactionDate <= ?
        ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([ $_SESSION['id'], $_GET['StartDate'], $_GET['EndDate'] ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif ($_GET['StartDate']>"0000-00-00") {
    $query = "
        SELECT
            PSID,
            Lname,
            TransactionID,
            ExpectedReturnDate,
            ActualReturnDate,
            TransactionDate,
            ItemID,
            ItemType,
            Active
        FROM Transaction JOIN User USING(PSID)
        WHERE PSID = ? AND TransactionDate >= ?
        ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([ $_SESSION['id'], $_GET['StartDate'] ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
elseif ($_GET['EndDate']>"0000-00-00") {
    $query = "
        SELECT
            PSID,
            Lname,
            TransactionID,
            ExpectedReturnDate,
            ActualReturnDate,
            TransactionDate,
            ItemID,
            ItemType,
            Active
        FROM Transaction JOIN User USING(PSID)
        WHERE PSID = ? AND TransactionDate <= ?
        ";
        
    $stmt = $pdo->prepare($query);
    $stmt->execute([ $_SESSION['id'], $_GET['EndDate'] ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    $query = "
        SELECT
            PSID,
            Lname,
            TransactionID,
            ExpectedReturnDate,
            ActualReturnDate,
            TransactionDate,
            ItemID,
            ItemType,
            Active
        FROM Transaction JOIN User USING(PSID)
        WHERE PSID = ?
        ";
        
    $stmt = $pdo->prepare($query);
    $stmt->execute([ $_SESSION['id'] ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Borrow History</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">

        <!-- export testing -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script src="tableExport/tableExport.js"></script>
        <script type="text/javascript" src="tableExport/jquery.base64.js"></script>
        <script src="js/export.js"></script> 
        <!-- end export testing -->
	</head>
    
	<body class="loggedin">
		<nav class="navtop">
			<div>
				<h1>RamamurthyLibrary</h1>
				<a href="home.php"><i class="fas fa-home"></i>Home</a>
				<a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
                <a href="user-report.php"><i class="fas fa-file"></i>User Reports</a>
				<?php if ($_SESSION['role'] == 'Admin'): ?>
				<a href="admin/index.php" target="_blank"><i class="fas fa-user-cog"></i>Admin</a>
				<?php endif; ?>
				<a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
			</div>
		</nav>

        <div class="content">
            <h2>Borrow History Page</h2>
            <?php echo '<a href="user-report.php"><button>Go To Balance Summary Report</button></a>'; ?>
        </div>
        <br>
        <div style="width:300px; margin-left:460px;">
        <div>
        <form action="borrow-history.php">
        <label> 
        Enter dates to narrow results <br>
        From: <input type="date" name="StartDate" value="0000-00-00"/> <br>
        To: <input type="date" name="EndDate" value="0000-00-00"/>
        </label>
        <p><button>Submit</button></p>
        </form>
        Showing results from <?php echo $_GET["StartDate"] ?> to <?php echo $_GET["EndDate"]; ?> <br> <br>
         </div>
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

        <div class="content-header links responsive-flex-column">
        <div style="width:300px; margin-left:460px;">
            <button onclick="exportTableToExcel('mytable')">Export Table Data To Excel File</button>
    </div>
        </div>
         <div class="content">
             <div class="content-block">
                    <div class="table">
                        <table table id="mytable" class="display" style="width:100%">
                            <h3>Borrow History Report</h3>
                            <thead>
                                <tr>
                                    <th>PSID</th>
                                    <th>Last Name</th>
                                    <th>Transaction ID</th>
                                    <th>Return by</th>
                                    <th>Returned on</th>
                                    <th>Date Of Transaction</th>
                                    <th>Item ID</th>
                                    <th>Item Type</th>
                                    <th>Active</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?=$row['PSID']?></td>
                                    <td><?=$row['Lname']?></td>
                                    <td><?=$row['TransactionID']?></td>
                                    <td><?=$row['ExpectedReturnDate']?></td>
                                    <td><?=$row['ActualReturnDate']?></td>
                                    <td><?=$row['TransactionDate']?></td>
                                    <td><?=$row['ItemID']?></td>
                                    <td><?=$row['ItemType']?></td>
                                    <td><?=$row['Active']?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
             </div>
        
        </div>
	</body>
</html>