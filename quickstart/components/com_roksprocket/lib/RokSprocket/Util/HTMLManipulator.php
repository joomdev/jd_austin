<?php
/**
 * @version   $Id: HTMLManipulator.php 10887 2013-05-30 06:31:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


/**
 *
 */
class RokSprocket_Util_HTMLManipulator
{
	/**
	 * @var array
	 */
	protected $stack = array();

	/**
	 * @param      $content
	 * @param bool $amount
	 *
	 * @return mixed
	 */
	public function truncate($content, $amount = false)
	{

		if (!$amount || preg_match_all("/\s+/", $content, $junk) <= $amount) return $content;

		$content = preg_replace_callback("/(<\/?[^>]+\s+[^>]*>)/", array($this, '_shield'), $content);

		$words   = 0;
		$output  = array();
		$content = str_replace(array("<", ">"), array(" <", "> "), $content);
		$tokens  = mb_split("\s+", $content);

		foreach ($tokens as $token) {
			// goes through tags and store them so they can get restored afterwards
			if (preg_match_all("/<(\/?[^\x01>]+)([^>]*)>/", $token, $tags, PREG_SET_ORDER)) {
				foreach ($tags as $tag) $this->_recordTag($tag[1], $tag[2]);
			}

			$output[] = trim($token);

			if (!preg_match("/^(<[^>]+>)+$/", $token)) {
				// if it's a real word outside tags, increase the count
				if (preg_match("/\p{L}+/u", $token)) $matching = true;
				else $matching = preg_match("/\w/", $token);

				if (!strpos($token, '=') && !strpos($token, '<') && strlen(trim(strip_tags($token))) > 0 && $matching) ++$words;
			}

			if ($words >= $amount) break;
		}

		$truncate = $this->_unshield(implode(' ', $output));

		return $truncate;
	}

	/**
	 * @param      $content
	 * @param bool $amount
	 *
	 * @return string
	 */
	public function truncateHTML($content, $amount = false)
	{
		return $this->restoreTags($this->truncate($content, $amount));
	}

	/**
	 * @param $content
	 *
	 * @return string
	 */
	public function restoreTags($content)
	{
		foreach ($this->stack as $tag) $content .= "</" . $tag . ">";
		return $content;
	}

	/**
	 * @param $matches
	 *
	 * @return mixed
	 */
	private function _shield($matches)
	{
		return preg_replace("/\s/", "\x01", $matches[0]);
	}

	/**
	 * @param $strings
	 *
	 * @return mixed
	 */
	private function _unshield($strings)
	{
		return preg_replace("/\x01/", " ", $strings);
	}

	/**
	 * @param $tag
	 * @param $args
	 */
	private function _recordTag($tag, $args)
	{
		if (strlen($args) and $args[strlen($args) - 1] == '/') return; else if ($tag[0] == '/') {
			$tag = substr($tag, 1);
			for ($i = count($this->stack) - 1; $i >= 0; $i--) {
				if ($this->stack[$i] == $tag) {
					array_splice($this->stack, $i, 1);
					return;
				}
			}
			return;
		} else if (in_array($tag, array('p', 'li', 'ul', 'ol', 'div', 'span', 'a'))) $this->stack[] = $tag; else return;
	}
}
