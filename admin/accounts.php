<?php
    include 'main.php';

    // Delete account
    if (isset($_GET['delete'])) 
    {
        // Delete the account
        $stmt = $pdo->prepare('DELETE FROM User WHERE PSID = ?');
        $stmt->execute([ $_GET['delete'] ]);
        header('Location: accounts.php?success_msg=3');
        exit;
    }
    // Handle success messages
    if (isset($_GET['success_msg'])) 
    {
        if ($_GET['success_msg'] == 1) 
        {
            $success_msg = 'Account created successfully!';
        }
        if ($_GET['success_msg'] == 2) 
        {
            $success_msg = 'Account updated successfully!';
        }
        if ($_GET['success_msg'] == 3) 
        {
            $success_msg = 'Account deleted successfully!';
        }
    }

    // Retrieve the GET request parameters (if specified)
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Filters parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$activation = isset($_GET['activation']) ? $_GET['activation'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';
// Order by column
$order = isset($_GET['order']) && $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';
// Add/remove columns to the whitelist array
$order_by_whitelist = ['PSID','username','Email','activation_code','role','registered','last_seen'];
$order_by = isset($_GET['order_by']) && in_array($_GET['order_by'], $order_by_whitelist) ? $_GET['order_by'] : 'PSID';
// Number of results per pagination page
$results_per_page = 20;
// Declare query param variables
$param1 = ($page - 1) * $results_per_page;
$param2 = $results_per_page;
$param3 = '%' . $search . '%';
// SQL where clause
$where = '';
//$where .= $search ? 'WHERE (username LIKE :search OR email LIKE :search) ' : '';
$where .= $search ? 'WHERE (PSID LIKE :search OR Email LIKE :search) ' : '';
// Add filters
if ($status == 'active') {
    $where .= $where ? 'AND last_seen > date_sub(now(), interval 1 month) ' : 'WHERE last_seen > date_sub(now(), interval 1 month) ';
}
if ($status == 'inactive') {
    $where .= $where ? 'AND last_seen < date_sub(now(), interval 1 month) ' : 'WHERE last_seen < date_sub(now(), interval 1 month) ';
}
if ($activation == 'pending') {
    $where .= $where ? 'AND activation_code != "activated" ' : 'WHERE activation_code != "activated" ';
}
if ($role) {
    $where .= $where ? 'AND role = :role ' : 'WHERE role = :role ';
}
// Retrieve the total number of accounts
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM User ' . $where);
if ($search) $stmt->bindParam('search', $param3, PDO::PARAM_STR);
if ($role) $stmt->bindParam('role', $role, PDO::PARAM_STR);
$stmt->execute();
$accounts_total = $stmt->fetchColumn();
// Prepare accounts query
//$stmt = $pdo->prepare('SELECT * FROM User ' . $where . ' ORDER BY ' . $order_by . ' ' . $order . ' LIMIT :start_results,:num_results');
$stmt = $pdo->prepare('SELECT * FROM User');
//$stmt->bindParam('start_results', $param1, PDO::PARAM_INT);
//$stmt->bindParam('num_results', $param2, PDO::PARAM_INT);
if ($search) $stmt->bindParam('search', $param3, PDO::PARAM_STR);
if ($role) $stmt->bindParam('role', $role, PDO::PARAM_STR);
$stmt->execute();
// Retrieve query results
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_admin_header('Accounts', 'accounts', 'view')?>

<h2>Accounts</h2>

<?php if (isset($success_msg)): ?>
<div class="msg success">
    <i class="fas fa-check-circle"></i>
    <p><?=$success_msg?></p>
    <i class="fas fa-times"></i>
</div>
<?php endif; ?>

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

<div class="content-header links responsive-flex-column">
    <a href="account.php">Create Account</a>
</div>

<div class="content-block">
    <div class="table">
        <table table id="mytable" class="display" style="width:100%">
            <thead>
                <tr>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=id'?>">PSID<?php if ($order_by=='id'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=fname'?>">First<?php if ($order_by=='fname'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=lname'?>">Last<?php if ($order_by=='lname'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=email'?>">Email<?php if ($order_by=='email'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=activation_code'?>">Activation Code<?php if ($order_by=='activation_code'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=role'?>">Role<?php if ($order_by=='role'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=registered'?>">Registered Date<?php if ($order_by=='registered'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=last_seen'?>">Last Seen<?php if ($order_by=='last_seen'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td>Actions</td>
                </tr>
            </thead>
            <tbody>
                <?php if (!$accounts): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">There are no accounts</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($accounts as $account): ?>
                <tr>
                    <td><?=$account['PSID']?></td>
                    <td><?=$account['Fname']?></td>
                    <td><?=$account['Lname']?></td>
                    <td class="responsive-hidden"><?=$account['Email']?></td>
                    <td class="responsive-hidden"><?=$account['activation_code'] ? $account['activation_code'] : '--'?></td>
                    <td class="responsive-hidden"><?=$account['role']?></td>
                    <td class="responsive-hidden"><?=$account['registered']?></td>
                    <td class="responsive-hidden" title="<?=$account['last_seen']?>"><?=time_elapsed_string($account['last_seen'])?></td>
                    <td>
                        <a href="summary.php?id=<?=$account['PSID']?>">View</a>
                        <a href="account.php?id=<?=$account['PSID']?>">Edit</a>
                        <a href="accounts.php?delete=<?=$account['PSID']?>" onclick="return confirm('Are you sure you want to delete this account?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>