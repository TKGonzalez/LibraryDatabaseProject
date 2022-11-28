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

<h2>Hold Request</h2>
<input type="text" id="emailInput" onkeyup="searchUser()" placeholder="Search for anything..." title="Type in an email">

<style>
* {
  box-sizing: border-box;
}

#emailInput {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'%3E%3C/path%3E%3C/svg%3E");
  background-position: 10px 10px;
  background-repeat: no-repeat;
  width: 30%;
  font-size: 16px;
  padding: 12px 20px 12px 40px;
  border: 1px solid #ddd;
  margin-bottom: 12px;
}

#holdRequestTable {
  border-collapse: collapse;
  width: 100%;
  border: 1px solid #ddd;
  font-size: 18px;
}

#holdRequestTable th, #holdRequestTable td {
  text-align: left;
  padding: 12px;
}

#holdRequestTable tr {
  border-bottom: 1px solid #ddd;
}

#holdRequestTable tr.header, #holdRequestTable tr:hover {
  background-color: #f1f1f1;
}

</style>
</head>


<body>
<div class="content-block">
    <div class="table">
        <table id="holdRequestTable">
                <tr class="header">
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
                        <a href="hold-request.php?delete=<?=$row['HoldRequestId']?>" onclick="return confirm('Are you sure you want to delete this account?')">
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
        </table>
    </div>
</div>

<script>
    function searchUser() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("emailInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("holdRequestTable");
        tr = table.getElementsByTagName("tr");
        for (i = 0; i < tr.length; i++) {
            for (j = 0; j < 8; j++) {
                td = tr[i].getElementsByTagName("td")[j];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        break;
                    } else {
                        tr[i].style.display = "none";
                    }
                }  
            }     
        }
    }
</script>

</body>

<?=template_admin_footer()?>
