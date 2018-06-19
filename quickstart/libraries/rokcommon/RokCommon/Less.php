<?php
/**
 * @version   $Id: Less.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Less
{
	const CACHE_GROUP = 'rokcommon_less';

	public static function compile($lessfile, $outfile, $force = false)
	{
		try {
			$container = RokCommon_Service::getContainer();

			/** @var $cache_handler RokCommon_ICache */
			$cache_handler = $container->getService('cache');
			$less_file_md5 = md5($lessfile);

			if ($force || !$cache = $cache_handler->get(self::CACHE_GROUP, $less_file_md5)) {
				$cache = $lessfile;
			}

			$new_cache = RokCommon_Less_Compiler::cexecute($cache);
			if (!is_array($cache) || $new_cache['updated'] > $cache['updated']) {
				$cache_handler->set(self::CACHE_GROUP, $less_file_md5, $new_cache);
				$tmp_ouput_file = tempnam(dirname($outfile), 'rokcommon_less');
				file_put_contents($tmp_ouput_file, $new_cache['compiled']);

				// Do the messed up file renaming for windows
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					$move_old_file_name = tempnam(dirname($outfile), 'rokcommon_less');
					if (is_file($outfile)) @rename($outfile, $move_old_file_name);
					@rename($tmp_ouput_file, $outfile);
					@unlink($move_old_file_name);
				} else {
					@rename($tmp_ouput_file, $outfile);
				}


			}
		} catch (Exception $ex) {
			echo "lessphp fatal error: " . $ex->getMessage();
		}
	}
}
