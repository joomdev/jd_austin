<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class SessionHandler {

	static $instances = array();
	
	function __construct($params = array()){
		session_set_save_handler(
			array($this, 'open'), 
			array($this, 'close'), 
			array($this, 'read'), 
			array($this, 'write'),
			array($this, 'destroy'), 
			array($this, 'gc')
		);
	}

	public static function getInstance($handler = 'php', $params = array()){
		if($handler != 'php'){
			if(empty(self::$instances[$handler])){
				$class = '\G2\L\SessionHandlers\\'.Str::camilize($handler);
				self::$instances[$handler] = new $class($params);
			}
			return self::$instances[$handler];
		}
	}

	function open($save_path, $session_name){
		return true;
	}

	function close(){
		return true;
	}

	function read($sess_id){
		return null;
	}

	function write($sess_id, $data){
		return true;
	}

	function destroy($sess_id){
		return true;
	}
	
	function gc($max_life_time = null){
		return true;
	}
}