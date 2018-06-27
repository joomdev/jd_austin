<?php
/**
 * @version   $Id: URL.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 *
 * Original Author and Copyright by
 * File provides easy way to manipulate url parameters
 * @author Alexander Podgorny
 */

defined('ROKCOMMON') or die;

/**
 *
 */
class RokCommon_URL
{
    /**
     * Splits url into array of it's pieces as follows:
     * [scheme]://[user]:[pass]@[host]/[path]?[query]#[fragment]
     * In addition it adds 'query_params' key which contains array of
     * url-decoded key-value pairs
     *
     * @param String $sUrl Url
     * @return Array Parsed url pieces
     */
    public static function explode($sUrl)
    {
        $aUrl = parse_url($sUrl);
        $aUrl['query_params'] = array();
        $aPairs = array();
        if (array_key_exists('query', $aUrl))
        {
            $aUrl['query'] = preg_replace('/&(?!amp;)/i', '&amp;', $aUrl['query']);
            $aPairs = explode('&amp;', $aUrl['query']);
        }
        foreach ($aPairs as $sPair)
        {
            if (trim($sPair) == '')
            {
                continue;
            }
            if (strpos($sPair, '=') !== false)
            {
                list($sKey, $sValue) = explode('=', $sPair);
                $aUrl['query_params'][$sKey] = urldecode($sValue);
            }
            else
            {
                $aUrl['query_params'][$sPair] = '';
            }


        }
        return $aUrl;
    }

    /**
     * Compiles url out of array of it's pieces (returned by explodeUrl)
     * 'query' is ignored if 'query_params' is present
     *
     * @param Array $aUrl Array of url pieces
     * @return string
     */
    public static function implode($aUrl)
    {
        //[scheme]://[user]:[pass]@[host]/[path]?[query]#[fragment]

        $sQuery = '';

        // Compile query
        if (isset($aUrl['query_params']) && is_array($aUrl['query_params']))
        {
            $aPairs = array();
            foreach ($aUrl['query_params'] as $sKey => $sValue)
            {
                if (!empty($sValue))
                {
	                if(is_array($sValue))
	                {
		                $kvparray = array();
		                foreach($sValue as $subValue)
		                {
			                $aPairs[] = $sKey . '[]=' . urlencode($subValue);
		                }
	                }
	                else{
		                $aPairs[] = $sKey . '=' . urlencode($sValue);
	                }
                }
	            else{
		            $aPairs[] = $sKey;
	            }
            }
            $sQuery = implode('&amp;', $aPairs);
        } else
        {
            $sQuery = $aUrl['query'];
        }

        // Compile url
        $sUrl =
                (
                isset($aUrl['scheme']) && isset($aUrl['host]']) ?
                        $aUrl['scheme'] . '://' . (
                        isset($aUrl['user']) && $aUrl['user'] != '' && isset($aUrl['pass'])
                                ? $aUrl['user'] . ':' . $aUrl['pass'] . '@'
                                : ''
                        ) .
                        $aUrl['host']
                        : ''
                ) . (
                isset($aUrl['path']) && $aUrl['path'] != ''
                        ? $aUrl['path']
                        : ''
                ) . (
                $sQuery != ''
                        ? '?' . $sQuery
                        : ''
                ) . (
                isset($aUrl['fragment']) && $aUrl['fragment'] != ''
                        ? '#' . $aUrl['fragment']
                        : ''
                );
        return $sUrl;
    }

    /**
     * Parses url and returns array of key-value pairs of url params
     *
     * @param String $sUrl
     * @return Array
     */
    public static function getParams($sUrl)
    {
        $aUrl = RokCommon_URL::explode($sUrl);
        return $aUrl['query_params'];
    }

    /**
     * Removes existing url params and sets them to those specified in $aParams
     *
     * @param String $sUrl Url
     * @param Array $aParams Array of Key-Value pairs to set url params to
     * @return  String Newly compiled url
     */
    public static function setParams($sUrl, $aParams)
    {
        $aUrl = RokCommon_URL::explode($sUrl);
        $aUrl['query'] = '';
        $aUrl['query_params'] = $aParams;
        return RokCommon_URL::implode($aUrl);
    }

    /**
     * Updates values of existing url params and/or adds (if not set) those specified in $aParams
     *
     * @param String $sUrl Url
     * @param Array $aParams Array of Key-Value pairs to set url params to
     * @return  String Newly compiled url
     */
    public static function updateParams($sUrl, $aParams)
    {
        $aUrl = RokCommon_URL::explode($sUrl);
        $aUrl['query'] = '';
        $aUrl['query_params'] = array_merge($aUrl['query_params'], $aParams);
        return RokCommon_URL::implode($aUrl);
    }
}
