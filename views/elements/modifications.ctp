<?php
    App::import('helper', 'ModificationTracing.ModificationTracingHelper');
    $this->ModificationTracing = new ModificationTracingHelper();
?>
<div class="modification_tracing">
    <table>
        <thead>
            <tr>
                <th><?php echo __d('modification_tracing', 'modification_tracing_no', true); ?></th>
                <th><?php echo __d('modification_tracing', 'modification_tracing_model_name', true); ?></th>
                <th><?php echo __d('modification_tracing', 'modification_tracing_foreign_key', true); ?></th>
                <th><?php echo __d('modification_tracing', 'modification_tracing_operation', true); ?></th>
                <th><?php echo __d('modification_tracing', 'modification_tracing_modifications', true); ?></th>
                <th><?php echo __d('modification_tracing', 'modification_tracing_modificator', true); ?></th>
                <th><?php echo __d('modification_tracing', 'modification_tracing_description', true); ?></th>
                <th><?php echo __d('modification_tracing', 'modification_tracing_created', true); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php for ($i=0; $i<count($modifications); $i++) : $m = array_key_exists($modelName, $modifications[$i]) ? $modifications[$i][$modelName] : $modifications[$i]; ?>
            <tr>
                <td><?php echo ($i + 1); ?></td>
                <td><?php echo h($m['model_name']); ?></td>
                <td><?php echo h($m['foreign_key']); ?></td>
                <td><?php echo $this->ModificationTracing->operation($m['operation']); ?></td>
                <td>
                    <table class="modifications">
                        <thead>
                            <tr>
                            <?php $keyList = $bList = $aList = array(); ?>
                            <?php foreach ($m['modifications'] as $className => $modList) : $modCount = count($modList); ?>
                                <?php 
                                    $keyList = array_keys($modList);
                                    $ba = array_values($modList);
                                    $bList = Set::extract('/before', $ba);
                                    $aList = Set::extract('/after', $ba);
                                ?>
                                <th colspan="<?php echo $modCount+1; ?>"><?php echo h($className); ?></th>
                            <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array('keyList' => 'key', 'bList' => 'before', 'aList' => 'after') as $varName => $clsName) : $list = ${$varName}; ?>
                            <tr class="<?php echo $clsName; ?>">
                                <th><?php echo __d('modification_tracing', 'modification_tracing_'.$clsName, true); ?></th>
                                <?php foreach ($list as $val) : ?>
                                <td><?php echo h($val); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </td>
                <td><?php echo h($m['modificator']); ?></td>
                <td><?php echo h($m['description']); ?></td>
                <td><?php echo h($m['created']); ?></td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
</div>

