<?php
App::import('behavior', 'modification_tracing.Modification');
App::import('model', 'modification_tracing.Modification');

class Dummy extends Model {
    var $actsAs = array('ModificationTracing.Modification');
}

class ModificationbehaviorTestCase extends CakeTestCase {
    var $fixtures = array('plugin.modification_tracing.dummy', 'plugin.modification_tracing.modification');

	function startTest() {
        $this->Dummy =& ClassRegistry::init('Dummy');
	}

	function endTest() {
		unset($this->Dummy);
		ClassRegistry::flush();
	}

    function testFind() {
        $dummy = $this->Dummy->findById(1);
        $expected = $this->_expectedSample;

        $this->assertEqual($dummy, $expected);
    }

    function testCreate() {
        $create = array(
            'Dummy' => array(
                'name' => 'dummy2',
                'day1' => 1,
                'day2' => 1,
                'day3' => 1,
                'go' => 1,
                'back' => 1,
            )
        );
        $this->Dummy->create();
        $this->Dummy->save(array_merge_recursive($create, array('Dummy' => array('modificator' => 'user1', 'description' => 'create'))));

        $insertedID = $this->Dummy->getLastInsertID();
        $created = $this->Dummy->findById($insertedID);
        $created['Modification'] = $this->__unsetChangeable($created['Modification']);

        $expected = array(
            'Dummy' => array_merge_recursive($create['Dummy'], array('id' => $insertedID)),
            'Modification' => $this->__unsetChangeable(array($this->_expectedSample['Modification'][2]))
        );
        $expected['Modification'][0]['foreign_key'] = $insertedID;
        $expected['Modification'][0]['modifications']['Dummy']['name']['after'] = 'dummy2';

        $this->assertEqual($created, $expected);
    }

    function testEdit() {
        $create = array(
            'Dummy' => array(
                'name' => 'dummy3',
                'day1' => 1,
                'day2' => 1,
                'day3' => 1,
                'go' => 1,
                'back' => 1,
            )
        );
        $this->Dummy->create();
        $this->Dummy->save(array_merge_recursive($create, array('Dummy' => array('modificator' => 'user1', 'description' => 'create'))));
        $insertedID = $this->Dummy->getLastInsertID();

        $edit = $create;
        $edit['Dummy']['day1'] = 0;
        $edit['Dummy']['day3'] = 0;
        $this->Dummy->save(array_merge_recursive($edit, array('Dummy' => array('modificator' => 'user2', 'description' => '1,3日目OFF'))));

        $edit['Dummy']['go'] = 2;
        $edit['Dummy']['back'] = 2;
        $this->Dummy->save(array_merge_recursive($edit, array('Dummy' => array('modificator' => 'user3', 'description' => '交通手段変更'))));

        $edited = $this->Dummy->findById($insertedID);
        $edited['Modification'] = $this->__unsetChangeable($edited['Modification']);

        $expected = array(
            'Dummy' => array_merge_recursive($edit['Dummy'], array('id' => $insertedID)),
            'Modification' => $this->__unsetChangeable($this->_expectedSample['Modification'])
        );
        $expected['Modification'][2]['modifications']['Dummy']['name']['after'] = 'dummy3';
        for ($i=0; $i<count($expected['Modification']); $i++) {
            $expected['Modification'][$i]['foreign_key'] = $insertedID;
        }

        $this->assertEqual($edited, $expected);
    }

    function testDelete() {
        $create = array(
            'Dummy' => array(
                'name' => 'dummy4',
                'day1' => 1,
                'day2' => 1,
                'day3' => 1,
                'go' => 1,
                'back' => 1,
            )
        );
        $this->Dummy->create();
        $this->Dummy->save(array_merge_recursive($create, array('Dummy' => array('modificator' => 'user1', 'description' => 'create'))));
        $insertedID = $this->Dummy->getLastInsertID();

        $this->Dummy->delete($insertedID);

        $this->Modification =& ClassRegistry::init('Modification');
        $tmp_modifications = $this->Modification->find(
            'all', 
            array(
                'fields' => array('model_name', 'foreign_key', 'operation', 'modifications', 'modificator', 'description'),
                'conditions' => array('model_name' => 'Dummy', 'foreign_key' => $insertedID),
                'order' => array('Modification.created' => 'desc', 'Modification.id' => 'desc')
            )
        );
        $modifications = array('Modification' => array(count($tmp_modifications)));
        for ($i=0; $i<count($tmp_modifications); $i++) {
            $tmp_modifications[$i]['Modification']['modifications'] = json_decode($tmp_modifications[$i]['Modification']['modifications'], true);
            $modifications['Modification'][$i] = $tmp_modifications[$i]['Modification'];
        }

        $expectedCreate =  $this->__unsetChangeable(array($this->_expectedSample['Modification'][2]));
        $expectedCreate[0]['foreign_key'] = $insertedID;
        $expectedCreate[0]['modifications']['Dummy']['name']['after'] = 'dummy4';

        $expectedDelete = $expectedCreate;
        foreach ($expectedDelete[0]['modifications']['Dummy'] as $key => &$val) {
            $val['before'] = $val['after'];
            $val['after'] = '';
        }
        $expectedDelete[0]['operation'] = 3;
        $expectedDelete[0]['modificator'] = $expectedDelete[0]['description'] = '';

        $expected = array('Modification' => array($expectedDelete[0], $expectedCreate[0]));

        $this->assertEqual($modifications, $expected);
    }

