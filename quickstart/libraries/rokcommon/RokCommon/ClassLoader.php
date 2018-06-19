<?php
/**
 * @version   $Id: ClassLoader.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Based on
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * http://groups.google.com/group/php-standards/web/final-proposal
 *
 *     // Example which loads classes for the Doctrine Common package in the
 *     // Doctrine\Common namespace.
 *     $classLoader = new SplClassLoader('Doctrine\Common', '/path/to/doctrine');
 *     $classLoader->register();
 *
 * @author    Jonathan H. Wage <jonwage@gmail.com>
 * @author    Roman S. Borschel <roman@code-factory.org>
 * @author    Matthew Weier O'Phinney <matthew@zend.com>
 * @author    Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author    Fabien Potencier <fabien.potencier@symfony-project.org>
 * @author    Juozas Kaziukenas <juozas@juokaz.com>
 */

if (!class_exists('RokCommon_ClassLoader', false)) {

	/**
	 *
	 */
	interface RokCommon_ClassLoader_ILoader
	{
		/**
		 *
		 */
		const FILE_EXTENSION = '.php';
		/**
		 *
		 */
		const DEFAULT_FINDER_PRIORITY = 10;
		/**
		 *
		 */
		const DEFAULT_PATH_PRIORITY = 10;

		/**
		 * @abstract
		 *
		 * @param  string $class the class name to look for and load
		 *
		 * @return bool True if the class was found and loaded.
		 */
		public function loadClass($class);

		/**
		 * @abstract
		 *
		 * @param array $finders
		 *
		 * @return mixed
		 */
		public function setFinders($finders = array());

		/**
		 * @abstract
		 *
		 * @param int $priority
		 *
		 * @return mixed
		 */
		public function activate($priority = RokCommon_ClassLoader::DEFAULT_LOADER_PRIORITY);
	}

	/**
	 *
	 */
	interface RokCommon_ClassLoader_IFinder
	{
		/**
		 * @abstract
		 *
		 * @param  string $class the class name to look for
		 *
		 * @return string|bool the path to the file or false if not found
		 */
		public function find($class);
	}

	/**
	 *
	 */
	class RokCommon_ClassLoader_BootStrapLoader
	{
		/**
		 * @param string $className the class name to load
		 *
		 * @return bool
		 */
		public static function loadClass($className)
		{
			if (stripos($className, 'RokCommon') === 0) {
				$commonsPath    = realpath(dirname(__FILE__) . '/..');
				$fileName       = str_replace('_', DIRECTORY_SEPARATOR, $className) . ".php";
				$full_file_path = $commonsPath . DIRECTORY_SEPARATOR . $fileName;
				if (file_exists($full_file_path) && is_readable($full_file_path)) {
					require($full_file_path);
				}
				return true;
			}
			return false;
		}
	}

	/**
	 *
	 */
	class RokCommon_ClassLoader_Exception extends Exception
	{
	}

	/**
	 * @todo       find references to this and remove
	 * @deprecated use RokCommon_ClassLoader_Exception instead
	 */
	class RokCommon_Loader_Exception extends RokCommon_ClassLoader_Exception
	{

	}


	/**
	 * @package RokCommon
	 */
	class RokCommon_ClassLoader
	{
		/**
		 *
		 */
		const DEFAULT_LOADER_PRIORITY = 10;
		/**
		 *
		 */
		const DEFAULT_PATH_PRIORITY = 10;

		/**
		 * @var RokCommon_ClassLoader
		 */
		protected static $_instance;

		/**
		 * @var array
		 */
		protected $loaders = array();

		/**
		 * @var bool
		 */
		protected $bootstrap_setup = false;

		/**
		 * @var bool
		 */
		protected $bootstrap_used = false;


		/**
		 * @static
		 * @return RokCommon_ClassLoader
		 */
		public static function getInstance()
		{
			if (!isset(self::$_instance)) {
				self::$_instance = new RokCommon_ClassLoader();
				self::$_instance->setupBootstrap();
			}
			return self::$_instance;
		}


		/**
		 * @static
		 *
		 * @param RokCommon_ClassLoader_ILoader $loader     the instance of the loader to register
		 * @param int                           $priority   priority of the loader
		 *
		 * @throws RokCommon_ClassLoader_Exception
		 * @internal   param string $loaderName name to register the loader under.
		 * @return void
		 * @deprecated use the container to get the classloader and register on it
		 */
		public static function registerLoader(RokCommon_ClassLoader_ILoader &$loader, $priority = self::DEFAULT_LOADER_PRIORITY)
		{
			self::getInstance();
			$container = RokCommon_Service::getContainer();
			$container->classloader->addLoader($loader, $priority);
		}

		/**
		 * Convenience function to add a path to the default loader
		 * @static
		 *
		 * @param string $path      the path to add to the default loader
		 *
		 * @deprecated use the container to set on the proper path
		 * @return void
		 */
		public static function addPath($path)
		{
			$container = RokCommon_Service::getContainer();
			$container->setParameter('classloader.classpath.' . self::DEFAULT_PATH_PRIORITY, array($path));
		}


		/**
		 * Returns a reference to the named loader.
		 * @static
		 *
		 * @param  string $loaderName the named loader to return
		 *
		 * @throws RokCommon_ClassLoader_Exception
		 * @return RokCommon_ClassLoader_ILoader|bool FALSE if no loader found with that name
		 */
		public static function &getLoader($loaderName)
		{
			$container = RokCommon_Service::getContainer();
			if (!$container->hasService($loaderName)) {
				throw new RokCommon_ClassLoader_Exception('Loader ' . $loaderName . ' does not exists');
			}
			return $container->getService($loaderName);
		}


		/**
		 * See if the loader is registered
		 * @static
		 *
		 * @param  $loaderName
		 *
		 * @return bool
		 * @deprecated use the container and check if there is a service
		 */
		public static function isLoaderRegistered($loaderName)
		{
			$container = RokCommon_Service::getContainer();
			return $container->hasService($loaderName);
		}

		/**
		 *
		 */
		protected function __construct()
		{
			//$this->register();
		}

		/**
		 *
		 */
		public function __destruct()
		{
			$this->unregister();
		}

		/**
		 *
		 */
		private function setupBootstrap()
		{
			if (!$this->bootstrap_setup && !$this->bootstrap_used) {
				//$this->bootstrap = new RokCommon_ClassLoader_BootStrapLoader();
				spl_autoload_register(array('RokCommon_ClassLoader_BootStrapLoader', 'loadClass'));
				$this->bootstrap_setup = true;
			}
		}

		/**
		 *
		 */
		private function cleanupBootstrap()
		{
			if ($this->bootstrap_setup && !$this->bootstrap_used) {
				spl_autoload_unregister(array('RokCommon_ClassLoader_BootStrapLoader', 'loadClass'));
				$this->bootstrap_setup = false;
			}
		}

		/**
		 * @param RokCommon_ClassLoader_ILoader $loader
		 * @param int                           $priority
		 */
		public function setDefaultLoader(RokCommon_ClassLoader_ILoader $loader, $priority = self::DEFAULT_LOADER_PRIORITY)
		{
			if ($this->bootstrap_setup && !$this->bootstrap_used) {
				$this->cleanupBootstrap();
				$this->bootstrap_used = true;
			}
			$this->addLoader($loader, $priority);
			$this->register();
		}

		/**
		 * @param RokCommon_ClassLoader_ILoader  $loader
		 * @param int                            $priority
		 *
		 * @throws RokCommon_ClassLoader_Exception
		 * @return bool
		 */
		public function addLoader(RokCommon_ClassLoader_ILoader &$loader, $priority = self::DEFAULT_LOADER_PRIORITY)
		{
			if ($this->bootstrap_setup && !$this->bootstrap_used) {
				throw new RokCommon_ClassLoader_Exception('The default loader has not been set');
			}
			if (!$this->checkIfLoaderAlreadyAdded($loader)) {
				$this->loaders[$priority][] =& $loader;
				ksort($this->loaders);
			} else {
				throw new RokCommon_ClassLoader_Exception('Loader already added');
			}
		}

		/**
		 * @param RokCommon_ClassLoader_ILoader $removingloader
		 */
		public function removeLoader(RokCommon_ClassLoader_ILoader &$removingloader)
		{
			foreach ($this->loaders as $priority => $priority_loaders) {
				foreach ($priority_loaders as $id => &$loader) {
					if ($loader === $removingloader) {
						unset($this->loaders[$priority][$id]);
					}
				}
			}
		}

		/**
		 * @param RokCommon_ClassLoader_ILoader $checking_loader
		 *
		 * @return bool
		 */
		protected function checkIfLoaderAlreadyAdded(RokCommon_ClassLoader_ILoader $checking_loader)
		{
			foreach ($this->loaders as $priority_loaders) {
				foreach ($priority_loaders as &$loader) {
					if ($loader === $checking_loader) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Installs this class loader on the SPL autoload stack.
		 */
		public function register()
		{
			$current_functions  = spl_autoload_functions();
			$prepend            = true;
			$already_registered = false;
			if ($current_functions === false) {
				$prepend = false;
			} elseif (is_array($current_functions) && count($current_functions) == 0) {
				// prepend original autoloader
				if (function_exists('__autoload')) {
					spl_autoload_register('__autoload');
				} else {
					$prepend = false;
				}
			} else {
				if (in_array(array($this, 'loadClass'), $current_functions)) {
					$already_registered = true;
				}
			}
			if (!$already_registered) {
				if ($prepend && version_compare(PHP_VERSION, '5.3.0') >= 0) {
					spl_autoload_register(array($this, 'loadClass'), true, true);
				} else {
					spl_autoload_register(array($this, 'loadClass'));
				}
			}
		}

		/**
		 * Uninstalls this class loader from the SPL autoloader stack.
		 */
		public function unregister()
		{
			spl_autoload_unregister(array($this, 'loadClass'));
		}

		/**
		 * Loads the given class or interface.
		 *
		 * @param string $className The name of the class to load.
		 *
		 * @return void
		 */
		public function loadClass($className)
		{
			if (!empty($this->loaders)) {
				foreach ($this->loaders as $priority => $priorityLoaders) {
					/** @var $priorityLoaders RokCommon_ClassLoader_ILoader[] */
					foreach ($priorityLoaders as $loaderName => $loader) {
						if ($loader->loadClass($className)) break;
					}
				}
			}
		}
	}
}

