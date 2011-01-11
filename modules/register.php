<?php
/**
* @version		$Id: register.php 10001 2011-01-11 15:40:00 @strazds $
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
include_once("modules/login.php");
include_once("modules/settings.php");
include_once("modules/user.php");
include_once("modules/collection.php");

/**
 * User registration class
 *
 * @static
 * @package 	ZIME.Framework
 * @subpackage	Modules
 * @since		3.0
 */
class ZRegister {	
	
	/**
	 * Shows the user registration dialog.
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function register_dialog() {
		global $_POST, $CONFIG, $SANITIZER, $SecureSession;
		$str_error = ''; // init
		if (isset($_POST["fullname"])) {
			$fullname = trim($_POST["fullname"]);
			$fullname = $SANITIZER->sanitize($fullname);
		} else {
			$fullname = "";
		}
		if (isset($_POST["un"])) {
			$un = trim($_POST["un"]);
			$un = $SANITIZER->sanitize($un);
		} else {
			$un = "";
		}
		/*
		if (isset($_POST["pw"])) {
			$pw = trim($_POST["pw"]);
			$pw = $SANITIZER->sanitize($pw);
		} else {
			$pw = "";
		}
		*/
		if (isset($_POST["pw"])) {
			//$pw_hash = trim($_POST["pw_hash"]);
			$pw_hash = md5($SANITIZER->sanitize($_POST["pw"]));
		} else {
			$pw_hash = "";
		}
		if (isset($_POST["email"])) {
			$email = trim($_POST["email"]);
			$email = $SANITIZER->sanitize($email);
		} else {
			$email = "";
		}
		
		echo "<br />";
		echo "<table width='100%'>";
			/** Headline **/
			echo "<tr>";
				echo "<td nowrap><h1>" . JText::_('Join ZIME') . "</h1></td>";
			  echo "<td width='100%'>&nbsp;</td>";
			  echo "<td nowrap valign='top'><small>" . JText::_('Already on ZIME?') . " <a href='{$CONFIG->basedir_rewrite}signin/'>" . JText::_('Sign in') . "</a></small></td>";
			echo "</tr>";
		echo "</table>";
		echo "<br />";
		echo "<table>";
			/** Full name **/
			echo "<tr>";
				echo "<td width='150'>" . JText::_('Full name') . "</td>";
			  echo "<td><input type='text' name='fullname' MAXLENGTH=20 style='width: 217px;' value='{$fullname}' /></td>";
			  echo "<td>&nbsp;</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>&nbsp;</td>";
				echo "<td colspan='2'><small>" . JText::_('Your full name will appear on your public profile') . "</small></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td colspan='3'>&nbsp;</td>";
			echo "</tr>";
			/** Username **/
			echo "<tr>";
				echo "<td width='120'>" . JText::_('Username') . "</td>";
			  echo "<td><input type='text' name='un' MAXLENGTH=15 style='width: 217px;' value='{$un}' /></td>";
			  echo "<td>&nbsp;</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>&nbsp;</td>";
				echo "<td colspan='2'><small>" . ZString::replaceVars(JText::_('Your public profile'), '<div id=\'username_small\' style=\'display:inline;\'>username</div>') . "</small></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td colspan='3'>&nbsp;</td>";
			echo "</tr>";
			/** Password **/
				echo "<tr>";
					echo "<td>" . JText::_('Password') . "</td>";
				  echo "<td colspan='2' nowrap><input type='password' name='pw' MAXLENGTH=32  id='pw' onkeydown='getPwStrength(\"" . JText::_('Be tricky') . "\", \"" . JText::_('Too short') . "\", \"" . JText::_('Good') . "\", \"" . JText::_('Weak') . "\");' onkeyup='getPwStrength(\"" . JText::_('Be tricky') . "\", \"" . JText::_('Too short') . "\", \"" . JText::_('Good') . "\", \"" . JText::_('Weak') . "\");' style='width: 217px;' value='' />&nbsp;&nbsp;&nbsp;<div id='strength' style='display:inline;'></div></td>";
				echo "</tr>";
			echo "<tr>";
				echo "<td colspan='3'>&nbsp;</td>";
			echo "</tr>";
			/** Email **/
			echo "<tr>";
				echo "<td>" . JText::_('Email') . "</td>";
			  echo "<td><input type='text' name='email' MAXLENGTH=255 style='width: 217px;' value='{$email}' /></td>";
			  echo "<td>&nbsp;</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>&nbsp;</td>";
				echo "<td colspan='2'><small>" . JText::_('Email will not be publicly displayed') . "</small></td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td colspan='3'>&nbsp;</td>";
			echo "</tr>";
			/** TOS **/
			echo "<tr>";
				echo "<td nowrap>" . JText::_('Terms of Service') . "</td>";
				echo "<td><textarea 
									id='tos' 
									name='tos'
									rows='5' 
									readonly='readonly'
									style='width:390px;'
									>" . JText::__('tos.txt') . " </textarea></td>";
			  echo "<td>&nbsp;</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td colspan='3'>&nbsp;</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td>&nbsp;</td>";
				echo "<td colspan='2' style='width:390px;'>" . JText::_("By clicking you are agreeing") . "</td>";
			echo "</tr>";
			echo "<tr>";
				echo "<td colspan='3'>&nbsp;</td>";
			echo "</tr>";

