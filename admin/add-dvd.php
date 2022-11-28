<?php
include 'main.php';

if (isset($_POST['submit'])) {
    $itemId = (time()+ rand(1,1000));
    
    $stmt = $pdo->prepare('INSERT INTO DVD (ItemId, DVDTitle, DVDType, PublishDate, AmountAvailable, TotalAmount) VALUES(?, ?, ?, ?, ?, ?)');
    $stmt->execute([$itemId, $_POST['title'], $_POST['type'], $_POST['pbdate'], $_POST['quantity'], $_POST['quantity']]);
   
    $stmt = $pdo->prepare('INSERT INTO Item (ItemId, ItemType) VALUES(?, ?)');
    $stmt->execute([ $itemId, "DVD"]);

    header('Location: items-dvd.php');
    exit;
}

?>
<?=template_admin_header($page . ' Inventory', 'dvd', 'add')?>

<h2><?=$page?> Add DVD</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <label for="title">DVD Title</label>
        <input id="title" type="text" name="title" required>

        <label for="type">DVD Type</label>
        <input id="type" type="text" name="type" required>

        <label for="pbdate">Publication Date</label>
        <input id="pbdate" type="text" name="pbdate" value="2020-11-11" required>

        <label for="quantity">Quantity</label>
        <input id="quantity" type="number" name="quantity" required>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
        </div>

    </form>

</div>

<?=template_admin_footer()?>