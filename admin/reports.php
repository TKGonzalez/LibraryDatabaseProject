<?php
include 'main.php';

check_loggedin($pdo);
?>

<?=template_admin_header('Reports', 'reports')?>

<h2>The Most Borrowed Books</h2>

<div class="content-block">

<form action="top-report.php" method="post" class="form responsive-width-100">
    From: <input type="date" name="StartDate" value="0000-00-00"/> <br>
    To: <input type="date" name="EndDate" value="0000-00-00"/>
    <div class="submit-btns">
        <input type="submit" name="submit" value="Build Report">
    </div>
</form>


</div>

<?=template_admin_footer()?>