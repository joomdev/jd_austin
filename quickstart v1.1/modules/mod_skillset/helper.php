<?php
# @Author: SPEDI srl
# @Date:   23-01-2018
# @Email:  sviluppo@spedi.it
# @Last modified by:   SPEDI srl
# @Last modified time: 23-01-2018
# @License: GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
# @Copyright: Copyright (C) SPEDI srl

defined('_JEXEC') or die;

/**
 * Helper for mod_spskillset
 *
 * @since 1.0
 */
class ModSkillsetHelper
{
	/**
	 * Conversione da esadecimale a rgb
	 * @param string $hexcode
	 * @return string $rgb
	 */
	public static function hexToRGB($hexcode){
    $redhex  = substr( $hexcode, 1, 2 );
  	$greenhex = substr( $hexcode, 3, 2 );
  	$bluehex = substr( $hexcode, 5, 2 );

  	$r = hexdec($redhex);
  	$g = hexdec($greenhex);
  	$b = hexdec($bluehex);

		$rgb = $r.','.$g.','.$b;

  	return $rgb;
	}
}
