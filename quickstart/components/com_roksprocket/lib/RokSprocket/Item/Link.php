<?php
/**
 * @version   $Id: Link.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Item_Link
{
	/**
	 * @var string;
	 */
	protected $url = '';

	/**
	 * @var string;
	 */
	protected $text;

	/**
	 * @var
	 */
	protected $identifier;

	public function __construct($url = null)
	{
		$this->url = $url;
	}

	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param $identifier
	 */
	public function setIdentifier($identifier)
	{
		$this->identifier = $identifier;
	}

	/**
	 * @return mixed
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}


	public function __toString()
	{
		return $this->url;
	}

}
