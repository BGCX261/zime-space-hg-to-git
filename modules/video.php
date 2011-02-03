<?php
/**
* @version		$Id: video.php 10000 2011-02-03 21:54:00 @strazds $
* @package		ZIME.Framework
* @subpackage	Modules
* @copyright	Copyright (C) 1999 - 2011 by ZIME Foundation. All rights reserved.
* @license		http://www.zime.lv/license
*/
?>

<?php
/**
 * ZIME Video class
 *
 * @static
 * @package 	ZIME.Framework
 * @subpackage	Modules
 * @since		3.0
 */
class ZVideo {
	/**
	 * Shows ZIME Video control.
	 *
	 * @access	public
	 * @param	string $code The YouTube video code
	 * @param	integer $width The width of the video display (height is calculated automatically)
	 * @param	string $str_error The string to append the error messages
	 * @since	3.0
	 *
	 */
	function show($code = "fOrFwUN4VRU", $width = 171, $str_error = "") {
		$height = floor(350 / 425 * $width);
		echo "<object width=\"{$width}\" height=\"{$height}\" data=\"http://www.youtube.com/v/{$code}\" type=\"application/x-shockwave-flash\"><param name=\"src\" value=\"http://www.youtube.com/v/{$code}\" /></object>";
	}
}
?>


