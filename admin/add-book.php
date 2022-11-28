<?php
include 'main.php';

if (isset($_POST['submit'])) {
    $itemId = (time()+ rand(1,1000));

    $stmt = $pdo->prepare('INSERT INTO Book (ItemId, Title, Author, PublicationYear, TotalQuantity, NumberAvailable) VALUES(?, ?, ?, ?, ?, ?)');
    $stmt->execute([$itemId, $_POST['title'], $_POST['author'], $_POST['pbyear'], $_POST['quantity'], $_POST['quantity']]);
   
    $stmt = $pdo->prepare('INSERT INTO Item (ItemId, ItemType) VALUES(?, ?)');
    $stmt->execute([ $itemId, "BOOK"]);

    header('Location: items-book.php');
    exit;
}

?>
<?=template_admin_header($page . ' Inventory', 'book', 'add')?>

<h2><?=$page?> Add Book</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <!-- <label for="isbn10">ISBN10</label>
        <input id="isbn10" type="number" name="isbn10" required> -->

        <label for="title">Book Title</label>
        <input id="title" type="text" name="title" required>

        <label for="author">Author</label>
        <input id="author" type="text" name="author" required>

        <label for="pbyear">Publication Year</label>
        <input id="pbyear" type="text" name="pbyear" value="2022" required>

        <label for="quantity">Quantity</label>
        <input id="quantity" type="number" name="quantity" required>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
        </div>

    </form>

</div>

<?=template_admin_footer()?>