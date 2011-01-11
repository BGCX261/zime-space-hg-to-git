<?php
/**
* @version		$Id: help.php 10001 2011-01-11 15:51:00 @strazds $
* @package		ZIME.Framework
* @subpackage	Modules
* @copyright	Copyright (C) 1999 - 2011 by ZIME Foundation. All rights reserved.
* @license		http://www.zime.lv/license
*/
?>

<?php 
// no direct access
defined( '_ZEXEC' ) or die( 'Access restricted' );

include_once("modules/url.php");

/**
 * ZIME Help class
 *
 * @static
 * @package 	ZIME.Framework
 * @subpackage	Modules
 * @since		3.0
 */
class ZHelp {
	/**
	 * Shows the help dialog.
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function show() {
		global $_GET, $_SESSION, $SANITIZER;
		$menu_item = $SANITIZER->sanitize(@$_GET["t"]);
		$menu_item = strip_tags($menu_item);
		if (empty($menu_item)) {
			$menu_item = "help";
		}
		//ZHelp::menu($menu_item);
		switch ($menu_item) {
			case "help":
				ZHelp::help_dialog();
				break;
			case "about":
				//ZAbout::about_dialog($str_error);
				break;
			case "resources":
				//ZAbout::resources_dialog($_SESSION['u_temp']);
				break;
			case "people":
				//ZAbout::people_dialog();
				break;
			case "contact":
				ZHelp::contacts_dialog();
				break;
			default:
				// unkonown menu item - ignore
		}
	}
	
	/**
	 * Shows the help menu.
	 *
	 * @access	private
	 * @param	string $active_item The pre-selected menu item (optional)
	 * @since	3.0
	 *
	 */
	function menu($active_item = "") {
		$menu_items = array(
			"help" => "Help"
			, "about" => "About ZIME"
			, "resources" => "Resources"
			, "people" => "People"
			, "contact" => "Contact us"
			//, "design"  => "Design"
		);
		echo "<div id='settings-menu'>";
			echo "<table>";
				echo "<tr>";
					foreach ($menu_items AS $key => $value) {
						if ($key != $active_item) {
							echo "<td class='menu'><a style='text-decoration: none;' href='about/?t={$key}'><div class='menu-item' id='menu-item'>" . JText::_($value) . "</div></a></td>";
						} else {
							echo "<td class='menu'><a style='text-decoration: none;' href='about/?t={$key}'><div class='menu-item-active' id='menu-item'>" . JText::_($value) . "</div></a></td>";
						}
					}
				echo "</tr>";
			echo "</table>";
		echo "</div>";
		echo "<br />";
		echo "<br />";
	}
	
	/**
	 * Shows a link to the context sensitive help topic.
	 *
	 * @access	private
	 * @param	string $context_id The identifier of the help topic
	 * @since	3.0
	 *
	 */
	function context_link($context_id) { 
		return "<sup><small><a href='" . ZUrl::modify(array('help' => $context_id)) . "'>?</a></small></sup>";
	}
	
	/**
	 * Shows the help topic.
	 *
	 * @access	private
	 * @since	3.0
	 *
	 */
	function help_dialog() { 
		/* */
		echo "<div class='' id='help-topic' style='display:block;'>";
			$content = JText::__('help_user_experience.htm');
			//$content = str_replace("[URL9]", "mailto:support@zime.lv", $content);
			echo "<table width='650'><tr><td>";
			echo $content;
			echo "</td></tr></table>";
		echo "</div>";
	}
}
?>


