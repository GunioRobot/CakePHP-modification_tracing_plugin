<?php
define('MODIFICATION_CREATE', 1);
define('MODIFICATION_MODIFY', 2);
define('MODIFICATION_DELETE', 3);

class ModificationBehavior extends ModelBehavior {
	var $_modelName = null;
	var $_modificatorField = false;
	var $_descriptionField = false;

	function setup(&$model, $config = array()){
		$default = array(
            // TODO: tableName
			'modelName' => 'Modification',
			'modificatorField' => 'modificator',
			'descriptionField' => 'description',
		);
		$option = array_merge($default, $config);

        // TODO: tableName
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
		            'conditions' => array('Modification.model_name' => $model->name),
					'order' => array('Modification.created' => 'desc', 'Modification.id' => 'desc'),
				)
			);
			$model->bindModel(array('hasMany' => $assoc), true);
        } else {
            $model->unbindModel(array('hasMany' => array('$this->_modelName')));
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
						    $this->__isNotModificatorOrDescription($className, $key)) // TODO: メソッド名ェ…
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
						if ($this->__isNotModificatorOrDescription($className, $key)) {
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
					if ($this->__isNotModificatorOrDescription($className, $key)) {
						$modifications[$className][$key] = array('b' => $val, 'a' => '');
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

	function __isNotModificatorOrDescription($className, $key){
	    return ($this->_modificatorField != $key) && ($this->_descriptionField != $key);
	}

	function __getModificator(&$model){
        var_dump($model->data[$model->name][$this->_modificatorField]);
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
			$this->_modelName => array(
				'model_name' => $model->name,
				'foreign_key' => $model->id,
				'operation' => $index,
				'modifications' => json_encode($modifications),
				'modificator' => $modificator,
				'description' => $description
			)
		);

		$m_model = ClassRegistry::init('Modification');
		$m_model->create();
		return $m_model->save($data);
	}
}

