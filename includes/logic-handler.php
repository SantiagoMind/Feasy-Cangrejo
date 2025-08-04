<?php
function cangrejo_load_logic($form_key) {
    $logic_file = plugin_dir_path(__FILE__) . 'form-logic-' . $form_key . '.php';
    if (!file_exists($logic_file)) {
        return [];
    }
    $logic = include $logic_file;
    return is_array($logic) ? $logic : [];
}

function cangrejo_apply_logic_to_config(array &$config, array $logic) {
    $apply_conditional = function (&$fields, $target, $condData) use (&$apply_conditional) {
        foreach ($fields as &$field) {
            if (($field['name'] ?? '') === $target) {
                $field['conditional'] = $condData;
            }
            if (!empty($field['fields']) && is_array($field['fields'])) {
                $apply_conditional($field['fields'], $target, $condData);
            }
        }
        unset($field);
    };

    foreach ($logic as $rule) {
        $condData = [
            'type'       => 'visibility',
            'conditions' => array_map(function ($c) {
                return [
                    'field'    => $c['field'],
                    'value'    => $c['value'],
                    'operator' => $c['operator'] ?? 'equal_to',
                ];
            }, $rule['conditions'] ?? []),
            'operator'   => ($rule['match'] ?? 'all') === 'all' ? 'AND' : 'OR',
        ];

        foreach ($rule['actions'] ?? [] as $action) {
            if (($action['action'] ?? '') === 'show') {
                foreach ($action['targets'] ?? [] as $target) {
                    $apply_conditional($config['fields'], $target, $condData);
                }
            }
        }
    }
}