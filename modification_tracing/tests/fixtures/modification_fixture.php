<?php
class ModificationFixture extends CakeTestFixture {
    var $name = 'Modification';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
        'model_name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'foreign_key' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10),
        'operation' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 1),
        'modifications' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 2048, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'modificator' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'description' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    var $records = array(
        array(
            'id' => 1,
            'model_name' => 'Dummy',
            'foreign_key' => 1,
            'operation' => 1,
            'modifications' => '{"Dummy":{"name":{"before":"","after":"dummy1"},"day1":{"before":"","after":"1"},"day2":{"before":"","after":"1"},"day3":{"before":"","after":"1"},"go":{"before":"","after":"1"},"back":{"before":"","after":"1"}}}',
            'modificator' => 'user1',
            'description' => 'create',
            'created' => '2011-07-14 23:29:22',
        ),
        array(
            'id' => 2,
            'model_name' => 'Dummy',
            'foreign_key' => 1,
            'operation' => 2,
            'modifications' => '{"Dummy":{"day1":{"before":"1","after":0},"day3":{"before":"1","after":0}}}',
            'modificator' => 'user2',
            'description' => '1,3“ú–ÚOFF',
            'created' => '2011-07-14 23:30:22',
        ),
        array(
            'id' => 3,
            'model_name' => 'Dummy',
            'foreign_key' => 1,
            'operation' => 2,
            'modifications' => '{"Dummy":{"go":{"before":"1","after":2},"back":{"before":"1","after":2}}}',
            'modificator' => 'user3',
            'description' => 'Œð’ÊŽè’i•ÏX',
            'created' => '2011-07-14 23:31:22',
        ),
    );
}

