<?php

return [
    'title'     => 'QC Inspection Checks Personal Hygiene',
    'form_name' => 'QC Inspection Checks Personal Hygiene',
    'endpoint'  => 'https://script.google.com/a/macros/mindandcreation.tech/s/AKfycbzB6un6lGbd_abp2K70R_GGqwURuuLQufCTmRX9iyhe3Rc79FUyKEukcyPqneJ1lqm7/exec',

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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'employees_cloth_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'hairnets_usage_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'beardnets_usage_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'fingernails_clean_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'hands_washed_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'gloves_usage_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'footwear_usage_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'jewelry_restriction_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'hand_injury_care_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'eating_drinking_policy_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'cellphone_policy_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'makeup_policy_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'illness_check_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'headphone_policy_findings',
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
            'type'  => 'textarea',
            'label' => 'Observations',
            'name'  => 'pen_policy_findings',
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