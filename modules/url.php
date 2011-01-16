<?php
/**
* @version		$Id: url.php 10000 2011-01-16 21:57:00 @strazds $
* @package		ZIME.Framework
* @subpackage	Modules
* @copyright	Copyright (C) 1999 - 2011 by ZIME Foundation. All rights reserved.
* @license		http://www.zime.lv/license
*/
?>

<?php

// no direct access
defined( '_ZEXEC' ) or die( 'Access restricted' );

/**
 * URL modification class
 *
 * @static
 * @package 	ZIME.Framework
 * @subpackage	Modules
 * @since		3.0
 */
class ZUrl {
	/**
	 * Modifies URL.
	 *
	 * @access	public
	 * @param	array $mod The array of parameters to modify, e.g. array('p' => 1)
	 * @since	3.0
	 *
	 */
	function modify($mod) { 
		if ($mod == "") {
			return "";
		}
		global $_SERVER;
		$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
		$_SERVER['QUERY_STRING'] = ZUrl::remove_qs_key($_SERVER['QUERY_STRING'], "id");
		$query = explode("&", $_SERVER['QUERY_STRING']);
		
		if (!$_SERVER['QUERY_STRING']) {
			$queryStart = "?";
		} else {
			$queryStart = "&";
		}
		
		/** modify/delete data **/
		foreach($query as $q) { 
			@list($key, $value) = explode("=", $q); 
			if(array_key_exists($key, $mod)) { 
				if($mod[$key]) { 
					$url = preg_replace('/\?'.$key.'='.$value.'/', '?' . $key.'='.$mod[$key], $url); 
					$url = preg_replace('/&'.$key.'='.$value.'/', '&' . $key.'='.$mod[$key], $url); 
				} else { 
					$url = preg_replace('/&?'.$key.'='.$value.'/', '', $url); 
				} 
			}
		} 
		/** add new data **/
		foreach($mod as $key => $value) { 
			if($value && !preg_match('/'.$key.'=/', $url)) { 
				$url .= $queryStart.$key.'='.$value; 
			} 
		} 
		return $url; 
	} 
	
	/**
	 * Removes the query-string key.
	 *
	 * @access	private
	 * @param	array $qs The query-string, e.g. $_SERVER['QUERY_STRING']
	 * @param	string $qs The key to remove
	 * @since	3.0
	 *
	 */
	function remove_qs_key($qs, $key) {
		$qs = preg_replace('/' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $qs . '&');
		$qs = substr($qs, 0, -1);
		return $qs;
	}	
}
?>


