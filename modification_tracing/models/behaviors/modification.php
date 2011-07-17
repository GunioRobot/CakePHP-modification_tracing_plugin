<?php
define('MODIFICATION_CREATE', 1);
define('MODIFICATION_MODIFY', 2);
define('MODIFICATION_DELETE', 3);

class ModificationBehavior extends ModelBehavior {
    var $_tableName = null;
	var $_modelName = null;
	var $_modificatorField = false;
	var $_descriptionField = false;

	function setup(&$model, $config = array()){
		$default = array(
            'tableName' => 'modifications',
			'modelName' => 'Modification',
			'modificatorField' => 'modificator',
			'descriptionField' => 'description',
		);
		$option = array_merge($default, $config);

        $this->_tableName = $option['tableName'];
		$this->_modelName = $option['modelName'];
		$this->_modificatorField = $option['modificatorField'];
		$this->_descriptionField = $option['descriptionField'];
	}

	function beforeFind(&$model, $queryData)
	{
        $queryData = array_merge(array('modificationTracing' => true), $queryData); // beforeSave の find では false
		if ($queryData['modificationTracing'] && ! array_key_exists($this->_modelName, $model->hasMany)){
			$assoc = array(
				$this->_modelName => array(
		            'className' => 'Modification',
		            'foreignKey' => 'foreign_key',
		            'conditions' => array($this->_modelName.'.model_name' => $model->name),
					'order' => array($this->_modelName.'.created' => 'desc', $this->_modelName.'.id' => 'desc'),
                    'fields' => array('id', 'model_name', 'foreign_key', 'operation', 'modifications', 'modificator AS '.$this->_modificatorField, 'description AS '.$this->_descriptionField, 'created', 'modified'),
				)
			);
			$model->bindModel(array('hasMany' => $assoc), true);
            $model->{$this->_modelName}->setSource($this->_tableName);
        } else {
            $model->unbindModel(array('hasMany' => array($this->_modelName)));
        }

		return true;
	}

	function afterFind(&$model, $results){
		for ($i=0; $i<count($results); $i++){
			$r =& $results[$i];
			if (array_key_exists($this->_modelName, $r)){
				for ($j=0; $j<count($r[$this->_modelName]); $j++){
					$r[$this->_modelName][$j]['modifications'] = json_decode($r[$this->_modelName][$j]['modifications'], true);
				}
			}
		}

		return $results;
	}

	var $_before = array();
	function beforeSave(&$model){
		if ($model->id){
			$this->_before = $model->find('first', array('conditions' => array($model->primaryKey => $model->id), 'modificationTracing' => false));
        } else {
            $this->_before = array();
        }
		return true;
	}

	function afterSave(&$model){
		$modifications = array();
		$index = 0;

		if ($this->_before){ // edit
			$index = MODIFICATION_MODIFY;
			if ($model->data) {
				foreach($model->data as $className => $new) {
                    if ($className == $this->_modelName) { continue; }
					$old = (isset($this->_before[$className])) ? $this->_before[$className] : array();
					foreach($new as $key => $val){
						if (// 前データがあって、フォーム値と異なる場合
						    ((array_key_exists($key, $old) && (strcmp($old[$key], $val) != 0)) ||
						    // 前データがなく、値が空でなく(理由忘れた…)、主キーでない場合 TODO: なんで？
						     (! array_key_exists($key, $old) && ! $val && $key != $model->primaryKey)) &&
						    // キーが modificator, descripition でない
						    $this->__isNotModificatorOrDescription($key))
					    {
							$o = array_key_exists($key, $old) ? $old[$key] : '';
							$modifications[$className][$key] = array('before' => $o, 'after' => $val);
						}
					}
				}
			}
		} else { // add
			$index = MODIFICATION_CREATE;
			if ($model->data){
				foreach($model->data as $className => $vals) {
                    if ($className == $this->_modelName) { continue; }
					foreach($vals as $key => $val){
						if ($this->__isNotModificatorOrDescription($key)) {
							$modifications[$className][$key] = array('before' => '', 'after' => $val);
						}
					}
				}
			}
		}

		// There are no modifications.
		if (! $modifications) { return; }

		return $this->__saveModification(
			$model,
			$index,
			$modifications,
			$this->__getModificator($model),
			$this->__getDescription($model)
		);
	}

	function beforeDelete(&$model, $cascade){
		if ($model->id){
			$this->_before = $model->find('first', array('conditions' => array($model->primaryKey => $model->id)));
		}
		return true;
	}

	function afterDelete(&$model){
		$modifications = array();
		if ($this->_before){
			foreach($this->_before as $className => $vals) {
				if ($className == $this->_modelName) { continue; }
				foreach($vals as $key => $val){
					if ($this->__isNotModificatorOrDescription($key) && $key != $model->primaryKey) {
						$modifications[$className][$key] = array('before' => $val, 'after' => '');
					}
				}
			}
		}

		return $this->__saveModification(
			$model,
			MODIFICATION_DELETE,
			$modifications,
			$this->__getModificator($model),
			$this->__getDescription($model)
		);
	}

	function __isNotModificatorOrDescription($key){
	    return ($this->_modificatorField != $key) && ($this->_descriptionField != $key);
	}

	function __getModificator(&$model){
		if ($this->_modificatorField && isset($model->data[$model->name][$this->_modificatorField]) && $model->data[$model->name][$this->_modificatorField]) {
	    	return $model->data[$model->name][$this->_modificatorField];
	    }
		return '';
	}

	function __getDescription(&$model){
		if ($this->_descriptionField && isset($model->data[$model->name][$this->_descriptionField]) && $model->data[$model->name][$this->_descriptionField]) {
	    	return $model->data[$model->name][$this->_descriptionField];
	    }
		return '';
	}

	function __saveModification(&$model, $index, $modifications, $modificator, $description){
		// Create modification.
		$data = array(
			'Modification' => array(
				'model_name' => $model->name,
				'foreign_key' => $model->id,
				'operation' => $index,
				'modifications' => json_encode($modifications),
				'modificator' => $modificator,
				'description' => $description
			)
		);

		$this->Modification =& ClassRegistry::init('Modification');
        $this->Modification->setSource($this->_tableName);
		$this->Modification->create();
		return $this->Modification->save($data);
	}
}