			/** Submit **/
			echo "<tr>";
				echo "<td>&nbsp;</td>";
			  echo "<td><input type='submit' class='button' name='cmd_register' onclick='javascript: form.tos.value = \"\";' value='" . JText::_('Create my account') . "' /></td>";
				echo "<td>&nbsp;</td>";
			echo "</tr>";
		echo "</table>";
		echo "<br />";
	}
	
	/**
	 * Performs a new user registration.
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function register_action() {
		global $_POST, $CONFIG, $SANITIZER, $SecureSession;
		$str_error = ''; // init
		if (isset($_POST["fullname"])) {
			$fullname = trim($SANITIZER->sanitize($_POST["fullname"]));
		} else {
			$fullname = "";
		}
		if (isset($_POST["un"])) {
			$un = trim($SANITIZER->sanitize($_POST["un"]));
		} else {
			$un = "";
		}
		/*
		if (isset($_POST["pw"])) {
			$pw = trim($SANITIZER->sanitize($_POST["pw"]));
		} else {
			$pw = "";
		}
		*/
		if (isset($_POST["pw"])) {
			//$pw_hash = trim($SANITIZER->sanitize($_POST["pw_hash"]));
			$pw_hash = md5(trim($SANITIZER->sanitize($_POST["pw"])));
		} else {
			$pw_hash = "";
		}
		if (isset($_POST["email"])) {
			$email = trim($SANITIZER->sanitize($_POST["email"]));
		} else {
			$email = "";
		}
		
		$email_validation_required = true;
		
		/**
		Save new user's data
		*/
		if (isset($_POST["cmd_register"])) {
			
			/** Check inputs**/
			//echo $pw_hash;
			if ($fullname == "" || $pw_hash == md5("")) {
				$str_error .= JText::_("Required field cannot be left blank.") . '<br />';
				//return $str_error;
			}
			
			/** Test integrity username **/
			$str_error .= ZRegister::test_integrity_username($un);
			
			/** Test integrity email **/
			$str_error .= ZRegister::test_integrity_email($email);
			
			/** Extract firstname, lastname from full name **/
			$fullname_array = ZRegister::extract_fullname_parts($fullname);
			$firstname = $fullname_array[0];
			$lastname = $fullname_array[1];
			
			if (empty($str_error)) {
				$sql = "
								INSERT INTO users (
									proj_fid
									, proj_item_id
									, un
									, pw
									, firstname
									, lastname
									, gender
									, email
									, birth_date
									, age_rule
									, country
									, language
									, timezone
									, newsletter
									, isconfirmed
									, created)
								VALUES (
									7
									, 1
									, '{$un}'
									, '{$pw_hash}'
									, '{$firstname}'
									, '{$lastname}'
									, 2
									, '{$email}'
									, '2100-01-01'
									, 0
									, ''
									, ''
									, 0
									, 0
									, 0
									, now()
								)
							 ";
				//
				if ($_SERVER["REMOTE_ADDR"] == @$CONFIG->debug_ip) {
					//echo $sql;
				}
				$result = mysql_query($sql);
				$new_user_id = mysql_insert_id();
				
				/* */
				if ($new_user_id && (mysql_affected_rows() > 0)) {
					@setcookie("registered", 1, time()+(60*60*24*365), "/");  /* expire in 1 year */
					
					/** Add default Josta (News-Josta) **/
					//$str_error = ZCollection::add_josta($new_user_id, JText::_("Friends"), "", $str_error);
					$str_error = ZCollection::add_josta($new_user_id, "Default Josta", "", $str_error);
					//ZUser::add_user_to_josta($new_user_id);
					
				} else {
					@session_destroy();
					$str_error .= JText::_('Registration was not successful. Please try again.');
				}
			}
			
			/** Send email validation request **/
			if ($email_validation_required && empty($str_error)) {
				
				// Encrypt email address
				$strongCipher = new Cipher_blowfish;
				$strongCipher->setKey(@$CONFIG->secret);
				$activation = $strongCipher->zf_encrypt(date("Y-m-d H:i:s") . "_" . $new_user_id);
				
				// Send email with password reset instructions
				$name = JText::_('ZIME Service'); //senders name
				$sender = "service@zime.lv"; //senders e-mail adress
				$recipient = $email; //recipient
				$subject = ZString::replaceVars(JText::_('Welcome to ZIME'), $un); //subject
				$mail_body = JText::__('email_registration.txt');
				$mail_body = str_replace("[USER]", $fullname . " ($un)", $mail_body);
				$mail_body = str_replace("[URL]", "{$CONFIG->basedir_rewrite}validate.php?option=register&activation=$activation", $mail_body);
				$header = "From: ". $name . " <" . $sender . ">\r\n"; //optional headerfields
				//echo $mail_body
				ini_set('sendmail_from', $sender); //Suggested by "Some Guy"
				
				if (!@mail($recipient, $subject, $mail_body, $header)) { //mail command :)
					$str_error .= JText::_('Could not send the notification.');
				}
			}
			
			/** Set default notices **/
			$_POST["notice_new_follower"] = "1";
			ZSettings::notices_action($new_user_id); // catch $str_error ??
			
			/** Log in user **/
			if (empty($str_error)) {
				$str_error .= ZLogin::login($un, $pw_hash);
			} else {
				//@session_destroy();
				//$str_error .= JText::_('We cannot log you into your account at this time. Please try again later.') . '<br />';
			}
						
			if (!empty($str_error)) {
				return $str_error;
			} else {
				@header("Location: {$CONFIG->basedir_rewrite}");
			}
		}
	}
	
	/**
	 * Shows the 'Join ZIME' dialog.
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function join_dialog() {
		//echo "<div class='slot' style='display:block; width:120px;'>";
		echo "<br />";
		echo "<table width='100%'>";
			/** Headline **/
			echo "<tr>";
				echo "<td nowrap align='center'><h2>" . JText::_('Create Your Account') . "</h2></td>";
			echo "</tr>";
			/** Submit **/
			echo "<tr>";
			  echo "<td align='center'><br /><input type='button' class='button_important' style='color: #A12830 !important; font-size: 15px !important; letter-spacing: 0.1ex !important;' name='cmd_join' onclick='document.location.href=\"signup\";' value='" . JText::_('Join') . "' /></td>";
			echo "</tr>";
			/** Select language **/
			echo "<tr>";
				echo "<td align='center'>";
					echo "<br /><br /><select name='language' onchange='form.submit();'>";
					echo "<option value='' selected='selected'>" . JText::_('Select Language') . "&nbsp;</option>";
					echo "<option value='en-GB'>English</option>";
					echo "<option value='lv-LV'>Latvian - Latviešu</option>";
					echo "</select>";
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		//echo "</div>";
		echo "<br />";
	}
	
	/**
	 * Performs the user name integrity test.
	 *
	 * @access	public
	 * @param	string $un The user name
	 * @since	3.0
	 *
	 */
	function test_integrity_username($un) {
		$str_error = ""; // init
		
		if (@$un == "") {
			$str_error .= JText::_("Username cannot be left blank.") . '<br />';
			return $str_error;
		}
		
		/** Check username sytax **/
		$is_valid_un = preg_match('/^[a-zA-Z0-9_]*$/', $un);
		if (!$is_valid_un) {
			$str_error .= JText::_("Wrong username syntax") . '<br />';
			return $str_error;
		}
		
		$un_exists = false;
		
		/** all numbers are currently reserved as usernames **/
		if (is_numeric($un)) {
			$un_exists = true;
		}
		
		if (!$un_exists) {
			/** list of reserved user names **/
			$reserved_un_list = "
				about, aboutus, admin, administer, administor, administrater, 
				administrator, anonymous, auther, author, 
				blogger, 
				contact, contactus, contributer, contributor, cpanel, 
				delete, directer, director, 
				editer, editor, email, emailus, 
				games, guest, 
				index, info, 
				loggedin, loggedout, login, logout, 
				moderater, moderator, mysql, 
				newjosta, nobody, 
				operater, operator, oracle, owner, 
				postmaster, 
				president, 
				registar, register, registrar, root, 
				strazds,
				signin, signout, 
				test, 
				user, 
				vicepresident, 
				webmaster,
				zime";
			$reserved_un = preg_split("/[\s,]+/", $reserved_un_list);
			if (in_array(strtolower($un), array_map('strtolower', $reserved_un))) {
			    $un_exists = true;
			}
		}
		
		/** look in database for existing username **/
		if (!$un_exists) {
			$sql = "
				SELECT u.un
				FROM users AS u
				WHERE u.un = '{$un}'
				LIMIT 0, 1
			";
			//echo $sql;
			$result = mysql_query($sql);
			$record_count = 0;
			if ($result) {
				$record_count = MySQL_NUM_ROWS($result);
			}
			if ($record_count == 1) {
				$un_exists = true;
			}
		}
		
		if ($un_exists) {
			$str_error .= JText::_('Username has already been taken.') . '<br />';
		}
		return $str_error;
	}
	
	/**
	 * Performs the email address integrity test.
	 *
	 * @access	public
	 * @param	string $email The email address
	 * @since	3.0
	 *
	 */
	function test_integrity_email($email) {
		$email_validation_required = true;
		$str_error = ""; // init
		
		/** is email address blank? **/
		if (@$email == "") {
			$str_error .= JText::_("Email cannot be left blank.") . '<br />';
			return $str_error;
		}
		
		/** check email address syntax **/
		if (!ZEmail::check($email)) {
			$str_error .= JText::_('Email should look like an email address.') . '<BR />';
			return $str_error;
		}
		
		$email_exists = false;
		
		/** look in database for existing username **/
		if (!$email_exists) {
			$sql = "
							SELECT u.email
							FROM users AS u
							WHERE u.email = '{$email}'
							LIMIT 0, 1
						 ";
			//echo $sql;
			$result = mysql_query($sql);
			$record_count = 0;
			if ($result) {
				$record_count = MySQL_NUM_ROWS($result);
			}
			if ($record_count == 1) {
				$email_exists = true;
			}
		}
		
		if ($email_exists) {
			$str_error .= JText::_('Email has already been taken.') . '<br />';
		}
		return $str_error;
	}
	
	/**
	 * Extracts firstname, lastname from full name .
	 *
	 * @access	public
	 * @param	string $fullname The user full name
	 * @since	3.0
	 *
	 */
	function extract_fullname_parts($fullname) {
		$fullname_array = explode(" ", $fullname, 2);
		if (is_array($fullname_array) && (count($fullname_array) == 2)) {
			$firstname = trim($fullname_array[0]);
			$lastname = trim($fullname_array[1]);
		} else {
			$firstname = $fullname;
			$lastname = "";
		}
		return array($firstname, $lastname);
	}
}
?>


