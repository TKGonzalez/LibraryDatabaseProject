<?php
include 'main.php';

    if (isset($_GET['id'])) 
    {
        $stmt = $pdo->prepare('SELECT * FROM Balance WHERE PSID = ?');
        $stmt->execute([ $_GET['id'] ]);
        $balance = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($_POST['submit'])) 
        {
            // Update the balance
            $stmt = $pdo->prepare('UPDATE Balance SET Funds_Available = ?, Owed_Amount = ?, balanceUpdateTime = ? WHERE PSID = ?');
            $date = new DateTime();
            $stmt->execute([ $_POST['Funds_Available'], $_POST['Owed_Amount'], $date->format('Y-m-d'), $_GET['id'] ]);
            header('Location: balance.php?id=' . $_GET['id']);
            exit;
        }
    }
?>

<?=template_admin_header($page . ' Account', 'accounts', 'manage')?>

<h2>Balance - <?=$_GET['id']?></h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">
        <label for="Funds_Available">Funds Available: </label>
        <input type="text" id="Funds_Available" name="Funds_Available" placeholder="Funds Available" value="<?=$balance['Funds_Available']?>" required>

        <label for="Owed_Amount">Amount Owed: </label>
        <input type="text" id="Owed_Amount" name="Owed_Amount" placeholder="Owed Amount" value="<?=$balance['Owed_Amount']?>" required>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
        </div>

        <input type="button" onclick="location.href='summary.php?id=<?=$_GET['id']?>';" value="Back" />

    </form>

</div>

<?=template_admin_footer()?>