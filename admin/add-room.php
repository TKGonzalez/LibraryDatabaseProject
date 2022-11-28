<?php
include 'main.php';

if (isset($_POST['submit'])) {
    $itemId = (time()+ rand(1,1000));
    
    $stmt = $pdo->prepare('INSERT INTO MeetingRoom (ItemId, RoomNumber, Status) VALUES(?, ?, ?)');
    $stmt->execute([$itemId, $_POST['roomnumber'], 1]);
   
    $stmt = $pdo->prepare('INSERT INTO Item (ItemId, ItemType) VALUES(?, ?)');
    $stmt->execute([ $itemId, "MEETINGROOM"]);

    header('Location: items-room.php');
    exit;
}

?>
<?=template_admin_header($page . ' Inventory', 'room', 'add')?>

<h2><?=$page?> Add Room</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <label for="roomnumber">Room Number</label>
        <input id="roomnumber" type="text" name="roomnumber" required>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Submit">
        </div>

    </form>

</div>

<?=template_admin_footer()?>