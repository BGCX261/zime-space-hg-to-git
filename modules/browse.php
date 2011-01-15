<?php
/**
* @version		$Id: browse.php 10000 2011-01-16 00:38:00 @strazds $
* @package		ZIME.Framework
* @subpackage	Modules
* @copyright	Copyright (C) 1999 - 2011 by ZIME Foundation. All rights reserved.
* @license		http://www.zime.lv/license
*/
?>

<?php
include_once ("modules/url.php");
/**
 * ZIME Page Browser class
 *
 * @static
 * @package 	ZIME.Framework
 * @subpackage	Modules
 * @since		3.0
 */
class ZBrowse {
	/**
	 * Shows the page browser control.
	 *
	 * @access	public
	 * @param	integer $items_per_page The count of items per page.
	 * @param	integer $items_total The total count of items.
	 * @param	integer $max_pages_visible The maximum visible count of pages in browser control.
	 * @since	3.0
	 *
	 */
	function control($items_per_page = 5, $items_total = 0, $max_pages_visible = 10) {
		global $_GET, $SANITIZER, $CONFIG;
		$page_selected = $SANITIZER->sanitize(@$_GET["p"]);
		if (empty($page_selected)) {
			$page_selected = 1;
		}
		$part_first = 1;
		$part_last = ceil($items_total / $items_per_page);
		if ($part_first < 1) {
			$part_first = 1;
		}
		if ($part_last > ceil($items_total / $items_per_page)) {
			$part_last = ceil($items_total / $items_per_page);
		}
		
		$s = "";
		if ($part_last > 1) {
			$s .= "<table>";
			$s .= "<tr>";
			if ($page_selected > $part_first || $part_first > 1) {
				$s .= "<td nowrap><a class='color3-fore-color' style='padding-left: 4px; padding-right: 4px;' href='" . ZUrl::modify(array('p' => ($page_selected - 1))) . "'><strong>" . JText::_("Previous") . "</strong></a></td>";
			}
			for ($n = $part_first; $n <= $part_last; $n++) {
				if ($n < $page_selected - floor($max_pages_visible) / 2) {
				} else if ($n > $page_selected + floor($max_pages_visible) / 2) {
				} else {
					if ($page_selected == $n) {
						$s .= "<td nowrap><a class='color3-fore-color' style='padding-left: 4px; padding-right: 4px;' href='" . ZUrl::modify(array('p' => $n)) . "'>[$n]</a></td>";
					} else {
						$s .= "<td nowrap><a class='color3-fore-color' style='padding-left: 4px; padding-right: 4px;' href='" . ZUrl::modify(array('p' => $n)) . "'>$n</a></td>";
					}
				}
			}
			if ($page_selected < $part_last) {
				$s .= "<td nowrap><a class='color3-fore-color' style='padding-left: 4px; padding-right: 4px;' href='" . ZUrl::modify(array('p' => ($page_selected + 1))) . "'><strong>" . JText::_("Next") . "</strong></a></td>";
			} else {
				$s .= "<td nowrap>&nbsp;</td>";
			}
			$s .= "</tr>";
			$s .= "</table>";
		}
		echo $s;
	}
}
?>


