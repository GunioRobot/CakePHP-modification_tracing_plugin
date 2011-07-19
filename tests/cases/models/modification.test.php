<?php
/* Modification Test cases generated on: 2011-07-14 23:29:22 : 1310653762*/
App::import('Model', 'ModificationTracing.Modification');

class ModificationTestCase extends CakeTestCase {
	function startTest() {
		$this->Modification =& ClassRegistry::init('Modification');
	}

	function endTest() {
		unset($this->Modification);
		ClassRegistry::flush();
	}

}
