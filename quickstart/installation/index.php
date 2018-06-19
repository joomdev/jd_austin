<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Akeeba Next Generation Installer For Joomla! - Main file
 */

// Sanity check
if(__DIR__ == '__DIR__')
{
	die('Akeeba Next Generation Installer For Joomla! requires PHP 5.3 or later');
}

// Define ourselves as a parent file
define('_AKEEBA', 1);
// Required by Joomla! files
define('_JEXEC', 1);
// Required for lang strings. This is what happens when you use Joomla! core code.
define('_QQ_', '&quot;');

// Debug
// define('AKEEBA_DEBUG', 1);
if (defined('AKEEBA_DEBUG'))
{
	error_reporting(E_ALL | E_NOTICE | E_DEPRECATED);
	ini_set('display_errors', 1);
}

// Load the required INI parser
require_once __DIR__ . '/angie/helpers/ini.php';

// Load the framework autoloader
require_once __DIR__ . '/framework/autoloader.php';
// Load PSR-4 autoloader
require_once __DIR__ . '/framework/Autoloader/Autoloader.php';

\Angie\Autoloader\Autoloader::getInstance()->addMap('Symfony\\', array(realpath(__DIR__ . '/framework/Symfony')));

require_once __DIR__ . '/defines.php';

// Load Angie autoloader
require_once __DIR__. '/angie/autoloader.php';

// Required by the Joomla! CMS version file (mind. blown!)
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', APATH_LIBRARIES);
}

try
{
	if (@file_exists(__DIR__ . '/platform/defines.php'))
	{
		require_once __DIR__ . '/platform/defines.php';
	}

    if(!defined('ANGIE_INSTALLER_NAME'))
    {
        define('ANGIE_INSTALLER_NAME', 'Generic');
    }

    $container = new AContainer(array(
        'application_name'	=> 'angie'
    ));

	// Create the application
	$application = $container->application;

	// Initialise the application
	$application->initialise();

	// Dispatch the application
	$application->dispatch();

	// Render
	$application->render();

	// Clean-up and shut down
	$application->close();
}
catch (Exception $exc)
{
	$filename = null;
	if (isset($application))
	{
		if ($application instanceof AApplication)
		{
			$template = $application->getTemplate();
			if (file_exists(APATH_THEMES . '/' . $template . '/error.php'))
			{
				$filename = APATH_THEMES . '/' . $template . '/error.php';
			}
		}
	}
	if (!is_null($filename))
	{
		@ob_start();
	}
	// An uncaught application error occurred
	echo "<h1>Application Error</h1>\n";
	echo "<p>Please submit the following error message and trace in its entirety when requesting support</p>\n";
	echo "<div class=\"alert alert-error\">" . get_class($exc) . ' &mdash; ' . $exc->getMessage() . "</div>\n";
	echo "<pre class=\"well\">\n";
	echo $exc->getTraceAsString();
	echo "</pre>\n";
	if (!is_null($filename))
	{
		$error_message = @ob_get_clean();
		include $filename;
	}
}
