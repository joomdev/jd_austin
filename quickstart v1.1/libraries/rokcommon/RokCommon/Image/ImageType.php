<?php
/**
 * @version   $Id: ImageType.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Derived from
 *
 * PhpThumb GD Thumb Class Definition File
 * 
 * This file contains the definition for the RokCommon_Image_ImageType_GD object
 * 
 * PHP Version 5 with GD 2.0+
 * PhpThumb : PHP Thumb Library <http://phpthumb.gxdlabs.com>
 * Copyright (c) 2009, Ian Selby/Gen X Design
 * 
 * Author(s): Ian Selby <ian@gen-x-design.com>
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author Ian Selby <ian@gen-x-design.com>
 * @copyright Copyright (c) 2009 Gen X Design
 * @link http://phpthumb.gxdlabs.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 3.0
 * @package PhpThumb
 * @filesource
 */

/**
 * RokCommon_Image_ImageType_GD Class Definition
 * 
 * This is the GD Implementation of the PHP Thumb library.
 * 
 * @package PhpThumb
 * @subpackage Core
 */
interface RokCommon_Image_ImageType
{
	const OPTION_NAME_RESIZE_UP = 'resizeUp';
	const OPTION_NAME_JPEG_QUALITY = 'jpegQuality';
	const OPTION_NAME_CORRENT_PERMISSIONS = 'correctPermissions';
	const OPTION_NAME_PRESERVE_ALPHA = 'preserveAlpha';
	const OPTION_NAME_ALPHA_MASK_COLOR = 'alphaMaskColor';
	const OPTION_NAME_PRESERVE_TRANSPARENT = 'preserveTransparency';
	const OPTION_NAME_TRANSPARENCY_MASK_COLOR ='transparencyMaskColor';
	/**
	 * Class Constructor
	 *
	 * @return \RokCommon_Image_ImageType
	 *
	 * @param string $fileName
	 * @param array  $options
	 * @param bool   $isDataStream
	 *
	 * @throws RokCommon_Image_Exception
	 */
	public function __construct($fileName, array $options = array(), $isDataStream = false);
	
	##############################
	# ----- API FUNCTIONS ------ #
	##############################
	
	/**
	 * Resizes an image to be no larger than $maxWidth or $maxHeight
	 * 
	 * If either param is set to zero, then that dimension will not be considered as a part of the resize.
	 * Additionally, if $this->options['resizeUp'] is set to true (false by default), then this function will
	 * also scale the image up to the maximum dimensions provided.
	 * 
	 * @param int $maxWidth The maximum width of the image in pixels
	 * @param int $maxHeight The maximum height of the image in pixels
	 * @return RokCommon_Image_ImageType
	 */
	public function resize ($maxWidth = 0, $maxHeight = 0);

	/**
	 * Adaptively Resizes the Image
	 * 
	 * This function attempts to get the image to as close to the provided dimensions as possible, and then crops the 
	 * remaining overflow (from the center) to get the image to be the size specified
	 * 
	 * @param int $width
	 * @param int $height
	 * @return RokCommon_Image_ImageType
	 */
	public function adaptiveResize ($width, $height);
	
	/**
	 * Resizes an image by a given percent uniformly
	 * 
	 * Percentage should be whole number representation (i.e. 1-100)
	 * 
	 * @param int $percent
	 * @return RokCommon_Image_ImageType
	 */
	public function resizePercent ($percent = 0);

	
	/**
	 * Crops an image from the center with provided dimensions
	 * 
	 * If no height is given, the width will be used as a height, thus creating a square crop
	 * 
	 * @param int $cropWidth
	 * @param int $cropHeight
	 * @return RokCommon_Image_ImageType
	 */
	public function cropFromCenter ($cropWidth, $cropHeight = null);

	
	/**
	 * Vanilla Cropping - Crops from x,y with specified width and height
	 * 
	 * @param int $startX
	 * @param int $startY
	 * @param int $cropWidth
	 * @param int $cropHeight
	 * @return RokCommon_Image_ImageType
	 */
	public function crop ($startX, $startY, $cropWidth, $cropHeight);

	/**
	 * Rotates image either 90 degrees clockwise or counter-clockwise
	 * 
	 * @param string $direction
	 * @retunrn RokCommon_Image_ImageType
	 */
	public function rotateImage ($direction = 'CW');


	/**
	 * Rotates image specified number of degrees
	 *
	 * @param int $degrees
	 * @param int $bgColor
	 *
	 * @return RokCommon_Image_ImageType
	 */
	public function rotateImageNDegrees ($degrees, $bgColor = 0);
	
	/**
	 * Shows an image
	 * 
	 * This function will show the current image by first sending the appropriate header
	 * for the format, and then outputting the image data. If headers have already been sent, 
	 * a runtime exception will be thrown 
	 * 
	 * @param bool $rawData Whether or not the raw image stream should be output
	 * @return RokCommon_Image_ImageType
	 */
	public function show ($rawData = false);
	
	/**
	 * Returns the Working Image as a String
	 *
	 * This function is useful for getting the raw image data as a string for storage in
	 * a database, or other similar things.
	 *
	 * @return string
	 */
	public function getImageAsString ();

	/**
	 * Saves an image
	 * 
	 * This function will make sure the target directory is writeable, and then save the image.
	 * 
	 * If the target directory is not writeable, the function will try to correct the permissions (if allowed, this
	 * is set as an option ($this->options['correctPermissions']).  If the target cannot be made writeable, then a
	 * RuntimeException is thrown.
	 * 
	 * TODO: Create additional paramter for color matte when saving images with alpha to non-alpha formats (i.e. PNG => JPG)
	 * 
	 * @param string $fileName The full path and filename of the image to save
	 * @param string $format The format to save the image in (optional, must be one of [GIF,JPG,PNG]
	 * @return RokCommon_Image_ImageType
	 */
	public function save ($fileName, $format = null);

	
	#################################
	# ----- GETTERS / SETTERS ----- #
	#################################



	/**
	 * Sets $this->options to $options
	 * 
	 * @param array $options
	 */
	public function setOptions (array $options = array());


	public function setOption($name, $value);

	/**
	 * Returns $options.
	 *
	 * @return array an array of all set options
	 */
	public function getOptions ();

	/**
	 * Get the value of a named option
	 *
	 * @abstract
	 * @param string $name the name of the option to get the value for
	 *
	 * @return mixed|null the value of the passed in option or null if it doesn't exist
	 */
	public function getOption($name);
	

	/**
	 * Gets the height of the current image
	 *
	 * @abstract
	 * @return int the height in pixels of the current image
	 */
	public function getHeight();

	/**
	 * Gets the width of the current image
	 *
	 * @abstract
	 * @return int the width of the current image in pixels
	 */
	public function getWidth();

	/**
	 * Returns $fileName.
	 *
	 * @see ThumbBase::$fileName
	 */
	public function getFileName();

	/**
	 * Returns $format.
	 *
	 * @see ThumbBase::$format
	 */
	public function getFormat();
}