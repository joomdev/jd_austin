<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer For Joomla!
 */

defined('_AKEEBA') or die();

class AngieApplication extends AApplication
{
	public function initialise()
	{
		// Load the version file
		require_once APATH_INSTALLATION . '/version.php';

        // Load text callbacks
		if (file_exists(APATH_INSTALLATION.'/angie/platform/iniprocess.php'))
		{
			require_once APATH_INSTALLATION.'/angie/platform/iniprocess.php';

			AText::addIniProcessCallback(array('IniProcess', 'processLanguageIniFile'));
		}
        elseif (file_exists(APATH_INSTALLATION.'/platform/iniprocess.php'))
        {
            require_once APATH_INSTALLATION.'/platform/iniprocess.php';

            AText::addIniProcessCallback(array('IniProcess', 'processLanguageIniFile'));
        }
	}
}
