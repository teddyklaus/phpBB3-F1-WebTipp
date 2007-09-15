<?php
/** 
*
* \install_f1webtipp.php
*
* @package
* @version $Id: install_f1webtipp.php 1 2007-07-30 13:25:14Z stoffel04 $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!$user->data['is_registered'])
{
    if ($user->data['is_bot'])
    {
        redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
    }
    login_box('', $user->lang['LOGIN_INFO']);
}
else if ($user->data['user_type'] != USER_FOUNDER)
{
	$message = "<span style=\"color:red;\">You must be logged in as a founder to run this script.</span><br />
				<span style=\"color:red;\">Du musst Gründer Rechte besitzen um dieses Script ausführen zu können.</span><br />
				";
	trigger_error($message);
}

$submit = request_var('install', '');

if ($submit == 'continue') 
{
	// What sql_layer should we use?
	switch ($db->sql_layer)
	{
		case 'mysql':
			$db_type = 'mysql_40';
		break;

		case 'mysql4':
			if (version_compare($db->mysql_version, '4.1.3', '>='))
			{
				$db_type = 'mysql_41';
			}
			else
			{
				$db_type = 'mysql_40';
			}
		break;

		case 'mysqli':
			$db_type = 'mysql_41';
		break;

		default:
			$db_type = $db->sql_layer;
		break;
	}

	switch ($db_type)
	{
		case 'mysql_40':
			$type = array(
				'mediumint'		=> 'mediumint',
				'mediumtext'	=> 'mediumblob',
				'int'			=> 'int',
				'tinyint'		=> 'tinyint',
				'text'			=> 'blob',
				'varchar'		=> 'varbinary',
				'character_set'	=> '',
			);
		break;

		case 'mysql_41':
			$type = array(
				'mediumint'		=> 'mediumint',
				'mediumtext'	=> 'mediumtext',
				'int'			=> 'int',
				'tinyint'		=> 'tinyint',
				'text'			=> 'text',
				'varchar'		=> 'varchar',
				'character_set'	=> 'CHARACTER SET `utf8` COLLATE `utf8_bin`',
			);	
		break;

		default:
			trigger_error('Sorry, only MySQL Databases are supportet yet.');
		break;
	}

	// Drop the formel_config table if existing
	$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'formel_config';
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	// Drop the formel_drivers table if existing
	$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'formel_drivers';
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	// Drop the formel_teams table if existing
	$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'formel_teams';
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	// Drop the formel_races table if existing
	$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'formel_races';
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	// Drop the formel_wm table if existing
	$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'formel_wm';
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	// Drop the formel_tipps table if existing
	$sql = 'DROP TABLE IF EXISTS '.$table_prefix.'formel_tipps';
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	// Create formel_config table
	$sql_ary[] = array(
		'type'	=> 'CREATE TABLE',
		'name'	=> $table_prefix . 'formel_config',
		'fields'	=> array(
			'config_name'				=> array('varchar', 255, ''),
			'config_value'				=> array('varchar', 255, ''),
		),
		'primary_key'	=> 'config_name',
	);
	
	// Create formel_drivers table
	$sql_ary[] = array(
		'type'	=> 'CREATE TABLE',
		'name'	=> $table_prefix . 'formel_drivers',
		'fields'	=> array(
			'driver_id'					=> array('mediumint', 8, 0, true, true),
			'driver_name'				=> array('varchar', 32, ''),
			'driver_img'				=> array('varchar', 255, ''),
			'driver_team'				=> array('mediumint', 8, 0),
		),
		'primary_key'	=> 'driver_id',
	);

	// Create formel_teams table
	$sql_ary[] = array(
		'type'	=> 'CREATE TABLE',
		'name'	=> $table_prefix . 'formel_teams',
		'fields'	=> array(
			'team_id'				=> array('mediumint', 8, 0, true, true),
			'team_name'				=> array('varchar', 64, ''),
			'team_img'				=> array('varchar', 255, ''),
			'team_car'				=> array('varchar', 255, ''),
		),
		'primary_key'	=> 'team_id',
	);

	// Create formel_races table
	$sql_ary[] = array(
		'type'	=> 'CREATE TABLE',
		'name'	=> $table_prefix . 'formel_races',
		'fields'	=> array(
			'race_id'				=> array('mediumint', 8, 0, true, true),
			'race_name'				=> array('varchar', 64, ''),
			'race_img'				=> array('varchar', 255, ''),
			'race_quali'			=> array('varchar', 255, 0),
			'race_result'			=> array('varchar', 255, 0),
			'race_time'				=> array('int', 11, 0),
			'race_length'			=> array('varchar', 8, ''),
			'race_laps'				=> array('mediumint', 8, 0),
			'race_distance'			=> array('varchar', 8, ''),
			'race_debut'			=> array('mediumint', 8, 0),
		),
		'primary_key'	=> 'race_id',
	);
	
	// Create formel_wm table
	$sql_ary[] = array(
		'type'	=> 'CREATE TABLE',
		'name'	=> $table_prefix . 'formel_wm',
		'fields'	=> array(
			'wm_id'					=> array('mediumint', 8, 0, true, true),
			'wm_race'				=> array('mediumint', 8, 0),
			'wm_driver'				=> array('mediumint', 8, 0),
			'wm_team'				=> array('mediumint', 8, 0),
			'wm_points'				=> array('mediumint', 8, 0),
		),
		'primary_key'	=> 'wm_id',
	);
	
	// Create formel_wm table
	$sql_ary[] = array(
		'type'	=> 'CREATE TABLE',
		'name'	=> $table_prefix . 'formel_tipps',
		'fields'	=> array(
			'tipp_id'				=> array('mediumint', 8, 0, true, true),
			'tipp_user'				=> array('mediumint', 8, 0),
			'tipp_race'				=> array('mediumint', 8, 0),
			'tipp_result'			=> array('varchar', 60, ''),
			'tipp_points'			=> array('mediumint', 8, 0),
		),
		'primary_key'	=> 'tipp_id',
	);
	
	// And now create all needed tables
	foreach ($sql_ary as $row)
	{
		$query = "{$row['type']} {$row['name']} (";
		
		foreach ($row['fields'] as $field => $value)
		{
			$query .= "{$field} {$type[$value[0]]}" . (($value[1]) ? "({$value[1]})" : ' ');
			if (substr($value[0], -3, 3) == 'int')
			{
				if (isset($value[3]) && $value[3] == false)
				{
					$query .= '';
				}
				else
				{
					$query .= 'UNSIGNED ';
				}
			}
			
			$auto = '';
			if (isset($value[4]) && $value[4])
			{
				$auto = ' auto_increment';
			}
			
			if (substr($value[0], -4, 4) != 'text' && !$auto)
			{
				$query .= "DEFAULT '{$value[2]}' ";
			}
			$query .= "NOT NULL";
			
			if ($auto)
			{
				$query .= ' auto_increment';
			}
			$query .= ', ';
		}
		$query .= "PRIMARY KEY ({$row['primary_key']}),";
		
		$query = substr($query, 0, -1);
		$query .= ") {$type['character_set']};";
		
		$db->sql_query($query);
	}	
	
	// Now fill the tables with defaults

	// Insert config values into formel_config table
	$sql = 'INSERT INTO '.$table_prefix."formel_config (config_name, config_value) VALUES 
		('mod_id', '2'),
		('deadline_offset', '600'),
		('forum_id', '0'),
		('event_change', '86400'),
		('points_mentioned', '1'),
		('points_placed', '1'),
		('points_fastest', '2'),
		('points_tired', '2'),
		('restrict_to', '0'),
		('no_car_img', 'nocar.jpg'),
		('no_team_img', 'noteam.jpg'),
		('no_driver_img', 'nodriver.jpg'),
		('driver_img_height', '60'),
		('car_img_height', '50'),
		('car_img_width', '140'),
		('driver_img_width', '48'),
		('team_img_width', '120'),
		('team_img_height', '48'),
		('show_in_profile', '1'),
		('no_race_img', 'norace.jpg'),
		('race_img_width', '210'),
		('race_img_height', '121'),
		('show_gfx', '1'),
		('show_gfxr', '1'),
		('show_headbanner', '1'),
		('head_height', '60'),
		('head_width', '468'),
		('headbanner1_img', 'images/formel/formel_webtipp.jpg'),
		('headbanner1_url', 'formel.php'),
		('headbanner2_img', 'images/formel/formel_rules.jpg'),
		('headbanner2_url', 'formel.php?mode=rules'),
		('headbanner3_img', 'images/formel/formel_stats.jpg'),
		('headbanner3_url', 'formel.php?mode=stats'),
		('show_countdown', '1'),
		('show_avatar', '1')
		";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	// Todo: Check all races against start time and date
	// Insert races into formel_races table
	$sql = 'INSERT INTO '.$table_prefix."formel_races (race_id, race_name, race_img, race_quali, race_result, race_time, race_length, race_laps, race_distance, race_debut) VALUES 
		(1, 'Melbourne - Australien', '', '0', '0', 1174186800, '5,303', 58, '307,574', 1996),
		(2, 'Malaysia / Kuala Lumpur', '', '0', '0', 1176015600, '5,543', 56, '310,408', 1999),
		(3, 'Bahrain / Manama', '', '0', '0', 1176636600, '5,412', 57, '308,238', 2004),
		(4, 'Spanien / Barcelona', '', '0', '0', 1179057600, '4,627', 66, '305,256', 1991),
		(5, 'Monaco / Monte Carlo', '', '0', '0', 1180267200, '3,340', 78, '260,520', 1950),
		(6, 'Kanada / Montreal', '', '0', '0', 1181494800, '4,361', 70, '305,270', 1978),
		(7, 'USA / Indianapolis', '', '0', '0', 1182099600, '4,192', 73, '306,016', 2000),
		(8, 'Frankreich / Magny-Cours', '', '0', '0', 1183291200, '4,411', 70, '308,586', 1991),
		(9, 'Großbritannien / Silverstone', '', '0', '0', 1183892400, '5,141', 60, '308,355', 1950),
		(10, 'Deutschland / Nürburgring', '', '0', '0', 1185105600, '5,148', 60, '308,863', 1984),
		(11, 'Ungarn / Budapest', '', '0', '0', 1186315200, '4,381', 70, '306,663', 1986),
		(12, 'Türkei / Istanbul', '', '0', '0', 1188129600, '5,338', 58, '309,356', 2005),
		(13, 'Italien / Monza', '', '0', '0', 1189339200, '5,793', 53, '306,720', 1950),
		(14, 'Belgien / Spa-Francorchamps', '', '0', '0', 1189944000, '6,976', 44, '306,927', 1950),
		(15, 'Japan / Fuji', '', '0', '0', 1191132000, '4,563', 67, '305,721', 1976),
		(16, 'China / Shanghai', '', '0', '0', 1191733200, '5,451', 56, '305,066', 2004),
		(17, 'Brasilien / São Paulo', '', '0', '0', 1192986000, '4,309', 71, '305,909', 1973)
		";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	// Todo: Check all teams against teamnames
	// Insert teams into formel_teams table
	$sql = 'INSERT INTO '.$table_prefix."formel_teams (team_id, team_name, team_img, team_car) VALUES 
		(1, 'McLaren Mercedes', '', ''),
		(2, 'Renault F1 Team', '', ''),
		(3, 'Scuderia Ferrari', '', ''),
		(4, 'Honda Racing F1 Team', '', ''),
		(5, 'Toyota Racing', '', ''),
		(6, 'BMW Sauber F1 Team', '', ''),
		(7, 'Red Bull Racing', '', ''),
		(8, 'Williams F1 Team', '', ''),
		(9, 'Scuderia Toro Rosso', '', ''),
		(10, 'Spyker MF1', '', ''),
		(11, 'Super Aguri F1', '', '')
		";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	// Todo: Check all drivers against their team memberships
	// Insert all drivers into formel_drivers table 
	$sql = 'INSERT INTO '.$table_prefix."formel_drivers (driver_id, driver_name, driver_img, driver_team) VALUES 
		(1, 'Alonso, Fernando', '', 1),
		(2, 'De la Rosa, Pedro', '', 1),
		(3, 'Paffett, Gary', '', 1),
		(4, 'Hamilton, Lewis', '', 1),
		(5, 'Kovalainen, Heikki', '', 2),
		(6, 'Fisichella, Giancarlo', '', 2),
		(7, 'Piquet Jr., Nelson', '', 2),
		(8, 'Zonta, Ricardo', '', 2),
		(9, 'Räikkönen, Kimi', '', 3),
		(10, 'Massa, Felipe', '', 3),
		(11, 'Badoer, Luca', '', 3),
		(12, 'Gené, Mark', '', 3),
		(13, 'Barrichello, Rubens', '', 4),
		(14, 'Button, Jenson', '', 4),
		(15, 'Klien, Christian', '', 4),
		(16, 'Rossiter, James', '', 4),
		(17, 'Heidfeld, Nick', '', 6),
		(18, 'Kubica, Robert', '', 6),
		(19, 'Vettel, Sebastian', '', 6),
		(20, 'Glock, Timo', '', 6),
		(21, 'Schumacher, Ralf', '', 5),
		(22, 'Trulli, Jarno', '', 5),
		(23, 'Montagny, Franck', '', 5),
		(24, 'Coulthard, David', '', 7),
		(25, 'Webber, Mark', '', 7),
		(26, 'Doornbos, Robert', '', 7),
		(27, 'Rosberg, Nico', '', 8),
		(28, 'Wurz, Alexander', '', 8),
		(29, 'Karthikeyan, Narain', '', 8),
		(30, 'Nakajima, Kazuki', '', 8),
		(31, 'Liuzzi, Vitantonio', '', 9),
		(32, 'Speed, Scott', '', 9),
		(33, 'Ammermüller, Michael', '', 7),
		(34, 'Albers, Christijan', '', 10),
		(35, 'Winkelhock, Markus', '', 10),
		(36, 'Sutil, Adrian', '', 10),
		(37, 'Mondini, Giorgio', '', 10),
		(38, 'Sato, Takuma', '', 11),
		(39, 'Davidson, Anthony', '', 11),
		(40, 'Van der Garde, Giedo', '', 11),
		(41, 'Yamamoto, Sakon', '', 11)
		";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	//Delete old permission set if exists from prior f1webtipp mod installations
	$sql = 'DELETE FROM '.$table_prefix."acl_options WHERE auth_option = 'a_formel_teams'";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	$sql = 'DELETE FROM '.$table_prefix."acl_options WHERE auth_option = 'a_formel_drivers'";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	$sql = 'DELETE FROM '.$table_prefix."acl_options WHERE auth_option = 'a_formel_settings'";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);

	$sql = 'DELETE FROM '.$table_prefix."acl_options WHERE auth_option = 'a_formel_races'";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	//Destroy the old permission chache
	$cache->purge();
	
	//Add new permission set to the module system
	$sql = 'INSERT INTO '.$table_prefix."acl_options (auth_option, is_global, is_local, founder_only) VALUES 
		('a_formel_settings', 1, 0, 0),
		('a_formel_drivers', 1, 0, 0),
		('a_formel_teams', 1, 0, 0),
		('a_formel_races', 1, 0, 0)
		";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
	
	//Destroy the old permission chache again to enable the new set :-)
	$cache->purge();

	$message = '<span style="color:green; font-weight: bold;font-size: 1.5em;">Formula 1 WebTip database successfully installed.</span><br />
				To finish installing this mod, edit all files according to the install.xml, then open up templates/prosilver.xml and follow those instructions.<br />
				When you are finished, go to the ACP and purge the cache.<br />
				<br />
				<span style="color:green; font-weight: bold;font-size: 1.5em;">Formel 1 WebTipp Datenbank erfolgreich installiert.</span><br />
				Um die Installation abzuschliessen befolge alle Anweisungen in der Install.xml, danach die templates/prosilver.xml und language/de.xml .<br />
				Wenn Du damit fertig bist, gehe in das ACP und leere den Cache.';
	trigger_error($message);
} 
else 
{
	$message = '<span style="color:green; font-weight: bold;font-size: 1.5em;">Formel 1 WebTipp MOD v0.1.24 (beta)</span><br />
				<br />
				English:<br />
				Script for automated Formula 1 WebTip table generation.<br />
				<br />
				<span style="color:red; font-weight: bold;">This procedure will erase all settings, drivers, teams, races and usertipps of any previous Formel 1 WebTipp installations!</span><br />
				Are you sure you want to continue? If so, then click on "Continue / Weiter"<br />
				<br />
				<br />
				German:<br />
				Script für die automatische Formel 1 WebTipp Tabellen Erstellung.<br />
				<br />
				<span style="color:red; font-weight: bold;">Diese Script wird alle F1 WebTipp Einstellungen, Fahrer, Team, Rennen und abgegebene Benutzer Tipps von vorherigen Installationen löschen!</span><br />
				Bist Du Dir absolut sicher ? Dann klicke auf "Continue / Weiter"<br />
				<br />
				';
	$message .= '%sContinue / Weiter%s ----- %sCancel / Abbrechen%s';
	$message  = sprintf($message, '<a href="'.append_sid("install_f1webtipp.$phpEx?install=continue").'" class="gen">', '</a>', '<a href="'.append_sid("index.$phpEx").'" class="gen">', '</a>');
	
	trigger_error( $message);
}
?>
</body>
</html>