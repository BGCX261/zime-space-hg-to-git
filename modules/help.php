<?php
// @(#) $Id$
// +-----------------------------------------------------------------------+
// | Copyright (C) 2008, http://yoursite                                   |
// +-----------------------------------------------------------------------+
// | This file is free software; you can redistribute it and/or modify     |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation; either version 2 of the License, or     |
// | (at your option) any later version.                                   |
// | This file is distributed in the hope that it will be useful           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of        |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the          |
// | GNU General Public License for more details.                          |
// +-----------------------------------------------------------------------+
// | Author: pFa                                                           |
// +-----------------------------------------------------------------------+
//
?>

<?php 
include_once("modules/url.php");
class ZHelp {
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
	
	function context_link($context_id) { 
		return "<sup><small><a href='" . ZUrl::modify(array('help' => $context_id)) . "'>?</a></small></sup>";
	}
	
	function help_dialog() { 
		/* */
		echo "<div class='' id='help-topic' style='display:block;'>";
			$content = JText::__('help_user_experience.htm');
			//$content = str_replace("[URL9]", "mailto:support@zime.lv", $content);
			echo "<table width='650'><tr><td>";
			echo $content;
			echo "</td></tr></table>";
			//echo '<input class="button" type="submit" name="confirm_delete" id="confirm_delete" onclick="form.action=\'settings\?t=deleted\'" value="' . JText::_("Deactivate account") . '" />';
		echo "</div>";
	}
}
?>


