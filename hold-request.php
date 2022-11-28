<?php
include 'main.php';

check_loggedin($pdo);

// $rows = $pdo->query('SELECT * FROM HoldRequest')->fetchAll();

$rows = $pdo->query('SELECT * FROM Item JOIN HoldRequest USING(ItemId)')->fetchAll();

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
?>

<h2>Hold Request</h2>

<head>

    <style>
    .button {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    /* font-size: 10px; */
    margin: 4px 2px;
    cursor: pointer;
    }

    .button2 {background-color: #008CBA;} /* Blue */
    .button3 {background-color: #f44336;} /* Red */ 
    .button4 {background-color: #e7e7e7; color: black;} /* Gray */ 
    .button5 {background-color: #555555;} /* Black */
    </style>

    <!-- <link href="admindelete.css" rel="stylesheet" type="text/css"> -->

</head>

<body>


<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td>Hold Request ID</td>
                    <td>User UD</td>
                    <td>Item ID</td>
                    <td>Item Type</td>
                    <td>Reserve Time</td>
                    <td>Expirtation Time</td>
                    <td>Status</td>
                    <td>Action</td>
                    <td>Reminder</td>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?=$row['HoldRequestId']?></td>
                    <td><?=$row['PSID']?></td>
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
                        <a href="hold-request.php?delete=<?=$row['HoldRequestId']?>" onclick="return confirm('Are you sure you want to delete this account?')">
                            <input type="button" style="color:#FCFCFC; background-color: #FF0000;" value="Delete">
                        </a>
                    </td>
                    <td>
                        <a href="hold-request.php?send_reminder=1" onclick="return confirm('Countinue?')">
                            <input type="button" style="color:#FCFCFC; background-color: #078E03;" value="Send Reminder">
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>


