<?php

class ModificationTracingHelper extends Helper {

    var $_operations = array(
        1 => 'modification_tracing_create',
        2 => 'modification_tracing_edit',
        3 => 'modification_tracing_delete',
    );

    function operation($opr) {
        return array_key_exists($opr, $this->_operations) ? __d('modification_tracing', $this->_operations[$opr], true) : '';
    }
}
