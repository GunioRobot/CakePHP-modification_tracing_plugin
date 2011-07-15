<?php
/* Modification Fixture generated on: 2011-07-14 23:29:22 : 1310653762 */
class ModificationFixture extends CakeTestFixture {
	var $name = 'Modification';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'model_name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'foreign_key' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
		'operation' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 1),
		'modifications' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 2048, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modificator' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 2),
		'notes' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	var $records = array(
		array(
			'id' => 1,
			'model_name' => 'Lorem ipsum dolor ',
			'foreign_key' => 1,
			'operation' => 1,
			'modifications' => 'Lorem ipsum dolor sit amet',
			'modificator' => 1,
			'notes' => 'Lorem ipsum dolor sit amet',
			'created' => '2011-07-14 23:29:22',
			'modified' => '2011-07-14 23:29:22'
		),
	);
}
