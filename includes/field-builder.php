<?php
if (!function_exists('cangrejo_render_field')) {
    function cangrejo_render_field($field) {
        $html = '';

        // ? Render oculto si el campo está marcado como hidden
        if (!empty($field['hidden'])) {
            $value = '';

            switch ($field['type']) {
                case 'user_name':
                    $current_user = wp_get_current_user();
                    $value = trim($current_user->first_name . ' ' . $current_user->last_name);
                    break;

                case 'user_login':
                    $current_user = wp_get_current_user();
                    $value = $current_user->user_login;
                    break;

                case 'user_role':
                    $current_user = wp_get_current_user();
                    $roles = $current_user->roles;
                    $value = !empty($roles) ? $roles[0] : '';
                    break;

                case 'current_date':
                    $value = date('Y-m-d');
                    break;

                default:
                    $value = '';
                    break;
            }

            return '<input type="hidden" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
        }

        // Atributo para lógica condicional
        $conditionalAttr = '';
        if (!empty($field['conditional'])) {
            $conditionalAttr = ' data-conditional="' . esc_attr(json_encode($field['conditional'])) . '"';
        }

        // Atributo para datos dinámicos
        $dynamicAttr = '';
        if (!empty($field['dynamic'])) {
            $dynamicAttr = ' data-dynamic="' . esc_attr(json_encode($field['dynamic'])) . '"';
        }

        // Marca visual para campos requeridos
        $requiredMark = !empty($field['required']) ? '<span class="required-label">*</span>' : '';

        // Si es section_title, no aplicar columnas
        if ($field['type'] === 'section_title') {
            $html .= '<h3 class="form-section-title">' . esc_html($field['label']) . '</h3>';
            return $html;
        }

        // Iniciar columna y grupo con data-conditional si aplica
        $html .= '<div class="form-columns"><div class="form-group"' . $conditionalAttr . '>';

        switch ($field['type']) {
            case 'user_name':
                $current_user = wp_get_current_user();
                $full_name = trim($current_user->first_name . ' ' . $current_user->last_name);
                $html .= '<label>' . esc_html($field['label'] ?? 'Full Name') . '</label>';
                $html .= '<input type="text" name="' . esc_attr($field['name']) . '" value="' . esc_attr($full_name) . '" readonly />';
                break;

            case 'user_login':
                $current_user = wp_get_current_user();
                $html .= '<label>' . esc_html($field['label'] ?? 'User Login') . '</label>';
                $html .= '<input type="text" name="' . esc_attr($field['name']) . '" value="' . esc_attr($current_user->user_login) . '" readonly />';
                break;

            case 'user_role':
                $current_user = wp_get_current_user();
                $roles = $current_user->roles;
                $role = !empty($roles) ? $roles[0] : 'undefined';
                $html .= '<label>' . esc_html($field['label'] ?? 'User Role') . '</label>';
                $html .= '<input type="text" name="' . esc_attr($field['name']) . '" value="' . esc_attr($role) . '" readonly />';
                break;

            case 'current_date':
                $today = date('Y-m-d');
                $html .= '<label>' . esc_html($field['label'] ?? 'Current Date') . '</label>';
                $html .= '<input type="date" name="' . esc_attr($field['name']) . '" value="' . esc_attr($today) . '" readonly />';
                break;

            case 'text':
            case 'number':
                $html .= '<label for="' . esc_attr($field['name']) . '">'
                       . esc_html($field['label']) . $requiredMark . '</label>';
                if (!empty($field['dynamic']['autocomplete'])) {
                    $html .= '<input type="text" id="' . esc_attr($field['name']) . '"'
                           . ' name="' . esc_attr($field['name']) . '"'
                           . ' list="dl_' . esc_attr($field['name']) . '"'
                           . $dynamicAttr
                           . (!empty($field['required']) ? ' required' : '')
                           . ' />';
                    $html .= '<datalist id="dl_' . esc_attr($field['name']) . '"></datalist>';
                } else {
                    $html .= '<input type="' . esc_attr($field['type']) . '"'
                           . ' id="' . esc_attr($field['name']) . '"'
                           . ' name="' . esc_attr($field['name']) . '"'
                           . ' placeholder="' . esc_attr($field['placeholder'] ?? '') . '"'
                           . (!empty($field['required']) ? ' required' : '')
                           . (!empty($field['attributes']['readonly']) ? ' readonly' : '')
                           . $dynamicAttr
                           . ' />';
                }
                break;

            case 'select':
                $html .= '<label for="' . esc_attr($field['name']) . '">'
                       . esc_html($field['label']) . $requiredMark . '</label>';
                $html .= '<select id="' . esc_attr($field['name']) . '"'
                       . ' name="' . esc_attr($field['name']) . '"'
                       . (!empty($field['required']) ? ' required' : '')
                       . $dynamicAttr
                       . '>';
                if (empty($field['dynamic'])) {
                    foreach ($field['options'] as $value => $label) {
                        $html .= '<option value="' . esc_attr($value) . '">'
                               . esc_html($label) . '</option>';
                    }
                } else {
                    $html .= '<option value="">Cargando…</option>';
                }
                $html .= '</select>';
                break;

            case 'textarea':
                $html .= '<label for="' . esc_attr($field['name']) . '">'
                       . esc_html($field['label']) . $requiredMark . '</label>';
                $html .= '<textarea id="' . esc_attr($field['name']) . '"'
                       . ' name="' . esc_attr($field['name']) . '"'
                       . ' placeholder="' . esc_attr($field['placeholder'] ?? '') . '"'
                       . $dynamicAttr
                       . '></textarea>';
                break;

            case 'radio':
                $html .= '<label class="form-label">' . esc_html($field['label']) . $requiredMark . '</label>';
                $html .= '<div class="single-choice-group"'
                       . ' data-name="' . esc_attr($field['name']) . '"'
                       . $dynamicAttr
                       . '>';
                if (empty($field['dynamic'])) {
                    foreach ($field['options'] as $value => $label) {
                        $html .= '<label class="form-option">';
                        $html .= '<input type="radio"'
                               . ' id="' . esc_attr($field['name'] . '_' . $value) . '"'
                               . ' name="' . esc_attr($field['name']) . '"'
                               . ' value="' . esc_attr($value) . '"'
                               . (!empty($field['required']) ? ' required' : '')
                               . '>';
                        $html .= esc_html($label);
                        $html .= '</label>';
                    }
                }
                $html .= '</div>';
                break;

            case 'checkbox':
                $html .= '<label>';
                $html .= '<input type="checkbox"'
                       . ' id="' . esc_attr($field['name']) . '"'
                       . ' name="' . esc_attr($field['name']) . '"'
                       . (!empty($field['required']) ? ' required' : '')
                       . '>';
                $html .= esc_html($field['label']) . $requiredMark;
                $html .= '</label>';
                break;

            case 'checkbox_single':
                $html .= '<label class="form-label">' . esc_html($field['label']) . $requiredMark . '</label>';
                $html .= '<div class="checkbox-single-group"'
                       . ' data-name="' . esc_attr($field['name']) . '"'
                       . $dynamicAttr
                       . '>';
                if (empty($field['dynamic'])) {
                    foreach ($field['options'] as $value => $label) {
                        $html .= '<label class="form-option">';
                        $html .= '<input type="checkbox"'
                               . ' name="' . esc_attr($field['name']) . '[]"'
                               . ' value="' . esc_attr($value) . '"'
                               . ' class="checkbox-single"'
                               . ' data-group="' . esc_attr($field['name']) . '"'
                               . (!empty($field['required']) ? ' required' : '')
                               . '>';
                        $html .= esc_html($label);
                        $html .= '</label>';
                    }
                }
                $html .= '</div>';
                break;

            case 'image':
                // Etiqueta del campo
                $html .= '<label for="' . esc_attr($field['name']) . '">'
                       . esc_html($field['label']) . $requiredMark
                       . '</label>';

                // Contenedor principal
                $html .= '<div class="feasy-image-wrapper">';

                // Botón de subida
                $html .= '<label for="' . esc_attr($field['name']) . '_file" class="feasy-image-upload">'
                       . 'Upload image'
                       . '</label>';

                // Input file oculto
                $html .= '<input type="file"'
                       . ' accept="image/*"'
                       . ' id="' . esc_attr($field['name']) . '_file"'
                       . ' class="feasy-image-file">';

                // Preview + botón eliminar
                $html .= '<div class="feasy-preview-container">';
                $html .= '  <img src="" alt="Preview" class="feasy-image-preview">';
                $html .= '  <button type="button"'
                       . ' class="feasy-image-remove"'
                       . ' aria-label="Eliminar imagen"'
                       . ' title="Eliminar imagen"></button>';
                $html .= '</div>';

                // Campo hidden para el base64
                $html .= '<input type="hidden"'
                       . ' name="' . esc_attr($field['name']) . '"'
                       . ' class="feasy-image-data">';

                $html .= '</div>';
                break;

            case 'date':
                $includeTime = !empty($field['attributes']['include_time']) ? 'datetime-local' : 'date';
                $defaultValue = (!empty($field['attributes']['default']) && $field['attributes']['default'] === 'current')
                    ? date($includeTime === 'datetime-local' ? 'Y-m-d\\TH:i' : 'Y-m-d')
                    : '';
                $html .= '<label for="' . esc_attr($field['name']) . '">'
                       . esc_html($field['label']) . $requiredMark . '</label>';
                $html .= '<input type="' . esc_attr($includeTime) . '"'
                       . ' id="' . esc_attr($field['name']) . '"'
                       . ' name="' . esc_attr($field['name']) . '"'
                       . ' value="' . esc_attr($defaultValue) . '"'
                       . (!empty($field['required']) ? ' required' : '')
                       . '>';
                break;

            default:
                $html .= '<!-- Tipo de campo no soportado: ' . esc_html($field['type']) . ' -->';
                break;
        }

        $html .= '</div></div>';

        if (!empty($field['name'])) {
            error_log('HTML generado para el campo: ' . $field['name'] . PHP_EOL . $html . PHP_EOL);
        }

        return $html;
    }
}