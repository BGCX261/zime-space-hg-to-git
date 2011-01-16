<?php
/**
* @version		$Id: login.php 10000 2011-01-16 20:38:00 @strazds $
* @package		ZIME.Framework
* @subpackage	Modules
* @copyright	Copyright (C) 1999 - 2011 by ZIME Foundation. All rights reserved.
* @license		http://www.zime.lv/license
*/
?>

<?php
// no direct access
defined( '_ZEXEC' ) or die( 'Access restricted' );

include_once("libraries/email.php");
include_once("libraries/cipher.php");
include_once("libraries/date.php");
include_once("modules/user.php");

/**
 * User log in class
 *
 * @static
 * @package 	ZIME.Framework
 * @subpackage	Modules
 * @since		3.0
 */
class ZLogin {
	/**
	 * Shows the user sign-in dialog.
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function login_dialog() {
		global $_POST, $SANITIZER, $SecureSession, $CONFIG;
		$str_error = ''; // init
		if (isset($_POST["un"])) {
			$un = trim($_POST["un"]);
			$un = $SANITIZER->sanitize($un);
		} else {
			$un = "";
		}
		if (isset($_POST["pw"])) {
			$pw_hash = trim($_POST["pw"]);
			$pw_hash = md5($SANITIZER->sanitize($pw_hash));
		} else {
			$pw_hash = "";
		}
		
		$_POST["remember_me"] = 1;
		$remember_me = true;
		
		/** Get user data from DB **/
		$PL_PW = $CONFIG->secure_login_password;
		if (!isset($_POST["cmd_login"])) {
			if (isset($_COOKIE["pl"])) { // Persistent Log In
				$pl = $_COOKIE["pl"];
				$pl_ssid = $_COOKIE["pl_ssid"];
			} else {
				$pl = "";
				$pl_ssid = "";
			}
			if ($pl != "" && $pl_ssid != "") {
				$sql = "
								SELECT u.un AS un
								FROM users AS u
								WHERE MD5(CONCAT(u.id, '{$PL_PW}')) = '{$pl}'
								AND u.session_id = '{$pl_ssid}'
								AND u.deleted IS NULL
								LIMIT 0, 1
							 ";
				$result3 = mysql_query($sql);
				$record_count3 = MySQL_NUM_ROWS($result3);
				if ($record_count3 == 1) {
					$un = mysql_result($result3, 0, "un");
					$remember_me = true;
				}
			}
		}
		
