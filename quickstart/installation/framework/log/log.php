<?php
/**
 * @package angifw
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer Framework
 */

defined('_AKEEBA') or die();

if(!defined('ANGIE_LOG_ERROR'))
{
	define('ANGIE_LOG_ERROR',		90);
	define('ANGIE_LOG_WARNING',		70);
	define('ANGIE_LOG_NOTICE',		50);
	define('ANGIE_LOG_INFO',		30);
	define('ANGIE_LOG_DEBUG',		10);
}

abstract class ALog
{
	public static function _($level, $message)
	{
		if (!defined('AKEEBA_DEBUG'))
		{
			return;
		}
		
		switch ($level)
		{
			case ANGIE_LOG_ERROR:
				$type = 'ERROR';
				break;

			case ANGIE_LOG_WARNING:
				$type = 'WARNING';
				break;

			case ANGIE_LOG_NOTICE:
				$type = 'NOTICE';
				break;

			case ANGIE_LOG_INFO:
				$type = 'INFO';
				break;

			case ANGIE_LOG_DEBUG:
			default:
				$type = 'DEBUG';
				break;
		}
		
		$timestring = gmdate('Y/m/d H:i:s');
		$line = str_pad($type, 8, ' ') . '| ' . $timestring . ' | '
				. str_replace("\n", ' ', $message) . "\n";
		
		$fp = @fopen(APATH_INSTALLATION . '/log.txt', 'at');
		if ($fp !== false)
		{
			@fputs($fp, $line);
			@fclose($fp);
		}
	}
}
