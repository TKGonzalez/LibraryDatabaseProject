<?php
include 'main.php';
// Default input product values
$holdRequest = [
    'active' => '',
    'expirationDate' => date('Y-m-d\TH:i:s')
];
// If editing an account
if (isset($_GET['id'])) {
    // Get the account from the database
    $stmt = $pdo->prepare('SELECT * FROM HoldRequest WHERE HoldRequestId = ?');
    $stmt->execute([ $_GET['HoldRequestId'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    // ID param exists, edit an existing account
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Update the account
        $stmt = $pdo->prepare('UPDATE HoldRequest SET Active = ?, ExpirationTime = ? WHERE HoldRequestId = ?');
        $stmt->execute([ $_POST['active'], $_POST['expirationDate'], $_GET['id'] ]);
        header('Location: hold-request.php?success_msg=1');
        exit;
    }
}

?>
<?=template_admin_header($page . ' Account', 'accounts', 'manage')?>

<h2><?=$page?> Hold Request</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">
        <label for="active">Active</label>
        <select id="active" name="active">
            <option value="1">Active</option>
            <option value="0">Cancel</option>
        </select>

        <label for="expirationDate">ExpirationDate</label>
        <input id="expirationDate" type="datetime-local" name="expirationDate" value="<?=date('Y-m-d\TH:i:s', strtotime($holdRequest['expirationDate']))?>" required>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
            <!-- <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('Are you sure you want to delete this account?')">
            <?php endif; ?> -->
        </div>

    </form>

</div>

<?=template_admin_footer()?>