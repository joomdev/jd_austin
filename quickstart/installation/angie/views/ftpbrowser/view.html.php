<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

class AngieViewFtpbrowser extends AView
{
	public function onBeforeMain()
	{
		try
		{
			$stuff = $this->getModel()->getListingAndCrumbs();
			
			$uri = clone AUri::getInstance();
			$queryParts = $uri->getQuery(true);
			unset($queryParts['directory']);
			$uri->setQuery($queryParts);
			
			$this->ftppath = $stuff['path'];
			$this->crumbs = $stuff['crumbs'];
			$this->directories = $stuff['directories'];
			$this->badFTP = false;
			$this->ftpError = '';
			$this->baseURL = $uri->toString();
		}
		catch (Exception $exc)
		{
			$this->badFTP = true;
			$this->ftpError = $exc->getMessage();
		}
		
		return true;
	}
}
