<?php
/**
 * @version   $Id: State.php 10831 2013-05-29 19:32:17Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Original Copyright below
 */

defined('ROKCOMMON') or die;
/**
Derived from:

The contents of this file are subject to the Mozilla Public
License Version 1.1 (the "License"); you may not use this file
except in compliance with the License. You may obtain a copy
of the License at http://www.mozilla.org/MPL/

Software distributed under the License is distributed on an
"AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
implied. See the License for the specific language governing
rights and limitations under the License.

The Original Code is State Machine Compiler (SMC).

The Initial Developer of the Original Code is Charles W. Rapp.
Portions created by Charles W. Rapp are
Copyright (C) 2005. Charles W. Rapp.
All Rights Reserved.

Port (from the Python port) to PHP5 by Toni Arnold

Contributor(s):

See: http://smc.sourceforge.net/

RCS ID
$Id: State.php 10831 2013-05-29 19:32:17Z btowles $

CHANGE LOG
$Log: statemap.php,v $
Revision 1.4  2009/04/25 14:29:10  cwrapp
Corrected isInTransition.

Revision 1.3  2009/04/22 20:19:57  fperrad
Pass initial state to FSMContext constructor

Revision 1.2  2008/05/20 18:31:13  cwrapp
----------------------------------------------------------------------

Committing release 5.1.0.

Modified Files:
Makefile README.txt smc.mk tar_list.txt bin/Smc.jar
examples/Ant/EX1/build.xml examples/Ant/EX2/build.xml
examples/Ant/EX3/build.xml examples/Ant/EX4/build.xml
examples/Ant/EX5/build.xml examples/Ant/EX6/build.xml
examples/Ant/EX7/build.xml examples/Ant/EX7/src/Telephone.java
examples/Java/EX1/Makefile examples/Java/EX4/Makefile
examples/Java/EX5/Makefile examples/Java/EX6/Makefile
examples/Java/EX7/Makefile examples/Ruby/EX1/Makefile
lib/statemap.jar lib/C++/statemap.h lib/Java/Makefile
lib/Php/statemap.php lib/Scala/Makefile
lib/Scala/statemap.scala net/sf/smc/CODE_README.txt
net/sf/smc/README.txt net/sf/smc/Smc.java
----------------------------------------------------------------------

Revision 1.1  2008/04/22 16:00:39  fperrad
- add PHP language (patch from Toni Arnold)


 */

/*
A StateUndefinedException is thrown by
an SMC-generated state machine whenever a transition is taken
and there is no state currently set. This occurs when a
transition is issued from within a transition action."""
*/
/**
 *
 */
class StateUndefinedException extends Exception
{
}

/*
A TransitionUndefinedException is thrown by
an SMC-generated state machine whenever a transition is taken
which:

 - Is not explicitly defined in the current state.
 - Is not explicitly defined in the current FSM's default state.
 - There is no Default transition in the current state."""
*/
/**
 *
 */
class TransitionUndefinedException extends Exception
{
}

/*
Base State class
*/
/**
 *
 */
class RokCommon_State
{
	/**
	 * @var
	 */
	protected $_name;
	/**
	 * @var
	 */
	protected $_id;

	/**
	 * @param $name
	 * @param $id
	 */
	public function __construct($name, $id)
	{
		$this->_name = $name;
		$this->_id   = $id;
	}

	// Returns the state's printable name.
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->_name;
	}

	// Returns the state's unique identifier.
	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->_id;
	}
}
