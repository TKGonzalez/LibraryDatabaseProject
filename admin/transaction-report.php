<?php
include 'main.php';

check_loggedin($pdo);

$transactions = $pdo->query('SELECT * FROM Transaction JOIN User USING(PSID)')->fetchAll();

function is_multi_array( $arr ) {
    rsort( $arr );
    return isset( $arr[0] ) && is_array( $arr[0] );
}

if (isset($_POST['submit'])) {
    $qeury = "
        SELECT 
            us.PSID, 
            Fname, 
            Lname, 
            Email,
            Role, 
            TransactionId,
            TransactionDate,
            ActualReturnDate,
            ItemID,
            ItemType
        FROM 
            Transaction ta JOIN User us USING(PSID) 
        WHERE
             True
             AND ta.Active = 0
    ";

    $where = "";
    $input = array();
    if ($_POST['psid']) {
        $where = $where . " AND us.PSID = ?";
        array_push($input, $_POST['psid']);
    }

    if ($_POST['email']) {
        $where = $where . " AND us.Email = ?";
        array_push($input, $_POST['email']);
    }

    if ($_POST['itemType'] !== "ALL") {
        $where = $where . " AND ta.ItemType = ?";
        array_push($input, $_POST['itemType']);
    }

    $stmt = $pdo->prepare($qeury . $where);
    $stmt->execute($input);
    $rows = $stmt->fetchAll();
}
?>

<?=template_admin_header('Transaction Report', 'transaction-report')?>

<h2>User Return Report</h2>

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

</head>

<body>
<div class="content-block">
<button onclick="exportTableToExcel('mytable')">Export Table Data To Excel File</button>
    <div class="table">
        <table table id="mytable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User Email</th>
                    <th>User Name</th>
                    <th>User Role</th>
                    <th>Transaction Id</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                    <th>Item Id</th>
                    <th>Item Type</th>
                </tr>
            </thead>
            <tbody>

            <?php if (!is_multi_array($rows)): ?>
                <tr>
                    <td><?=$rows['PSID']?></td>
                    <td><?=$rows['Email']?></td>
                    <td><?=$rows['Fname'] . " " . $rows['Lname']?></td>
                    <td><?=$rows['Role']?></td>
                    <td><?=$rows['TransactionId']?></td>
                    <td><?=$rows['TransactionDate']?></td>
                    <td><?=$rows['ActualReturnDate']?></td>
                    <td><?=$rows['ItemID']?></td>
                    <td><?=$rows['ItemType']?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?=$row['PSID']?></td>
                    <td><?=$row['Email']?></td>
                    <td><?=$row['Fname'] . " " . $row['Lname']?></td>
                    <td><?=$row['Role']?></td>
                    <td><?=$row['TransactionId']?></td>
                    <td><?=$row['TransactionDate']?></td>
                    <td><?=$row['ActualReturnDate']?></td>
                    <td><?=$row['ItemID']?></td>
                    <td><?=$row['ItemType']?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>

<?=template_admin_footer()?>