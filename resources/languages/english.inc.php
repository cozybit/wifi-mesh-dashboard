<?php
$_LANG['name'] = "English";

// Login Page
$_LANG['home'] = "Home";
$_LANG['support'] = "Support";
$_LANG['about'] = "About";

$_LANG['username'] = "Username";
$_LANG['password'] = "Password";
$_LANG['login'] = "Login";
$_LANG['register'] = "Register";

$_LANG['incorrect_username'] = "Incorrect Username";
$_LANG['incorrect_password'] = "Incorrect Password";

$_LANG['demo_intro'] = "Want to try " . $brand . "?";
$_LANG['demo_link'] = "Login as a Demo User";
$_LANG['forgot_intro'] = "Forgot your password?";
$_LANG['forgot_link'] = "Reset Password";

	// Email messages
	$_LANG['email_account_removal'] = "Your account has been scheduled for removal.";
	$_LANG['email_account_removal_admin_subject'] = $_SESSION['user'] . " wants to be deleted from " . $brand;
	$_LANG['email_account_removal_admin_body'] = "Hi there " . $brand . " admin,\n\nPlease login to the admin console at:\nhttp://" . $sn . $wdir . "super-powers.php\nand remove the user: " . $_SESSION['user'] . " account.\n\nVerification Code: " . $_SESSION['user'] . $_SESSION['pass'] . "\n\nRegards,\nThe " . $brand . " server";
	$_LANG['email_server_complete'] = "Your server should now be working, you may now create an account on the left.";
	$_LANG['email_account_complete'] = "Please check your email for more details on how to get started using " . $brand . ".";


// Overview Page
$_LANG['error_no_checkin_data'] = "There is no checkin data for this network yet.";
$_LANG['error_nodes_checkedin'] = "No nodes have checked in so far.<br />Please check back here later.";
$_LANG['error_clients_checkedin'] = "No clients are active right now.<br />Please check back here later.";

$_LANG['nodes_list'] = "Nodes List";
$_LANG['node_info'] = 'Node Info';

$_LANG['close_window'] = "Close Window";

$_LANG['name'] = "name";
$_LANG['ip'] = "ip";
$_LANG['mac'] = "mac";
$_LANG['users'] = "users";
$_LANG['usage'] = "usage";
$_LANG['mb'] = "Mb";
$_LANG['uptime'] = "up time";
$_LANG['txrate'] = "tx rate";
$_LANG['version'] = "version";
$_LANG['load'] = "load";
$_LANG['memfree'] = "memfree";
$_LANG['gatewayip'] = "gateway ip";
$_LANG['ping'] = "ping";
$_LANG['hops'] = "hops";

$_LANG['clients_list'] = "Clients List";
$_LANG['clients'] = "Clients";

$_LANG['user_name_mac'] = "User Name/MAC";
$_LANG['lastseen'] = "Last Seen On";
$_LANG['vendor'] = "Wi-Fi Vendor";
$_LANG['rssi'] = "RSSI";
$_LANG['dbm'] = "dBm";
$_LANG['kbdown'] = "KB Down";
$_LANG['kbup'] = "Kb Up";
$_LANG['status'] = "Status";
$_LANG['graph'] = "Graph";
?>