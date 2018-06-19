<?php

/**
 * @package   angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */
defined('_AKEEBA') or die();

class AngieModelJoomlaSetup extends AngieModelBaseSetup
{
	public function getStateVariables()
	{
		// I have to extend the parent method to include FTP params, too
		$params = (array) parent::getStateVariables();

		$params = array_merge($params, $this->getFTPParamsVars());

		return (object) $params;
	}

	/**
	 * Gets the basic site parameters
	 *
	 * @return  array
	 */
	protected function getSiteParamsVars()
	{
		$jVersion = $this->container->session->get('jversion', '3.6.0');

		// Default tmp directory: tmp in the root of the site
		$defaultTmpPath = APATH_ROOT . '/tmp';
		// Default logs directory: logs in the administrator directory of the site
		$defaultLogPath = APATH_ADMINISTRATOR . '/logs';

		// If it's a Joomla! 1.x, 2.x or 3.0 to 3.5 site (inclusive) the default log dir is in the site's root
		if (!empty($jVersion) && version_compare($jVersion, '3.5.999', 'le'))
		{
			// I use log instead of logs because "logs" isn't writeable on many hosts.
			$defaultLogPath = APATH_ROOT . '/log';
		}

		$defaultSSL = 2;

		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')
		{
			$defaultSSL = 0;
		}

		$ret = array(
			'sitename'      => $this->getState('sitename', $this->configModel->get('sitename', 'Restored website')),
			'siteemail'     => $this->getState('siteemail', $this->configModel->get('mailfrom', 'no-reply@example.com')),
			'emailsender'   => $this->getState('emailsender', $this->configModel->get('fromname', 'Restored website')),
			'livesite'      => $this->getState('livesite', $this->configModel->get('live_site', '')),
			'cookiedomain'  => $this->getState('cookiedomain', $this->configModel->get('cookie_domain', '')),
			'cookiepath'    => $this->getState('cookiepath', $this->configModel->get('cookie_path', '')),
			'tmppath'       => $this->getState('tmppath', $this->configModel->get('tmp_path', $defaultTmpPath)),
			'logspath'      => $this->getState('logspath', $this->configModel->get('log_path', $defaultLogPath)),
			'force_ssl'     => $this->getState('force_ssl', $this->configModel->get('force_ssl', $defaultSSL)),
			'default_tmp'   => $defaultTmpPath,
			'default_log'   => $defaultLogPath,
			'site_root_dir' => APATH_ROOT,
		);

		// Let's cleanup the live site url
		require_once APATH_INSTALLATION . '/angie/helpers/setup.php';

		$ret['livesite'] = AngieHelperSetup::cleanLiveSite($ret['livesite']);

		if (version_compare($this->container->session->get('jversion'), '3.2', 'ge'))
		{
			$ret['mailonline'] = $this->getState('mailonline', $this->configModel->get('mailonline', '1'));
		}

		// Deal with tmp and logs path
		if (!@is_dir($ret['tmppath']))
		{
			$ret['tmppath'] = $defaultTmpPath;
		}
		elseif (!@is_writable($ret['tmppath']))
		{
			$ret['tmppath'] = $defaultTmpPath;
		}

		if (!@is_dir($ret['logspath']))
		{
			$ret['logspath'] = $defaultLogPath;
		}
		elseif (!@is_writable($ret['logspath']))
		{
			$ret['logspath'] = $defaultLogPath;
		}

		return $ret;
	}

	/**
	 * Gets the FTP connection parameters
	 *
	 * @return  array
	 */
	private function getFTPParamsVars()
	{
		$ret = array(
			'ftpenable' => $this->getState('enableftp', $this->configModel->get('ftp_enable', 0)),
			'ftphost'   => $this->getState('ftphost', $this->configModel->get('ftp_host', '')),
			'ftpport'   => $this->getState('ftpport', $this->configModel->get('ftp_port', 21)),
			'ftpuser'   => $this->getState('ftpuser', $this->configModel->get('ftp_user', '')),
			'ftppass'   => $this->getState('ftppass', $this->configModel->get('ftp_pass', '')),
			'ftpdir'    => $this->getState('ftpdir', $this->configModel->get('ftp_root', '')),
		);

		return $ret;
	}

