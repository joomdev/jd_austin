<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

$scripts = $this->getScripts();
$scriptDeclarations = $this->getScriptDeclarations();
$styles = $this->getStyles();
$styleDeclarations = $this->getStyleDeclarations();

// Scripts before the template ones
if(!empty($scripts)) foreach($scripts as $url => $params)
{
	if($params['before'])
	{
		echo "\t<script type=\"{$params['mime']}\" src=\"$url\"></script>\n";
	}
}

// Template scripts
?>
<script type="text/javascript" src="template/angie/js/jquery.js"></script>
<script type="text/javascript" src="template/angie/js/jquery.simulate.js"></script>
<script type="text/javascript" src="template/angie/js/bootstrap.min.js"></script>
<?php
// Scripts after the template ones
if(!empty($scripts)) foreach($scripts as $url => $params)
{
	if(!$params['before'])
	{
		echo "\t<script type=\"{$params['mime']}\" src=\"$url\"></script>\n";
	}
}

// Script declarations
if(!empty($scriptDeclarations)) foreach($scriptDeclarations as $type => $content)
{
	echo "\t<script type=\"$type\">\n$content\n</script>";
}

// CSS files before the template CSS
if(!empty($styles)) foreach($styles as $url => $params)
{
	if($params['before'])
	{
		$media = ($params['media']) ? "media=\"{$params['media']}\"" : '';
		echo "\t<link rel=\"stylesheet\" type=\"{$params['mime']}\" href=\"$url\" $media></script>\n";
	}
}
?>
<link rel="stylesheet" type="text/css" href="template/angie/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="template/angie/css/bootstrap-responsive.min.css" />
<link rel="stylesheet" type="text/css" href="template/angie/css/footer.css" />
<?php
// CSS files before the template CSS
if(!empty($styles)) foreach($styles as $url => $params)
{
	if(!$params['before'])
	{
		$media = ($params['media']) ? "media=\"{$params['media']}\"" : '';
		echo "\t<link rel=\"stylesheet\" type=\"{$params['mime']}\" href=\"$url\" $media></script>\n";
	}
}

// Script declarations
if(!empty($styleDeclarations)) foreach($styleDeclarations as $type => $content)
{
	echo "\t<style type=\"$type\">\n$content\n</style>";
}
