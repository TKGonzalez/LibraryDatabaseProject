<?php
    include 'main.php';

    if (isset($_GET['id'])) 
    {   
        $stmt = $pdo->prepare('SELECT * FROM Transaction WHERE PSID = ?');
        $stmt->execute([ $_GET['id'] ]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare('SELECT * FROM Balance WHERE PSID = ?');
        $stmt->execute([ $_GET['id'] ]);
        $balance = $stmt->fetch(PDO::FETCH_ASSOC);
        $netBalance = $balance['Funds_Available'] - $balance['Owed_Amount'];
    }
    else
    {
        $stmt = $pdo->prepare('SELECT * FROM Transaction');
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (isset($_GET['return'])) 
    {
        try{
            $stmt = $pdo->prepare('UPDATE Transaction SET ActualReturnDate = ?, Active = ? WHERE TransactionID = ?');
            $date = new DateTime();
            $stmt->execute([ $date->format('Y-m-d'), 0, $_GET['return'] ]);
            }
            catch(Exception $error){
                $message = $error->getMessage();
                echo $message;
            }

            try{
                $stmt = $pdo->prepare('SELECT * FROM Transaction WHERE TransactionID = ?');
                $stmt->execute([ $_GET['return'] ]);
                $returnTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                catch(Exception $error){
                    $message = $error->getMessage();
                    echo "<script type='text/javascript'>alert('Something is wrong for the second time');</script>";
                    echo $message;
                }

        //$stmt = $pdo->prepare('UPDATE Transaction SET ActualReturnDate = ?, Active = ? WHERE TransactionID = ?');
        //$date = new DateTime();
        //$stmt->execute([ $date->format('Y-m-d'), 0, $_GET['return'] ]);

        //$stmt = $pdo->prepare('SELECT * FROM Transaction WHERE TransactionID = ?');
        //$stmt->execute([ $_GET['return'] ]);
        //$returnTransaction = $stmt->fetch(PDO::FETCH_ASSOC);

        if($returnTransaction['ItemType'] == 'BOOK')
        {
            $stmt = $pdo->prepare('SELECT * FROM Book WHERE ItemId = ?');
            $stmt->execute([ $returnTransaction['ItemID'] ]);
            $returnBook = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare('UPDATE Book SET NumberAvailable = ? WHERE ItemId = ?');
            $stmt->execute([$returnBook['NumberAvailable'] + 1, $returnTransaction['ItemID'] ]);
        }

        if($returnTransaction['ItemType'] == 'DVD')
        {
            $stmt = $pdo->prepare('SELECT * FROM DVD WHERE ItemId = ?');
            $stmt->execute([ $returnTransaction["ItemID"] ]);
            $returnDVD = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare('UPDATE DVD SET AmountAvailable = ? WHERE ItemId = ?');
            $stmt->execute([$returnDVD['AmountAvailable'] + 1, $returnTransaction["ItemID"] ]);
        }

        if($returnTransaction['ItemType'] == 'LAPTOP')
        {
            $stmt = $pdo->prepare('SELECT * FROM Laptop WHERE ItemId = ?');
            $stmt->execute([ $returnTransaction["ItemID"] ]);
            $returnLaptop = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare('UPDATE Laptop SET AmountAvailable = ? WHERE ItemId = ?');
            $stmt->execute([$returnLaptop['AmountAvailable'] + 1, $returnTransaction["ItemID"] ]);
        }

        if($returnTransaction['ItemType'] == 'MEETINGROOM')
        {
            $stmt = $pdo->prepare('SELECT * FROM MeetingRoom WHERE ItemId = ?');
            $stmt->execute([ $returnTransaction["ItemID"] ]);
            $returnRoom = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare('UPDATE MeetingRoom SET Status = ? WHERE ItemId = ?');
            $stmt->execute(['0', $returnTransaction["ItemID"] ]);
        }

        header('Location: summary.php?id=' . $returnTransaction['PSID']);
    }
?>

<?=template_admin_header($page . ' Account', 'accounts', 'manage')?>

<h2>Summary - <?=$_GET['id']?></h2>

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

<div class="content-block">
    <p><a href="balance.php?id=<?=$_GET['id']?>">Current Balance: $<?=$netBalance?></a></p>
    <div class="table">
        <table table id="mytable" class="display" style="width:100%">
            <thead>
                <tr>
                    <td>Transaction ID</td>
                    <td>Item ID</td>
                    <td>Item Type</td>
                    <td>Date</td>
                    <td>Return Date</td>
                    <td>Active</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
                <?php if (!$transactions): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no transactions for this user!</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?=$transaction['TransactionID']?></td>
                    <td><?=$transaction['ItemID']?></td>
                    <td><?=$transaction['ItemType']?></td>
                    <td><?=$transaction['TransactionDate']?></td>
                    <td><?=$transaction['ExpectedReturnDate']?></td>
                    <td><?=$transaction['Active']?></td>
                    <?php 
                        $transactionID = $transaction['TransactionID'];
                        $disabled = "";
                        if ($transaction['Active'] == 0) 
                        {
                            $disabled = "disabled";
                        }
                        echo "<td>";
                        if ($transaction['Active'] != 0)
                            echo "<a href=\"summary.php?return=$transactionID\" onclick=\"return confirm('Continue to return?')\" >";
                        echo "<input type='button' $disabled style=";
                        if($transaction['Active'] == 0) 
                        {
                            echo "\"color:#FCFCFC; background-color: grey;\"";
                        } 
                        else 
                        {
                            echo "\"color:#FCFCFC; background-color: #1F61D5;\"";
                        }
                        
                        echo "value='Return'>";
                        echo "</a>";
                        echo "</td>";
                        
                    ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>