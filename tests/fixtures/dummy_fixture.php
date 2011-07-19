<?php
class DummyFixture extends CakeTestFixture {
    var $name = 'Dummy';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
        'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20, 'collate' => 'utf8_hungarian_ci', 'charset' => 'utf8'),
        'day1' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 4),
        'day2' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 4),
        'day3' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 4),
        'go' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 1),
        'back' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 1),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    var $records = array(
        array(
            'id' => 1,
            'name' => 'dummy1',
            'day1' => 0,
            'day2' => 1,
            'day3' => 0,
            'go' => 2,
            'back' => 2,
        ),
    );
}