	protected function getSuperUsersVars()
	{
		$ret = array();

		// Connect to the database
		try
		{
			$db = $this->getDatabase();
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Find the Super User groups
		try
		{
			$query = $db->getQuery(true)
			            ->select($db->qn('rules'))
			            ->from($db->qn('#__assets'))
			            ->where($db->qn('parent_id') . ' = ' . $db->q(0));
			$db->setQuery($query, 0, 1);
			$rulesJSON = $db->loadResult();
			$rules     = json_decode($rulesJSON, true);

			$rawGroups = $rules['core.admin'];
			$groups    = array();

			if (empty($rawGroups))
			{
				return $ret;
			}

			foreach ($rawGroups as $g => $enabled)
			{
				if ($enabled)
				{
					$groups[] = $db->q($g);
				}
			}

			if (empty($groups))
			{
				return $ret;
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user IDs of users belonging to the SA groups
		try
		{
			$query = $db->getQuery(true)
			            ->select($db->qn('user_id'))
			            ->from($db->qn('#__user_usergroup_map'))
			            ->where($db->qn('group_id') . ' IN(' . implode(',', $groups) . ')');
			$db->setQuery($query);
			$rawUserIDs = $db->loadColumn(0);

			if (empty($rawUserIDs))
			{
				return $ret;
			}

			$userIDs = array();

			foreach ($rawUserIDs as $id)
			{
				$userIDs[] = $db->q($id);
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user information for the Super Administrator users
		try
		{
			$query = $db->getQuery(true)
			            ->select(array(
				            $db->qn('id'),
				            $db->qn('username'),
				            $db->qn('email'),
			            ))->from($db->qn('#__users'))
			            ->where($db->qn('id') . ' IN(' . implode(',', $userIDs) . ')');
			$db->setQuery($query);
			$ret['superusers'] = $db->loadObjectList(0);
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		return $ret;
	}

	/**
	 * Apply the settings to the configuration.php file and the database
	 */
	public function applySettings()
	{
		// Apply the Super Administrator changes
		$this->applySuperAdminChanges();

		// Apply server config changes
		$this->applyServerconfigchanges();

		// Get the state variables and update the global configuration
		$stateVars = $this->getStateVariables();
		// -- General settings
		$this->configModel->set('sitename', $stateVars->sitename);
		$this->configModel->set('mailfrom', $stateVars->siteemail);
		$this->configModel->set('fromname', $stateVars->emailsender);
		$this->configModel->set('live_site', $stateVars->livesite);
		$this->configModel->set('cookie_domain', $stateVars->cookiedomain);
		$this->configModel->set('cookie_path', $stateVars->cookiepath);
		$this->configModel->set('tmp_path', $stateVars->tmppath);
		$this->configModel->set('log_path', $stateVars->logspath);
		$this->configModel->set('force_ssl', $stateVars->force_ssl);

		if (version_compare($this->container->session->get('jversion'), '3.2', 'ge'))
		{
			$this->configModel->set('mailonline', $stateVars->mailonline);
		}

		// -- FTP settings
		$this->configModel->set('ftp_enable', ($stateVars->ftpenable ? 1 : 0));
		$this->configModel->set('ftp_host', $stateVars->ftphost);
		$this->configModel->set('ftp_port', $stateVars->ftpport);
		$this->configModel->set('ftp_user', $stateVars->ftpuser);
		$this->configModel->set('ftp_pass', $stateVars->ftppass);
		$this->configModel->set('ftp_root', $stateVars->ftpdir);

		// -- Database settings
		$connectionVars = $this->getDbConnectionVars();
		$this->configModel->set('dbtype', $connectionVars->dbtype);
		$this->configModel->set('host', $connectionVars->dbhost);
		$this->configModel->set('user', $connectionVars->dbuser);
		$this->configModel->set('password', $connectionVars->dbpass);
		$this->configModel->set('db', $connectionVars->dbname);
		$this->configModel->set('dbprefix', $connectionVars->prefix);

		// Let's get the old secret key, since we need it to update encrypted stored data
		$oldsecret = $this->configModel->get('secret', '');
		$newsecret = $this->genRandomPassword(32);

		// -- Replace Two Factor Authentication first
		$this->updateEncryptedData($oldsecret, $newsecret);
		// -- Now replace the secret key
		$this->configModel->set('secret', $newsecret);
		$this->configModel->saveToSession();

		// Get the configuration.php file and try to save it
		$configurationPHP = $this->configModel->getFileContents();
		$filepath         = APATH_SITE . '/configuration.php';

		if (!@file_put_contents($filepath, $configurationPHP))
		{
			if ($this->configModel->get('ftp_enable', 0))
			{
				// Try with FTP
				$ftphost = $this->configModel->get('ftp_host', '');
				$ftpport = $this->configModel->get('ftp_port', '');
				$ftpuser = $this->configModel->get('ftp_user', '');
				$ftppass = $this->configModel->get('ftp_pass', '');
				$ftproot = $this->configModel->get('ftp_root', '');

				try
				{
					$ftp = AFtp::getInstance($ftphost, $ftpport, array('type' => FTP_AUTOASCII), $ftpuser, $ftppass);
					$ftp->chdir($ftproot);
					$ftp->write('configuration.php', $configurationPHP);
					$ftp->chmod('configuration.php', 0644);
				}
				catch (Exception $exc)
				{
					// Fail gracefully
					return false;
				}

				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * This method will update the data encrypted with the old secret key, encrypting it again using
	 * the new secret key
	 *
	 * @param   string $oldsecret Old secret key
	 * @param   string $newsecret New secret key
	 *
	 * @return  void
	 */
	private function updateEncryptedData($oldsecret, $newsecret)
	{
		$this->updateTFA($oldsecret, $newsecret);
	}

	private function updateTFA($oldsecret, $newsecret)
	{
		$this->container->session->set('tfa_warning', false);

		// There is no TFA in Joomla < 3.2
		$jversion = $this->container->session->get('jversion');

		if (version_compare($jversion, '3.2', 'lt'))
		{
			return;
		}

		$db = $this->getDatabase();

		$query = $db->getQuery(true)
		            ->select('COUNT(extension_id)')
		            ->from($db->qn('#__extensions'))
		            ->where($db->qn('type') . ' = ' . $db->q('plugin'))
		            ->where($db->qn('folder') . ' = ' . $db->q('twofactorauth'))
		            ->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$count = $db->setQuery($query)->loadResult();

		// No enabled plugin, there is no point in continuing
		if (!$count)
		{
			return;
		}

		$query = $db->getQuery(true)
		            ->select('*')
		            ->from($db->qn('#__users'))
		            ->where($db->qn('otpKey') . ' != ' . $db->q(''))
		            ->where($db->qn('otep') . ' != ' . $db->q(''));

		$users = $db->setQuery($query)->loadObjectList();

		// There are no users with TFA configured, let's stop here
		if (!$users)
		{
			return;
		}

		// Otherwise I'll get a blank page
		if (!defined('FOF_INCLUDED'))
		{
			define('FOF_INCLUDED', 1);
		}

		// I only included specific files, not the entire library, to minimise exposure to autoloader issues.
		$filesToInclude = array(
			'utils/phpfunc/phpfunc.php',
			'encrypt/randvalinterface.php',
			'encrypt/randval.php',
			'encrypt/aes/interface.php',
			'encrypt/aes/abstract.php',
			'encrypt/aes/mcrypt.php',
			'encrypt/aes/openssl.php',
			'encrypt/aes.php',
		);

		// Joomla! 3.6.2 and earlier doesn't have all these files (and I don't need them) so I am checking if they
		// exist before trying to include them.
		foreach ($filesToInclude as $file)
		{
			$filePath = APATH_LIBRARIES . '/fof/' . $file;

			if (file_exists($filePath))
			{
				include_once $filePath;
			}
		}

		// Does this host support AES?
		if (!FOFEncryptAes::isSupported())
		{
			// If not, set a flag, so we will display a big, fat warning in the finalize screen
			$this->container->session->set('tfa_warning', true);

			// Let's disable them
			$query = $db->getQuery(true)
			            ->update($db->qn('#__extensions'))
			            ->set($db->qn('enabled') . ' = ' . $db->q('0'))
			            ->where($db->qn('type') . ' = ' . $db->q('plugin'))
			            ->where($db->qn('folder') . ' = ' . $db->q('twofactorauth'));
			$db->setQuery($query)->execute();

			return;
		}

		foreach ($users as $user)
		{
			$update = (object) array(
				'id'     => $user->id,
				'otpKey' => '',
				'otep'   => ''
			);

			list($method, $otpKey) = explode(':', $user->otpKey, 2);

			$otpKey = $this->decryptTFAString($oldsecret, $otpKey);
			$otep   = $this->decryptTFAString($oldsecret, $user->otep);

			$update->otpKey = $method . ':' . $this->encryptTFAString($newsecret, $otpKey);
			$update->otep   = $this->encryptTFAString($newsecret, $otep);

			$db->updateObject('#__users', $update, 'id');
		}
	}

	private function applySuperAdminChanges()
	{
		// Get the Super User ID. If it's empty, skip.
		$id = $this->getState('superuserid', 0);

		if (!$id)
		{
			return false;
		}

		// Get the Super User email and password
		$email     = $this->getState('superuseremail', '');
		$password1 = $this->getState('superuserpassword', '');
		$password2 = $this->getState('superuserpasswordrepeat', '');

		// If the email is empty but the passwords are not, fail
		if (empty($email))
		{
			if (empty($password1) && empty($password2))
			{
				return false;
			}

			throw new Exception(AText::_('SETUP_ERR_EMAILEMPTY'));
		}

		// If the passwords are empty, skip
		if (empty($password1) && empty($password2))
		{
			return false;
		}

		// Make sure the passwords match
		if ($password1 != $password2)
		{
			throw new Exception(AText::_('SETUP_ERR_PASSWORDSDONTMATCH'));
		}

		// Let's load the password compatibility file
		require_once APATH_ROOT . '/installation/framework/utils/password.php';

		// Connect to the database
		$db = $this->getDatabase();

		// Create a new salt and encrypted password (legacy method for Joomla! 1.5.0 through 3.2.0)
		$salt      = $this->genRandomPassword(32);
		$crypt     = md5($password1 . $salt);
		$cryptpass = $crypt . ':' . $salt;

		// Get the Joomla! version. If none was detected we assume it's 1.5.0 (so we can use the legacy method)
		$jVersion = $this->container->session->get('jversion', '1.5.0');

		// If we're restoring Joomla! 3.2.2 or later which fully supports bCrypt then we need to get a bCrypt-hashed
		// password.
		if (version_compare($jVersion, '3.2.2', 'ge'))
		{
			// Create a new bCrypt-bashed password. At the time of this writing (July 2015) Joomla! is using a cost of 10
			$cryptpass = password_hash($password1, PASSWORD_BCRYPT, array('cost' => 10));
		}

		// Update the database record
		$query = $db->getQuery(true)
		            ->update($db->qn('#__users'))
		            ->set($db->qn('password') . ' = ' . $db->q($cryptpass))
		            ->set($db->qn('email') . ' = ' . $db->q($email))
		            ->where($db->qn('id') . ' = ' . $db->q($id));
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	private function genRandomPassword($length = 8)
	{
		$salt     = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len      = strlen($salt);
		$makepass = '';

		$stat = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = array(php_uname());
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i++)
		{
			$makepass .= $salt[mt_rand(0, $len - 1)];
		}

		return $makepass;
	}

	/**
	 * Tries to decrypt the TFA configuration, using a different method depending on the Joomla version.
	 *
	 * @param   string $secret          Site's secret key
	 * @param   string $stringToDecrypt Base64-encoded and encrypted, JSON-encoded information
	 *
	 * @return  string  Decrypted, but JSON-encoded, information
	 *
	 * @see     https://github.com/joomla/joomla-cms/pull/12497
	 */
	private function decryptTFAString($secret, $stringToDecrypt)
	{
		$jVersion = $this->container->session->get('jversion', '3.6.0');

		// Joomla 3.6.3 and earlier
		if (version_compare($jVersion, '3.6.3', 'le') || !class_exists('FOFEncryptAesMcrypt', true))
		{
			$aesDecryptor = new FOFEncryptAes($secret, 256, 'cbc');

			return $aesDecryptor->decryptString($stringToDecrypt);
		}

		// Joomla 3.6.4 or later. If it's raw JSON just return it, otherwise try to decrypt it first.
		$stringToDecrypt = trim($stringToDecrypt, "\0");

		if (!is_null(json_decode($stringToDecrypt, true)))
		{
			return $stringToDecrypt;
		}

		$openssl         = new FOFEncryptAes($secret, 256, 'cbc', null, 'openssl');
		$mcrypt          = new FOFEncryptAes($secret, 256, 'cbc', null, 'mcrypt');

		if ($openssl->isSupported())
		{
			$decryptedConfig = $openssl->decryptString($stringToDecrypt);
			$decryptedConfig = trim($decryptedConfig, "\0");

			if (!is_null(json_decode($decryptedConfig, true)))
			{
				return $decryptedConfig;
			}
		}

		if ($mcrypt->isSupported())
		{
			$decryptedConfig = $mcrypt->decryptString($stringToDecrypt);
			$decryptedConfig = trim($decryptedConfig, "\0");

			if (!is_null(json_decode($decryptedConfig, true)))
			{
				return $decryptedConfig;
			}
		}

		return '';
	}

	private function encryptTFAString($secret, $data)
	{
		$jVersion = $this->container->session->get('jversion', '3.6.0');

		// Do not encode the TFA config for Joomla! 3.6.4 or later
		if (version_compare($jVersion, '3.6.4', 'ge'))
		{
			return $data;
		}

		// Joomla 3.6.3 and earlier
		$aes = new FOFEncryptAes($secret, 256, 'cbc');

		return $aes->encryptString($data);
	}

	/**
	 * Applies server configuration changes (removing/renaming server configuration files)
	 */
	private function applyServerconfigchanges()
	{
		if ($this->input->get('removephpini'))
		{
			$this->removePhpini();
		}

		if ($this->input->get('replacehtaccess'))
		{
			$this->replaceHtaccess();
		}

		if ($this->input->get('replacewebconfig'))
		{
			$this->replaceWebconfig();
		}

		if ($this->input->get('removehtpasswd'))
		{
			$this->removeHtpasswd();
		}
	}

	/**
	 * Removes any user-defined PHP configuration files (.user.ini or php.ini)
	 *
	 * @return  bool
	 */
	private function removePhpini()
	{
		if (!$this->hasPhpIni())
		{
			return true;
		}

		// First of all let's remove any .bak file
		$files = array(
			'.user.ini.bak',
			'php.ini.bak',
			'administrator/.user.ini.bak',
			'administrator/php.ini.bak',
		);

		foreach ($files as $file)
		{
			if (file_exists(APATH_ROOT . '/' . $file))
			{
				// If I get any error during the delete, let's stop here
				if (!@unlink(APATH_ROOT . '/' . $file))
				{
					return false;
				}
			}
		}

		$renameFiles = array(
			'.user.ini',
			'php.ini',
			'administrator/.user.ini',
			'administrator/php.ini',
		);

		// Let's use the copy-on-write approach to rename those files.
		// Read the contents, create a new file, delete the old one
		foreach ($renameFiles as $file)
		{
			$origPath = APATH_ROOT . '/' . $file;

			if (!file_exists($origPath))
			{
				continue;
			}

			$contents = file_get_contents($origPath);

			// If I can't create the file let's continue with the next one
			if (!file_put_contents($origPath . '.bak', $contents))
			{
				if (!empty($contents))
				{
					continue;
				}
			}

			unlink($origPath);
		}

		return true;
	}

	/**
	 * Replaces the current version of the .htaccess file with the default one provided by Joomla.
	 * The original contents are saved in a backup file named htaccess.bak
	 *
	 * @return bool
	 */
	private function replaceHtaccess()
	{
		// If I don't have any .htaccess file there's no point on continuing
		if (!$this->hasHtaccess())
		{
			return true;
		}

		// Fetch the latest version from Github
		$downloader = new ADownloadDownload();
		$contents   = false;

		if ($downloader->getAdapterName())
		{
			$contents = $downloader->getFromURL('https://raw.githubusercontent.com/joomla/joomla-cms/staging/htaccess.txt');
		}

		// If a connection error happens or there are no download adapters we'll use our local copy of the file
		if (empty($contents))
		{
			$contents = file_get_contents(__DIR__ . '/serverconfig/htaccess.txt');
		}

		// First of all let's remove any backup file. Then copy the current contents of the .htaccess file in a
		// backup file. Finally delete the .htaccess file and write a new one with the default contents
		// If any of those steps fails we simply stop
		if (!@unlink(APATH_ROOT . '/htaccess.bak'))
		{
			return false;
		}

		$orig = file_get_contents(APATH_ROOT . '/.htaccess');

		if (!empty($orig))
		{
			if (!file_put_contents(APATH_ROOT . '/htaccess.bak', $orig))
			{
				return false;
			}
		}

		if (file_exists(APATH_ROOT . '/.htaccess'))
		{
			if (!@unlink(APATH_ROOT . '/.htaccess'))
			{
				return false;
			}
		}

		if (!file_put_contents(APATH_ROOT . '/.htaccess', $contents))
		{
			return false;
		}

		return true;
	}

	/**
	 * Replaces the current version of the web.config file with the default one provided by Joomla.
	 * The original contents are saved in a backup file named web.config.bak
	 *
	 * @return bool
	 */
	private function replaceWebconfig()
	{
		// If I don't have any web.config file there's no point on continuing
		if (!$this->hasWebconfig())
		{
			return true;
		}

		// Fetch the latest version from Github
		$downloader = new ADownloadDownload();
		$contents   = $downloader->getFromURL('https://raw.githubusercontent.com/joomla/joomla-cms/staging/web.config.txt');

		// If a connection error happens, let's use the local version of such file
		if ($contents === false)
		{
			$contents = file_get_contents(__DIR__.'/serverconfig/web.config.txt');
		}

		// First of all let's remove any backup file. Then copy the current contents of the web.config file in a
		// backup file. Finally delete the web.config file and write a new one with the default contents
		// If any of those steps fails we simply stop
		if (!@unlink(APATH_ROOT.'/web.config.bak'))
		{
			return false;
		}

		$orig = file_get_contents(APATH_ROOT.'/web.config');

		if (!file_put_contents(APATH_ROOT.'/web.config.bak', $orig))
		{
			return false;
		}

		if (!@unlink(APATH_ROOT.'/web.config'))
		{
			return false;
		}

		if (!file_put_contents(APATH_ROOT.'/web.config', $contents))
		{
			return false;
		}

		return true;
	}

	/**
	 * Removes password protection from /administrator folder
	 *
	 * @return bool
	 */
	private function removeHtpasswd()
	{
		if (!$this->hasHtpasswd())
		{
			return true;
		}

		$files = array(
			'.htaccess',
			'.htpasswd'
		);

		foreach ($files as $file)
		{
			if (file_exists(APATH_ROOT.'/administrator/'.$file))
			{
				@unlink(APATH_ROOT.'/administrator/'.$file);
			}
		}

		return true;
	}

	/**
	 * Checks if the current site has user-defined configuration files (ie php.ini or .user.ini etc etc)
	 *
	 * @return  bool
	 */
	public function hasPhpIni()
	{
		$files = array(
			'.user.ini',
			'.user.ini.bak',
			'php.ini',
			'php.ini.bak',
			'administrator/.user.ini',
			'administrator/.user.ini.bak',
			'administrator/php.ini',
			'administrator/php.ini.bak',
		);

		foreach ($files as $file)
		{
			if (file_exists(APATH_ROOT . '/' . $file))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the current site has .htaccess files
	 *
	 * @return bool
	 */
	public function hasHtaccess()
	{
		$files = array(
			'.htaccess',
			'htaccess.bak'
		);

		foreach ($files as $file)
		{
			if (file_exists(APATH_ROOT.'/'.$file))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the current site has webconfig files
	 *
	 * @return bool
	 */
	public function hasWebconfig()
	{
		$files = array(
			'web.config',
			'web.config.bak'
		);

		foreach ($files as $file)
		{
			if (file_exists(APATH_ROOT.'/'.$file))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the current site has htpasswd files
	 *
	 * @return bool
	 */
	public function hasHtpasswd()
	{
		$files = array(
			'administrator/.htaccess',
			'administrator/.htpasswd');

		foreach ($files as $file)
		{
			if (file_exists(APATH_ROOT.'/'.$file))
			{
				return true;
			}
		}

		return false;
	}
}
