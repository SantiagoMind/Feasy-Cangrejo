<?php

return [
    [
        'conditions' => [
            [
                'field'    => 'employees_cloth',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['employees_cloth_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'hairnets_usage',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['hairnets_usage_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'beardnets_usage',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['beardnets_usage_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'fingernails_clean',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['fingernails_clean_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'hands_washed',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['hands_washed_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'gloves_usage',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['gloves_usage_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'footwear_usage',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['footwear_usage_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'jewelry_restriction',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['jewelry_restriction_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'hand_injury_care',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['hand_injury_care_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'eating_drinking_policy',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['eating_drinking_policy_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'cellphone_policy',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['cellphone_policy_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'makeup_policy',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['makeup_policy_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'illness_check',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['illness_check_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'headphone_policy',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['headphone_policy_findings']
            ]
        ]
    ],
    [
        'conditions' => [
            [
                'field'    => 'pen_policy',
                'operator' => 'equal_to',
                'target'   => 'value',
                'value'    => 'fail'
            ]
        ],
        'match'   => 'all',
        'actions' => [
            [
                'action'  => 'show',
                'targets' => ['pen_policy_findings']
            ]
        ]
    ],
];