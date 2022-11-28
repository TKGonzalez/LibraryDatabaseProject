<?php
include 'main.php';

check_loggedin($pdo);

$users = $pdo->query('SELECT * FROM User')->fetchAll();

// var_dump($users);

$rows = $pdo->query('SELECT * FROM Item JOIN HoldRequest USING(ItemId) JOIN User USING(PSID)')->fetchAll();

// Delete account
if (isset($_GET['delete'])) {
    // Delete the account
    $stmt = $pdo->prepare('DELETE FROM HoldRequest WHERE HoldRequestId = ?');
    $stmt->execute([ $_GET['delete'] ]);
    header('Location: hold-request.php?success_msg=2');
    exit;
}
// Handle success messages
if (isset($_GET['success_msg'])) {
    if ($_GET['success_msg'] == 1) {
        $success_msg = 'HoldRequest Updated successfully!';
    }
    if ($_GET['success_msg'] == 2) {
        $success_msg = 'HoldRequest deleted successfully!';
    }
    if ($_GET['success_msg'] == 3) {
        $success_msg = 'Reminder Email Sent successfully!';
    }
}

// send reminder
if (isset($_GET['item'])) {
    $item_arr = unserialize(urldecode($_GET['item']));
    send_hold_reminder_email($item_arr['Email'], $item_arr);
    exit;
}
?>

<?=template_admin_header('HoldRequest', 'hold-request')?>

<h1>User Hold Requests</h1>

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
</head>


<body>
<div class="content-block">
    <div class="table">
        <table table id="mytable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Hold Request ID</th>
                    <th>User ID</th>
                    <th>User Email</th>
                    <th>Item ID</th>
                    <th>Item Type</th>
                    <th>Reserve Time</th>
                    <th>Expirtation Time</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Reminder</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?=$row['HoldRequestId']?></td>
                    <td><?=$row['PSID']?></td>
                    <td><?=$row['Email']?></td>
                    <td><?=$row['ItemId']?></td>
                    <td><?=$row['ItemType']?></td>
                    <td><?=$row['RequestTime']?></td>
                    <td><?=$row['ExpirationTime']?></td>
                    <td>
                        <input 
                                type="button" 
                                disabled
                                style=<?php
                                    if ($row['Active']) {
                                        echo "\"color:#FCFCFC; background-color: #078E03;\"";
                                    } else {
                                        echo "\"color:#FCFCFC; background-color: #FF0000;\"";
                                    }
                                ?>
                                value= <?php
                                    if ($row['Active']) {
                                        echo "Active";
                                    } else {
                                        echo "Cancelled";
                                    }
                                ?>
                        >
                    </td>
                    <td>
                        <a href="update-hold-request.php?id=<?=$row['HoldRequestId']?>">
                            <input type="button" style="color:#FCFCFC; background-color: #1F61D5;" value="Edit">
                        </a>
                        <a href="hold-request.php?delete=<?=$row['HoldRequestId']?>" onclick="return confirm('Are you sure you want to delete this hold request?')">
                            <input type="button" style="color:#FCFCFC; background-color: #FF0000;" value="Delete">
                        </a>
                    </td>
                    <td>
                        <?php
                            echo "<a href='hold-request.php?item=" . urlencode(serialize($row)) . "' onclick=\"return confirm('Countinue to send reminder email?')\"> <input type=\"button\" style=\"color:#FCFCFC; background-color: #078E03;\" value=\"Send Reminder\"> </a>";
                            ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>

<?=template_admin_footer()?>
