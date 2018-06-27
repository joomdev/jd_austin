<?php
/**
 * @version   $Id: Image.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Item_Image
{
	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var string
	 */
	protected $source;

	/**
	 * @var string
	 */
	protected $height = '100%';

	/**
	 * @var string
	 */
	protected $width = '100%';

	/**
	 * @var string
	 */
	protected $caption;

	/**
	 * @var string
	 */
	protected $alttext;

	/**
	 * @var string
	 */
	protected $filepath;


	/**
	 * @static
	 *
	 * @param $json
	 *
	 * @return bool|null|RokSprocket_Item_Image
	 */
	public static function createFromJSON($json)
	{
		$container = RokCommon_Service::getContainer();
		/** @var $logger RokCommon_Logger */
		$logger = $container->roksprocket_logger;

		$image = null;
		if (!empty($json)) {
			$image       = new self();
			$json_decode = null;
			try {
				$json_decode = RokCommon_JSON::decode(str_replace("'", '"', str_replace('\\', '', $json)));
			} catch (RokCommon_JSON_Exception $jse) {
				$logger->warning("Unable to decode image JSON : " . $json);
			}
			if (!$json_decode) return false;

			$image->source = $json_decode->path;
			$size          = array(0, 0);
			$image_source  = $json_decode->path;
			if (!$container->platforminfo->isLinkExternal($image_source)) {
				$size_check_path = $container->platforminfo->getPathForUrl($image_source);
				$image->filepath = $size_check_path;
				/// if the image source doesnt start with / assume its relative to root
			} else {
				$size_check_path = $image_source;
			}
			if (!empty($image->filepath) && file_exists($image->filepath)) {
				$size = @getimagesize($size_check_path);
				if (!empty($size)) {
					$size[0] = ($size[0]) ? $size[0] : '100%';
					$size[1] = ($size[1]) ? $size[1] : '100%';
				}
				$image->width  = $size[0];
				$image->height = $size[1];
			}
		}
		return $image;
	}

	protected function setSizeFromSource()
	{
		$container    = RokCommon_Service::getContainer();
		$image_source = $this->source;
		if (!$container->platforminfo->isLinkExternal($image_source)) {
			$size_check_path = $container->platforminfo->getPathForUrl($image_source);
			$this->filepath  = $size_check_path;
			/// if the image source doesnt start with / assume its relative to root
		} else {
			$size_check_path = $image_source;
		}

		if (!empty($this->filepath) && file_exists($this->filepath)) {
			$size = @getimagesize($size_check_path);
			if ($size !== false) {
				$this->width  = $size[0];
				$this->height = $size[1];
			}
		}
	}

	/**
	 * @param $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 * @return mixed
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @param      $url
	 * @param bool $autosetsize
	 */
	public function setSource($url, $autosetsize = true)
	{
		$container = RokCommon_Service::getContainer();

		if (!$container->platforminfo->isLinkExternal($url)) {
			$url = $container->platforminfo->getUrlForPath($container->platforminfo->getPathForUrl($url));
		}
		$this->source = $url;

		if ($autosetsize) {
			$this->setSizeFromSource();
		}
	}

	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @return mixed
	 */
	public function getWidth()
	{
		return $this->width;
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

	/**
	 * @param $alttext
	 */
	public function setAlttext($alttext)
	{
		$this->alttext = $alttext;
	}

	/**
	 * @return mixed
	 */
	public function getAlttext()
	{
		return $this->alttext;
	}

	/**
	 * @param $caption
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
	}

	/**
	 * @return mixed
	 */
	public function getCaption()
	{
		return $this->caption;
	}


	public function resize($width = 0, $height = 0)
	{
		$container = RokCommon_Service::getContainer();
		/** @var $platformHelper RokSprocket_PlatformHelper */
		$platformHelper = $container->roksprocket_platformhelper;

		if (!empty($this->filepath) && file_exists($this->filepath)) {
			try {
				$file_parts    = pathinfo($this->filepath);
				$file_path_md5 = md5($this->filepath);
				$new_path      = sprintf('%s/%s_%d_%d.%s', $platformHelper->getCacheDir(), $file_path_md5, $height, $width, $file_parts['extension']);
				$new_source    = sprintf('%s/%s_%d_%d.%s', $platformHelper->getCacheUrl(), $file_path_md5, $height, $width, $file_parts['extension']);
				if (!file_exists($new_path)) {
					$new_path_parts = pathinfo($new_path);
					if (!is_dir($new_path_parts['dirname']) && !is_file($new_path_parts['dirname'])) {
						$origmask = @umask(0);
						if (!$ret = @mkdir($new_path_parts['dirname'], 0755, true)) {
							@umask($origmask);
							throw new RokCommon_Image_Exception(rc__('Unable to create directory %s.', $new_path_parts['dirname']));
						}
						@umask($origmask);
					}
					$resized_image = RokCommon_Image::create($this->filepath);
					$resized_image->setOption(RokCommon_Image_ImageType::OPTION_NAME_RESIZE_UP, true);
					$resized_image->resize($width, $height)->save($new_path);
				}
				$this->setSource($new_source);
			} catch (RokCommon_Image_Exception $rcie) {

			}
		}
	}
}
