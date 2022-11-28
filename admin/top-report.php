<?php
include 'main.php';

check_loggedin($pdo);

function is_multi_array( $arr ) {
    rsort( $arr );
    return isset( $arr[0] ) && is_array( $arr[0] );
}

if (isset($_POST['submit'])) {
    $start_date = $_POST['StartDate'];
    $end_date = $_POST['EndDate'];

    $query = "
        SELECT Book.Title, Book.Author, Book.LanguageCode, Transaction.ItemID, COUNT(Transaction.ItemID) AS Freq
        FROM RamamurthyLibrary.Transaction
        INNER JOIN RamamurthyLibrary.Book ON Book.ItemId = Transaction.ItemID
        WHERE Transaction.TransactionDate BETWEEN '" . $start_date . "' AND '" . $end_date . "' GROUP BY Transaction.ItemID
        ORDER BY Freq DESC LIMIT 10";

    $rows = $pdo->query($query)->fetchAll();
}
?>

<?=template_admin_header('Transaction Report', 'transaction-report')?>

<h2>The Most Borrowed Books</h2>

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
                    <th>Title</th>
                    <th>Author</th>
                    <th>LanguageCode</th>
                    <th>Item ID</th>
                    <th>Transaction Num</th>
                </tr>
            </thead>
            <tbody>

            <?php if (!is_multi_array($rows)): ?>
                <tr>
                    <td><?=$rows['Title']?></td>
                    <td><?=$rows['Author']?></td>
                    <td><?=$rows['LanguageCode']?></td>
                    <td><?=$rows['ItemID']?></td>
                    <td><?=$rows['Freq']?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?=$row['Title']?></td>
                    <td><?=$row['Author']?></td>
                    <td><?=$row['LanguageCode']?></td>
                    <td><?=$row['ItemID']?></td>
                    <td><?=$row['Freq']?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>

<?=template_admin_footer()?>