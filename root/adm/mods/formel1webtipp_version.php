<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package formel1webtipp
*/
class formel1webtipp_version
{
	function version()
	{
		return array(
			'author'	=> 'Dr.Death',
			'title'		=> 'Formel1WebTipp',
			'tag'		=> 'formel1webtipp',
			'version'	=> '0.3.5',
			'file'		=> array('www.lpi-clan.de', 'updatecheck', 'formel1webtipp.xml'),
		);
	}
}

?>