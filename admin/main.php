<?php
// Include the root "config.php" and "main.php" files
include_once '../config.php';
include_once '../main.php';

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged-in
check_loggedin($pdo, '../index.php');
// Fetch account details associated with the logged-in user
$stmt = $pdo->prepare('SELECT * FROM User WHERE PSID = ?');
$stmt->execute([ $_SESSION['id'] ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
// Check if the user is an admin...
if ($account['role'] != 'Admin') {
    exit('You do not have permission to access this page!');
}
// Add/remove roles from the list
$roles_list = ['Student','Faculty', 'Admin'];
// Template admin header
function template_admin_header($title, $selected = 'dashboard', $selected_child = '') {
    // Admin HTML links
    $admin_links = '
        <a href="index.php"' . ($selected == 'dashboard' ? ' class="selected"' : '') . '><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="hold-request.php"' . ($selected == 'hold-request' ? ' class="selected"' : '') . '><i class="fas fa-hand-holding"></i>Hold Request</a>
        
        <a href="accounts.php"' . ($selected == 'accounts' ? ' class="selected"' : '') . '><i class="fas fa-users"></i>Accounts</a>
        <div class="sub">
            <a href="accounts.php"' . ($selected == 'accounts' && $selected_child == 'view' ? ' class="selected"' : '') . '><span>&#9724;</span>View Accounts</a>
            <a href="account.php"' . ($selected == 'accounts' && $selected_child == 'manage' ? ' class="selected"' : '') . '><span>&#9724;</span>Create Account</a>
        </div>

        <a href="items.php"' . ($selected == 'items' ? ' class="selected"' : '') . '><i class="fas fa-book"></i>Inventory</a>
        
        <a href="reports.php"' . ($selected == 'reports' ? ' class="selected"' : '') . '><i class="fas fa-clipboard"></i>Reports</a>
        
        <a href="roles.php"' . ($selected == 'roles' ? ' class="selected"' : '') . '><i class="fas fa-list"></i>Roles</a>
    ';
    // Below links should be in $admin_links, were removed 
    // <a href="emailtemplate.php"' . ($selected == 'emailtemplate' ? ' class="selected"' : '') . '><i class="fas fa-envelope"></i>Email Templates</a>
    // <a href="settings.php"' . ($selected == 'settings' ? ' class="selected"' : '') . '><i class="fas fa-tools"></i>Settings</a>

    // Indenting the below code may cause an error
echo <<<EOT
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>$title</title>
        <link href="admin.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    </head>
    <body class="admin">
        <aside class="responsive-width-100 responsive-hidden">
            <h1>Admin Panel</h1>
            $admin_links
        </aside>
        <main class="responsive-width-100">
            <header>
                <a class="responsive-toggle" href="#">
                    <i class="fas fa-bars"></i>
                </a>
                <div class="space-between"></div>
                <a href="about.php" class="right"><i class="fas fa-question-circle"></i></a>
                <a href="account.php?id={$_SESSION['id']}" class="right"><i class="fas fa-user-circle"></i></a>
                <a href="../logout.php" class="right"><i class="fas fa-sign-out-alt"></i></a>
            </header>
EOT;
}
// Template admin footer
function template_admin_footer() {
    // Indenting the below code may cause an error
echo <<<EOT
        </main>
        <script>
        let aside = document.querySelector("aside"), main = document.querySelector("main"), header = document.querySelector("header");
        let asideStyle = window.getComputedStyle(aside);
        if (localStorage.getItem("admin_menu") == "closed") {
            aside.classList.add("closed", "responsive-hidden");
            main.classList.add("full");
            header.classList.add("full");
        }
        document.querySelector(".responsive-toggle").onclick = event => {
            event.preventDefault();
            if (asideStyle.display == "none") {
                aside.classList.remove("closed", "responsive-hidden");
                main.classList.remove("full");
                header.classList.remove("full");
                localStorage.setItem("admin_menu", "");
            } else {
                aside.classList.add("closed", "responsive-hidden");
                main.classList.add("full");
                header.classList.add("full");
                localStorage.setItem("admin_menu", "closed");
            }
        };
        document.querySelectorAll(".tabs a").forEach((element, index) => {
            element.onclick = event => {
                event.preventDefault();
                document.querySelectorAll(".tabs a").forEach((element, index) => element.classList.remove("active"));
                document.querySelectorAll(".tab-content").forEach((element2, index2) => {
                    if (index == index2) {
                        element.classList.add("active");
                        element2.style.display = "block";
                    } else {
                        element2.style.display = "none";
                    }
                });
            };
        });
        if (document.querySelector(".filters a")) {
            let filtersList = document.querySelector(".filters .list");
            let filtersListStyle = window.getComputedStyle(filtersList);
            document.querySelector(".filters a").onclick = event => {
                event.preventDefault();
                if (filtersListStyle.display == "none") {
                    filtersList.style.display = "flex";
                } else {
                    filtersList.style.display = "none";
                }
            };
            document.onclick = event => {
                if (!event.target.closest(".filters")) {
                    filtersList.style.display = "none";
                }
            };
        }
        document.querySelectorAll(".msg").forEach(element => {
            element.querySelector(".fa-times").onclick = () => {
                element.remove();
                history.replaceState && history.replaceState(null, '', location.pathname + location.search.replace(/[\?&]success_msg=[^&]+/, '').replace(/^&/, '?') + location.hash);
                history.replaceState && history.replaceState(null, '', location.pathname + location.search.replace(/[\?&]error_msg=[^&]+/, '').replace(/^&/, '?') + location.hash);
            };
        });
        history.replaceState && history.replaceState(null, '', location.pathname + location.search.replace(/[\?&]success_msg=[^&]+/, '').replace(/^&/, '?') + location.hash);
        history.replaceState && history.replaceState(null, '', location.pathname + location.search.replace(/[\?&]error_msg=[^&]+/, '').replace(/^&/, '?') + location.hash);
        </script>
    </body>
</html>
EOT;
}
// Convert date to elapsed string function
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = ['y' => 'year','m' => 'month','w' => 'week','d' => 'day','h' => 'hour','i' => 'minute','s' => 'second'];
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function send_hold_reminder_email($email, $item) 
{
	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();                                            //Send using SMTP
		$mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
		$mail->Username   = 'umalibraryservices@gmail.com';         //SMTP username
		$mail->Password   = 'kusqmtgegpstpdeg';                     //SMTP password
		$mail->SMTPSecure = 'ssl'; 
		$mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

		//Recipients
		$mail->setFrom('noreply@librarydb.tk', 'RamamurthyLibrary');
		$mail->addAddress($email);     //Add a recipient

		//Content
		$mail->isHTML(true);                                  //Set email format to HTML

        $email_html_string = file_get_contents('reminder-email-template.html');
        $email_html_string = str_replace('%c1%', $item['ItemId'], $email_html_string);
        $email_html_string = str_replace('%c2%', $item['ItemType'], $email_html_string);
        $email_html_string = str_replace('%c3%', $item['RequestTime'], $email_html_string);
        $email_html_string = str_replace('%c4%', $item['ExpirationTime'], $email_html_string);

		$mail->Subject = 'Item Hold Reminder';
		$mail->Body = $email_html_string;

		$mail->send();
		echo "<h2>Reminder Email Has Been Sent to {$email} Successfully!</h2>";
	} 
	catch (Exception $e) 
	{
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}
?>