    function test__isNotModificatorOrDescription() {
        $this->assertTrue($this->Dummy->Behaviors->Modification->__isNotModificatorOrDescription('test'));
        $this->assertFalse($this->Dummy->Behaviors->Modification->__isNotModificatorOrDescription('modificator'));
        $this->assertFalse($this->Dummy->Behaviors->Modification->__isNotModificatorOrDescription('description'));
    }

    function test__getModificator() {
        $expected= 'user1';

        $data = array('Dummy' => $this->_expectedSample['Dummy']);
        $this->Dummy->set($data);
        $this->assertIdentical($this->Dummy->Behaviors->Modification->__getModificator($this->Dummy), '');

        $data['Dummy']['modificator'] = $expected;
        $this->Dummy->set($data);

        $this->assertIdentical($this->Dummy->Behaviors->Modification->__getModificator($this->Dummy), $expected);
    }

    function test__getDescription() {
        $expected= 'modify username';

        $data = array('Dummy' => $this->_expectedSample['Dummy']);
        $this->Dummy->set($data);
        $this->assertIdentical($this->Dummy->Behaviors->Modification->__getDescription($this->Dummy), '');

        $data['Dummy']['description'] = $expected;
        $this->Dummy->set($data);

        $this->assertIdentical($this->Dummy->Behaviors->Modification->__getDescription($this->Dummy), $expected);
    }

    function __unsetChangeable($modifications) {
        $count = count($modifications);
        $ret = array($count);
        for ($i=0; $i<$count; $i++) {
            unset($modifications[$i]['id']);
            unset($modifications[$i]['created']);
            unset($modifications[$i]['modified']);
            $ret[$i] = $modifications[$i];
        }

        return $ret;
    }

    function __makefixture() {
        $dummy = $this->Dummy->findById(1);
        unset($dummy['Dummy']['id']);
        $dummy['Dummy']['modificator'] = 'user1';
        $dummy['Dummy']['description'] = 'create';
        debug($dummy);


        $this->Dummy->create();
        $this->Dummy->set($dummy);
        $this->Dummy->save();

        $dummy['Dummy']['day1'] = 0;
        $dummy['Dummy']['day3'] = 0;
        $dummy['Dummy']['modificator'] = 'user2';
        $dummy['Dummy']['description'] = '1,3日目OFF';
        $this->Dummy->set($dummy);
        $this->Dummy->save();

        $dummy['Dummy']['go'] = 2;
        $dummy['Dummy']['back'] = 2;
        $dummy['Dummy']['modificator'] = 'user3';
        $dummy['Dummy']['description'] = '交通手段変更';
        $this->Dummy->set($dummy);
        $this->Dummy->save();

        $dummy2 = $this->Dummy->findById(2);
        foreach ($dummy2['Modification'] as $mod) {
            debug($mod);
            debug(json_encode($mod['modifications']));
        }
    }

    var $_expectedSample = array(
            'Dummy' => array(
                'id' => 1,
                'name' => 'dummy1',
                'day1' => 0,
                'day2' => 1,
                'day3' => 0,
                'go' => 2,
                'back' => 2,
            ),
            'Modification' => array(
                array(
                    'id' => 3,
                    'model_name' => 'Dummy',
                    'foreign_key' => 1,
                    'operation' => 2,
                    'modifications' => array(
                        'Dummy' => array(
                            'go' => array('before' => '1', 'after' => '2'),
                            'back' => array('before' => '1', 'after' => '2'),
                        )
                    ),
                    'modificator' => 'user3',
                    'description' => '交通手段変更',
                    'created' => '2011-07-14 23:31:22',
                    'modified' => '2011-07-14 23:31:22'
                ),
                array(
                    'id' => 2,
                    'model_name' => 'Dummy',
                    'foreign_key' => 1,
                    'operation' => 2,
                    'modifications' => array(
                        'Dummy' => array(
                            'day1' => array('before' => '1', 'after' => '0'),
                            'day3' => array('before' => '1', 'after' => '0'),
                        )
                    ),
                    'modificator' => 'user2',
                    'description' => '1,3日目OFF',
                    'created' => '2011-07-14 23:30:22',
                    'modified' => '2011-07-14 23:30:22'
                ),
                array(
                    'id' => 1,
                    'model_name' => 'Dummy',
                    'foreign_key' => 1,
                    'operation' => 1,
                    'modifications' => array(
                        'Dummy' => array(
                            'name' => array('before' => '', 'after' => 'dummy1'),
                            'day1' => array('before' => '', 'after' => '1'),
                            'day2' => array('before' => '', 'after' => '1'),
                            'day3' => array('before' => '', 'after' => '1'),
                            'go' => array('before' => '', 'after' => '1'),
                            'back' => array('before' => '', 'after' => '1'),
                        )
                    ),
                    'modificator' => 'user1',
                    'description' => 'create',
                    'created' => '2011-07-14 23:29:22',
                    'modified' => '2011-07-14 23:29:22'
                ),
            ),
        );
}