		echo "<br />";
		echo "<table>";
			echo "<tr>";
				echo "<td width='120'>" . JText::_('User name') . "</td>";
			  echo "<td><input type='text' name='un' style='width: 150;' value='{$un}' /></td>";
			  echo "<td>&nbsp;</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td colspan='3'>&nbsp;</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>" . JText::_('Password') . "</td>";
			  echo "<td><input type='password' name='pw' style='width: 150;' value='' /></td>";
			  echo "<td>&nbsp;&nbsp;<a href='#' onclick='form.action.value=\"resend_password\"; form.submit();'>" . JText::_('Forgot?') . "</a></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>&nbsp;</td>";
			  echo "<td><br /><input type='submit' class='button' name='cmd_login' onclick='form.action=\"" . @$CONFIG->basedir_rewrite . "home' value='" . JText::_('Sign in') . "' /></td>";
				echo "<td>&nbsp;</td>";
			echo "</tr>";
		echo "</table>";
	}
	
	/**
	 * Performs user sign-in.
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function login_action() {
		global $_POST, $SANITIZER, $SecureSession, $CONFIG;
		$str_error = ''; // init
		if (isset($_POST["un"])) {
			$un = trim($_POST["un"]);
			$un = $SANITIZER->sanitize($un);
		} else {
			$un = "";
		}
		if (isset($_POST["pw"])) {
			$pw_hash = md5($SANITIZER->sanitize($_POST["pw"]));
		} else {
			$pw_hash = "";
		}
		$_POST["remember_me"] = 1;
		$remember_me = true;
		
		/** Get user data from DB **/
		$PL_PW = $CONFIG->secure_login_password;
		if (isset($_POST["cmd_login"])) {
			if ($un == "") {
				$str_error .= JText::_("Required field cannot be left blank.") . '<br />';
			}
			
			/** Log in user **/
			if (empty($str_error)) {
				$str_error .= ZLogin::login($un, $pw_hash);
			} else {
				@session_destroy();
				$str_error .= JText::_('We cannot log you into your account at this time. Please try again later.') . '<br />';
			}
			if (!empty($str_error)) {
				return $str_error;
			} else {
				//header("Location: {$CONFIG->basedir_rewrite}");
			}
		}
	}
	
	/**
	 * Performs user sign-in by user name and password hash.
	 *
	 * @access	public
	 * @param	string $un The user name
	 * @param	string $pw_hash The password hash
	 * @since	3.0
	 *
	 */
	function login($un, $pw_hash) {
		global $_SESSION, $_POST, $SANITIZER, $SecureSession, $CONFIG;
		$PL_PW = $CONFIG->secure_login_password;
		$str_error = ''; // init
		$_POST["remember_me"] = 1;
		$remember_me = true;
		$sql = "
						SELECT u.id, u.un, u.firstname, u.lastname, u.email, u.web, u.bio
						, GROUP_CONCAT(un.notice_fid ORDER BY un.notice_fid ASC SEPARATOR '|') AS u_notices
						FROM users AS u
						LEFT OUTER JOIN user_notices AS un ON un.user_fid = u.id
						WHERE u.un = '{$un}'
						AND u.pw = '{$pw_hash}'
						AND u.deleted IS NULL
						GROUP BY un.user_fid
						LIMIT 0, 1
					 ";
		if ($_SERVER["REMOTE_ADDR"] == @$CONFIG->debug_ip) {
			// echo $sql;
		}
		$result = mysql_query($sql);
		$record_count = 0;
		if ($result) {
			$record_count = MySQL_NUM_ROWS($result);
		}
		if ($record_count == 1) {
			$u_id = mysql_result($result, 0, "u.id");
			//ZUser::get($u_id);
	    $ss = new SecureSession();
	    $ss->check_browser = true;
	    $ss->check_ip_blocks = 2;
	    $ss->secure_word = 'SALT_';
	    $ss->regenerate_id = true;
	    $ss->Open();
	    $_SESSION['logged_in'] = true;
	    /** Generate a secure user id **/
	    $_SESSION['u'] = md5($_SESSION['ss_fprint'] . $u_id);
	    $_SESSION['u_temp'] = $u_id;
	    $u_un = mysql_result($result, 0, "u.un");
	    $u_email = mysql_result($result, 0, "u.email");
	    $u_firstname = mysql_result($result, 0, "u.firstname");
	    $u_lastname = mysql_result($result, 0, "u.lastname");
	    $u_web = mysql_result($result, 0, "u.web");
	    $u_bio = mysql_result($result, 0, "u.bio");
	    $_SESSION['u_un'] = $u_un;
	    $_SESSION['u_email'] = $u_email;
	    if (trim($u_lastname) != "") {
	    	$_SESSION['u_name'] = trim($u_firstname . " " . $u_lastname);
	    } else {
	    	$_SESSION['u_name'] = trim($u_firstname);
	    }
	    $_SESSION['u_web'] = trim($u_web);
	    $_SESSION['u_bio'] = trim($u_bio);
	    $u_notices = mysql_result($result, 0, "u_notices");
	    $u_notices = explode("|", $u_notices);
	    if (array_search("1", $u_notices) !== false) {
	    	$_SESSION["u_notice_1"] = "checked";
	    } else {
	    	$_SESSION["u_notice_1"] = "";
	    }
	    if (array_search("2", $u_notices) !== false) {
	    	$_SESSION["u_notice_2"] = "checked";
	    } else {
	    	$_SESSION["u_notice_2"] = "";
	    }
			
	    /** Get user data **/
			ZUser::query_user("", $_SESSION['u_temp']);
			ZUser::set();
	    
	    /** Save Session ID if 'Remember Me' activated **/
	    if (isset($_POST["remember_me"])) {
	    	$pl = MD5($u_id . $PL_PW);
	    	$pl_ssid = md5(uniqid(rand(), true));
	    	@setcookie("pl", $pl, time() + 3600 * 24 * 14, "/");  /* expire in 2 weeks */
	    	@setcookie("pl_ssid", $pl_ssid, time() + 3600 * 24 * 14, "/");  /* expire in 2 weeks */
			} else {
	    	$this_session_id = "";
	    	@setcookie("pl", "", time() - 3600);  /* delete cookie */
	    	@setcookie("pl_ssid", "", time() - 3600);  /* delete cookie */
	    }
	    /** Save new Persistent Login Session ID **/
			$sql = "
							UPDATE users AS u
							SET u.session_id = '{$pl_ssid}'
							WHERE u.un = '{$un}'
							AND u.pw = '{$pw_hash}'
						 ";
			if ($_SERVER["REMOTE_ADDR"] == @$CONFIG->debug_ip) {
				//echo $sql;
			}
			$result2 = mysql_query($sql);
	    //@header('Location: index.php');
	    //die();
		} else {
			@session_destroy();
			$str_error .= JText::_('Username and password do not match.') . '<br />';
		}
		return $str_error;
	}
}
?>


