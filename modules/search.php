<?php
/**
* @version		$Id: search.php 10000 2011-01-16 00:32:00 @strazds $
* @package		ZIME.Framework
* @subpackage	Modules
* @copyright	Copyright (C) 1999 - 2011 by ZIME Foundation. All rights reserved.
* @license		http://www.zime.lv/license
*/
?>
<?php
include_once ("modules/url.php");
/**
 * ZIME Search class
 *
 * @static
 * @package 	ZIME.Framework
 * @subpackage	Modules
 * @since		3.0
 */
class ZSearch {
	/**
	 * Shows the search dialog (text input field).
	 *
	 * @access	public
	 * @since	3.0
	 *
	 */
	function search_dialog() {
		global $_GET, $_POST, $CONFIG, $SANITIZER;
		$q = $SANITIZER->sanitize(@$_GET["s"]);
		echo "<span class='glass' id='glass' onclick='document.location.href=\"" . @$CONFIG->basedir_rewrite . "\" + \"?s=\" + form.q.value.replace(\"#\", \"%23\");'><i></i></span>";
		echo "<input onkeypress='handleKeyPress(event,this.form, \"" . @$CONFIG->basedir_rewrite . "\", \"search\")' value='". $q . "' placeholder='" . JText::_('Search') . "' name='q' id='search-query' type='text'>";
	}
}
?>


