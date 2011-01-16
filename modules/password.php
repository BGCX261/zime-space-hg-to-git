<?php
/**
* @version		$Id: password.php 10000 2011-01-16 22:11:00 @strazds $
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

/**
 * Password managment class
 *
 * @static
 * @package 	ZIME.Framework
 * @subpackage	Modules
 * @since		3.0
 */
class ZPassword {	
	
	/**
	 * Shows the resend user password dialog.
	 *
	 * @access	public
	 * @param	string $str_error The string to append the error messages
	 * @since	3.0
	 *
	 */
	function resend_password_dialog($str_error = "") {
		global $_POST, $SANITIZER, $CONFIG;
		if (isset($_POST["un"])) {
			$un = trim($_POST["un"]);
			$un = $SANITIZER->sanitize($un);
		} else {
			$un = "";
		}
		if (isset($_POST["email"])) {
			$email = trim($_POST["email"]);
			$email = $SANITIZER->sanitize($email);
		} else {
			$email = "";
		}
		if (isset($_POST["cmd_resend_password"]) && empty($str_error)) {
			echo "<table width=436>";
				echo "<tr>";
					echo "<td><h1>" . JText::_('Forgot your password?') . "</h1></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>" . JText::_('Instructions will be sent') . "</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>&nbsp;</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td style='font-size: 14pt;'>" . JText::_("Instructions were sent") . "</td>";
				echo "</tr>";
			echo "</table>";
		} else {
			echo "<table width=436>";
				echo "<tr>";
					echo "<td><h1>" . JText::_('Forgot your password?') . "</h1></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>" . JText::_('Instructions will be sent') . "</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>&nbsp;</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td width='120' style='padding-bottom: 5px;'>" . JText::_('Your user name') . "</td>";
				echo "</tr>";
				echo "<tr>";
				  echo "<td><input type='text' name='un' style='width: 210px;' value='{$un}' /></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>&nbsp;</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td width='120' style='padding-bottom: 5px;'>" . JText::_('Your email address') . "</td>";
				echo "</tr>";
				echo "<tr>";
				  echo "<td><input type='text' name='email' style='width: 210px;' value='{$email}' /></td>";
				echo "</tr>";
				echo "<tr>";
				  echo "<td><br /><input type='submit' class='button' onclick='form.action.value=\"resend_password\";' name='cmd_resend_password' value='" . JText::_('Send instructions') . "' /></td>";
				echo "</tr>";
			echo "</table>";
		}
	}
	
	
	/**
	 * Performs the resend user password action.
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function resend_password_action() {
		global $_POST, $SANITIZER, $CONFIG;
		$str_error = ''; // init
		if (isset($_POST["un"])) {
			$un = trim($_POST["un"]);
			$un = $SANITIZER->sanitize($un);
		} else {
			$un = "";
		}
		if (isset($_POST["email"])) {
			$email = trim($_POST["email"]);
			$email = $SANITIZER->sanitize($email);
		} else {
			$email = "";
		}
		/** Send email instructions about how to reset the password **/
		if (isset($_POST["cmd_resend_password"])) {
			
			if (trim($un) == "" || trim($email) == "") {
				$str_error .= JText::_('Required field cannot be left blank.') . '<BR />';
			}
			if (!ZEmail::check($email)) {
				$str_error .= JText::_('Email should look like an email address.') . '<BR />';
			}
			$email_address_owner_found = false;
			if (empty($str_error)) {
				$sql = "
								SELECT u.id, u.un, u.firstname, u.lastname
								FROM users AS u
								WHERE u.un = '$un'
								AND u.email = '$email'
								LIMIT 0, 1
							 ";
				$result = mysql_query($sql);
				if ($result) {
					$record_count = MySQL_NUM_ROWS($result);
					if ($record_count == 1) {
						$u_id = mysql_result($result, 0, "u.id"); // at least one user using the supplied email address was found
						$u_username = mysql_result($result, 0, "u.un");
						$u_firstname = mysql_result($result, 0, "u.firstname");
						$u_lastname = mysql_result($result, 0, "u.lastname");
						$u_fullname = $u_firstname . " " . $u_lastname;
						$email_address_owner_found = true;
					}
				}
				if ($email_address_owner_found) {
					/** Send instructions here **/
					
					/** Encrypt email address **/
					$strongCipher = new Cipher_blowfish;
					$strongCipher->setKey(@$CONFIG->secret);
					$activation = $strongCipher->zf_encrypt(date("Y-m-d H:i:s") . "_" . $u_id);
					
					/** Send email with password reset instructions **/
					$name = JText::_('ZIME Service'); //senders name
					$sender = "service@zime.lv"; //senders e-mail adress
					$recipient = $email; //recipient
					$subject = JText::_('Reset your ZIME Password'); //subject
					$mail_body = JText::__('email_pw_reset_instructions.txt');
					$mail_body = str_replace("[USER]", $u_fullname . " ($u_username)", $mail_body);
					$mail_body = str_replace("[URL]", "{$CONFIG->basedir_rewrite}validate.php?option=reset&activation=$activation", $mail_body);
					$header = "From: ". $name . " <" . $sender . ">\r\n"; //optional headerfields
					ini_set('sendmail_from', $sender); //Suggested by "Some Guy"
					mail($recipient, $subject, $mail_body, $header); //mail command :)
				} else {
					$str_error .= JText::_('Email address was not found.') . '<BR />';
				}
			}
		}
		return $str_error;
	}
	
	/**
	 * Validates the password reset request.
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function validate() {
		global $_GET, $CONFIG, $SANITIZER;
		$str_error = '';
		if (isset($_GET["option"])) {
			$option = strtoupper(trim($_GET["option"]));
			$option = $SANITIZER->sanitize($option);
		} else {
			$option = "";
			$str_error .= JText::_('Invalid option.') . '<BR />';
		}
		if (isset($_GET["activation"])) {
			$activation = trim($_GET["activation"]);
			$activation = $SANITIZER->sanitize($activation);
		} else {
			$activation = "";
			$str_error .= JText::_('Invalid activation request.') . '<BR />';
		}
		
		/** Check validation request **/
		if ($option != "") {
			$strongCipher = new Cipher_blowfish;
			$strongCipher->setKey(@$CONFIG->secret);
			$activation = $strongCipher->zf_decrypt($activation);
			$activation = explode("_", $activation);
			$request_time = $activation[0];
			$u_id = $activation[1];
			
			$days_since_activation_request = ZDate::datediff("d", $request_time, date("Y-m-d H:i:s"));
			if ($days_since_activation_request <= @$CONFIG->email_activation_timeout) {
				if ($option == "REGISTER" || $option == "RESET") {
					$sql = "
									UPDATE users AS u
									SET 
									u.isconfirmed = 1
									, u.modified = now()
									WHERE u.id = {$u_id}
								 ";
					//echo "<br />" . $sql;
					$result = mysql_query($sql);
					if ($result) {
						if ($option == "REGISTER") {
							// do nothing... OR header('Location: ...);
						} elseif ($option == "RESET") {
							ZPassword::change_password_dialog($u_id);
						} else {
							// do nothing... OR header('Location: ...);
						}
					} else {
						$str_error .= JText::_('Email activation failed.') . ' ' . JText::_('Please try again.') . '<BR />';
					}
				} else {
					// do nothing... OR header('Location: ...);
				}
			} else {
				$str_error .= JText::_('Email activation code has expired.') . '<BR />';
			}
		}
		return $str_error;
	}
	
	/**
	 * Shows the password change dialog.
	 *
	 * @access	public
	 * @param	integer $user_id The user id
	 * @param	string $str_error The string to append the error messages
	 * @since	3.0
	 *
	 */
	function change_password_dialog($user_id = "", $str_error = "") {
		global $_SESSION, $_POST, $SANITIZER, $SecureSession;
		
		if ($user_id == "") {
			$user_id = @$_SESSION['u_temp'];
		}
		
		if (isset($_POST["cmd_change_pw"])) {
			$user_id = $_POST["param"];
		}
		
		if (isset($_POST["cmd_change_pw"]) && empty($str_error)) {
			/** Password changed successfully **/
			echo "<table width='100%'>";
				echo "<tr>";
					echo "<td><h1>" . JText::_('Password changed') . "</h1></td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td>" . JText::_('Store password safely') . "</td>";
				echo "</tr>";
			echo "</table>";
		} else {
			echo "<br />";
			echo "<table>";
				echo "<tr>";
					echo "<td nowrap width='140'>" . JText::_('New Password') . "</td>";
				  echo "<td nowrap width='310'><input type='password' name='pw' id='pw' onkeyup='getPwStrength(\"" . JText::_('Be tricky') . "\", \"" . JText::_('Too short') . "\", \"" . JText::_('Good') . "\", \"" . JText::_('Weak') . "\");' style='width: 150;' value='' /> <div id='strength' style='display:inline;'></div></td>";
				echo "</tr>";
				echo "<tr><td>&nbsp;</td></tr>";
				echo "<tr>";
					echo "<td nowrap>" . JText::_('Verify New Password') . "</td>";
				  echo "<td><input type='password' name='pw2' id='pw2' style='width: 150;' value='' /></td>";
				echo "</tr>";
				echo "<tr><td>&nbsp;</td></tr>";
				echo "<tr>";
					echo "<td>&nbsp;</td>";
				  echo "<td><input type='submit' class='button' name='cmd_change_pw' onclick='form.param.value={$user_id};' value='" . JText::_('Change') . "' /></td>";
				echo "</tr>";
			echo "</table>";
		}
	}
	
	/**
	 * Performs the password change action.
	 *
	 * @access	public
	 * @param	integer $user_id The user id
	 * @since	3.0
	 *
	 */
	function change_password_action($user_id = "") {
		global $_POST, $SANITIZER, $SecureSession, $CONFIG;
		$str_error = ''; // init
		if ($user_id == "") {
			$str_error .= JText::_('User not found.') . '<BR />';
			return $str_error;
		}
		
		$_POST["pw"] = $SANITIZER->sanitize(@$_POST["pw"]);
		$_POST["pw2"] = $SANITIZER->sanitize(@$_POST["pw2"]);
		
		if (empty($_POST["pw"])) {
			$str_error .= JText::_('Password cannot be blank.') . '<BR />';
		} else {
			if ($_POST["pw"] != $_POST["pw2"]) {
				$str_error .= JText::_('Passwords do not match.') . '<BR />';
			}
		}
		
		if (empty($str_error)) {
			/** Hash password **/
			$pw_hash = md5($_POST["pw"]);
			/** Save the new password **/
			$sql = "
							UPDATE users AS u
							SET u.pw = '{$pw_hash}'
							WHERE u.id = {$user_id}
							";
			if ($_SERVER["REMOTE_ADDR"] == @$CONFIG->debug_ip) {
				// echo "<br />" . $sql;
			}
			$result = mysql_query($sql);
			if (!$result) {
				$str_error .= JText::_('Error saving password.') . '<BR />';
			} else {
				if (mysql_affected_rows() < 1) { // you are trying to save the same password
					// $str_error .= JText::_('Could not save password.') . '<BR />';
				}
			}
		}
		return $str_error;
	}
}
?>


