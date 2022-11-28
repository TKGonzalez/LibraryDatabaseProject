<?php
include 'main.php';
$stmt = $pdo->prepare('SELECT * FROM Book');
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['select'])) {
    if (strcmp($_POST['category'], "all") == 0) {
        header('Location: items.php');
    } elseif (strcmp($_POST['category'], "dvd") == 0) {
        header('Location: items-dvd.php');
    } elseif (strcmp($_POST['category'], "laptop") == 0) {
        header('Location: items-laptop.php');
    } elseif (strcmp($_POST['category'], "meetingRoom") == 0) {
        header('Location: items-room.php');
    } elseif (strcmp($_POST['category'], "book") == 0) {
        header('Location: items-book.php');
    }
    exit;
}
?>

<?=template_admin_header($page . 'Items', 'items', 'viewBooks')?>

<h2>
    <i class="fas fa-book"></i> Books 
    <a href="add-book.php">
    <button style="background-color:green; color: white; cursor: pointer; font-size: 18px;">+</button>
    </a>
</h2>

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

<form action="items-book.php?select" method="post">
    <label for="category">Item Category</label>
    <select id="category" name="category">
        <option value="all">All</option>
        <option value="book" selected>Book</option>
        <option value="dvd">DVD</option>
        <option value="laptop">Laptop</option>
        <option value="meetingRoom">Meeting Room</option>
    </select>
    <input type="submit" value="Show">
</from>

<div class="content-block">
    <!-- Table for displaying all items -->
    <div class="table">
        <table table id="mytable" class="display" style="width:100%">
            <thead>
                <tr>
                    <td>ItemID</td>
                    <td>ISBN10</td>
                    <td>Title</td>
                    <td>Author</td>
                    <td>Publication Year</td>
                    <td>Available</td>
                    <td>Total</td>
                </tr>
            </thead>
            <tbody>
                <?php if (!$books): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no books</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?=$book['ItemId']?></td>
                    <td><?=$book['ISBN10']?></td>
                    <td><?=$book['Title']?></td>
                    <td><?=$book['Author']?></td>
                    <td><?=$book['PublicationYear']?></td>
                    <td><?=$book['NumberAvailable']?></td>
                    <td><?=$book['TotalQuantity']?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>