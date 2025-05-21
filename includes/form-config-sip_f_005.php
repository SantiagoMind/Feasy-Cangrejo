<?php

return [
    'title'     => 'QC Inspection Checks Personal Hygiene',
    'form_name' => 'QC Inspection Checks Personal Hygiene',
    'endpoint'  => 'https://script.google.com/macros/s/AKfycby3UpffgoH4R01TBJ0zYwIt--SvXUk_4JDj1UPV2ReDWAOMgNYMj-scO4P48THl_5UOuA/exec',

    'fields' => [
        [
            'type'  => 'user_name',
            'label' => 'User Full Name',
            'name'  => 'user_fullname',
            'hidden' => true,
        ],
        [
            'type'  => 'user_login',
            'label' => 'User Login',
            'name'  => 'user_login',
            'hidden' => true,
        ],
        [
            'type'  => 'user_role',
            'label' => 'User Role',
            'name'  => 'user_role',
            'hidden' => true,
        ],
        [
            'type'  => 'current_date',
            'label' => 'Submission Date',
            'name'  => 'submission_date',
            'hidden' => true,
        ],
        // Fecha e inspector
        [
            'type'     => 'date',
            'label'    => 'Inspection Date',
            'name'     => 'inspection_date',
            'required' => true,
        ],
        [
            'type'     => 'text',
            'label'    => 'Inspected By',
            'name'     => 'inspected_by',
            'required' => true,
        ],

        // 1. Employees wear clean and proper clothes
        [
            'type'     => 'radio',
            'label'    => 'Employees wear clean and proper clothes (aprons, smocks)',
            'name'     => 'employees_cloth',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'employees_cloth_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'employees_cloth',
                'value' => 'fail',
            ],
        ],

        // 2. Hairnets usage
        [
            'type'     => 'radio',
            'label'    => 'All scalp hair is fully contained by proper use of hairnets',
            'name'     => 'hairnets_usage',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'hairnets_usage_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'hairnets_usage',
                'value' => 'fail',
            ],
        ],

        // 3. Beard nets usage
        [
            'type'     => 'radio',
            'label'    => 'All personnel with beards properly wear beard nets',
            'name'     => 'beardnets_usage',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'beardnets_usage_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'beardnets_usage',
                'value' => 'fail',
            ],
        ],

        // 4. Fingernails
        [
            'type'     => 'radio',
            'label'    => 'Fingernails are short, unpolished, and clean (no artificial nails)',
            'name'     => 'fingernails_clean',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'fingernails_clean_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'fingernails_clean',
                'value' => 'fail',
            ],
        ],

        // 5. Hand washing
        [
            'type'     => 'radio',
            'label'    => 'Hands are washed properly, frequently & at appropriate times',
            'name'     => 'hands_washed',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'hands_washed_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'hands_washed',
                'value' => 'fail',
            ],
        ],

        // 6. Gloves usage
        [
            'type'     => 'radio',
            'label'    => 'Gloves are properly worn and replaced regularly',
            'name'     => 'gloves_usage',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'gloves_usage_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'gloves_usage',
                'value' => 'fail',
            ],
        ],

        // 7. Footwear
        [
            'type'     => 'radio',
            'label'    => 'All personnel wear close-toe shoes in processing and storage areas',
            'name'     => 'footwear_usage',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'footwear_usage_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'footwear_usage',
                'value' => 'fail',
            ],
        ],

        // 8. Jewelry restriction
        [
            'type'     => 'radio',
            'label'    => 'Jewelry is limited to a plain wedding ring (no stones), no bracelets, watches, necklaces, earrings, etc.',
            'name'     => 'jewelry_restriction',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'jewelry_restriction_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'jewelry_restriction',
                'value' => 'fail',
            ],
        ],

        // 9. Hand injury care
        [
            'type'     => 'radio',
            'label'    => 'Burns, wounds, sores or scabs on the hand are covered with a metalized blue band-aid and glove',
            'name'     => 'hand_injury_care',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'hand_injury_care_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'hand_injury_care',
                'value' => 'fail',
            ],
        ],

        // 10. Eating/drinking policy
        [
            'type'     => 'radio',
            'label'    => 'Eating, drinking, chewing gum, smoking or using tobacco allowed only in designated areas',
            'name'     => 'eating_drinking_policy',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'eating_drinking_policy_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'eating_drinking_policy',
                'value' => 'fail',
            ],
        ],

        // 11. Cellphone policy
        [
            'type'     => 'radio',
            'label'    => 'Cellphones are used only by authorized personnel',
            'name'     => 'cellphone_policy',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'cellphone_policy_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'cellphone_policy',
                'value' => 'fail',
            ],
        ],

        // 12. Makeup policy
        [
            'type'     => 'radio',
            'label'    => 'Employees wear discrete makeup; NO lipstick/fake lashes in production areas',
            'name'     => 'makeup_policy',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'makeup_policy_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'makeup_policy',
                'value' => 'fail',
            ],
        ],

        // 13. Illness check
        [
            'type'     => 'radio',
            'label'    => 'No employees with symptoms (runny nose, excessive cough, watering eyes, etc.) in direct food zones',
            'name'     => 'illness_check',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'illness_check_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'illness_check',
                'value' => 'fail',
            ],
        ],

        // 14. Headphone policy
        [
            'type'     => 'radio',
            'label'    => 'No headphones or personal electronic devices allowed',
            'name'     => 'headphone_policy',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'headphone_policy_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'headphone_policy',
                'value' => 'fail',
            ],
        ],

        // 15. Pen policy
        [
            'type'     => 'radio',
            'label'    => 'Pens must be capped and carried above waist',
            'name'     => 'pen_policy',
            'options'  => [
                'pass' => 'Pass',
                'fail' => 'Fail',
            ],
            'required' => true,
        ],
        [
            'type'        => 'textarea',
            'label'       => 'Observations',
            'name'        => 'pen_policy_findings',
            'conditional' => [
                'type'  => 'visibility',
                'field' => 'pen_policy',
                'value' => 'fail',
            ],
        ],
        [
            'type'     => 'radio',
            'label'    => 'Test Dynamic Field',
            'name'     => 'test_dynamic_field',
            'required' => false,
            'dynamic'  => [
                'endpoint'     => 'https://script.google.com/macros/s/AKfycbwbHAoU2CSl2O3y_QlCknO1q-PCnw2SMP6gHmS0Nn_v0HHtMZeScEKTqPYGhuQMtX5q/exec',
                'query_param'  => 'sheet=TestSheet',
                'value_field'  => 'id',
                'label_field'  => 'name',
            ],
        ],
    ],
];