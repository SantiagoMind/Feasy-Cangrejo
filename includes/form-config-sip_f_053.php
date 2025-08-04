<?php
return [
    'title' => 'Inspección Mensual GMP Self Inspection',
    'fields' => [
        [
            'type' => 'date',
            'label' => 'Inspection Date',
            'name' => 'inspection_date',
            'required' => true,
        ],
        [
            'type' => 'text',
            'label' => 'Plant Inspected By',
            'name' => 'plant_inspected_by',
            'required' => true,
        ],
        [
            'type' => 'select',
            'label' => 'Inspection Type',
            'name' => 'inspection_type',
            'options' => [
                'internal' => 'Internal',
                'external' => 'External',
            ],
            'required' => true,
        ],
        // Sección: Segregation of Materials
        [
            'type' => 'section',
            'label' => 'Segregation of Materials',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Raw ingredients, finished goods, and packaging materials are segregated and properly stored',
                    'name' => 'materials_segregation',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: All Equipment
        [
            'type' => 'section',
            'label' => 'All Equipment',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Equipment is intact, safe, clean & production records are available',
                    'name' => 'equipment_condition',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
                [
                    'type' => 'radio',
                    'label' => 'Maintenance carts & cabinets are free of food, medicine or any personal item',
                    'name' => 'maintenance_carts_free',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Sanitation
        [
            'type' => 'section',
            'label' => 'Sanitation',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Check records; cleaning compounds storage; color coding system followed',
                    'name' => 'sanitation',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Allergens
        [
            'type' => 'section',
            'label' => 'Allergens',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Allergens are stored and handled properly; allergens are properly marked',
                    'name' => 'allergens_management',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Temporary Repairs
        [
            'type' => 'section',
            'label' => 'Temporary Repairs',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Look for evidence of tapes, cardboard attached to utensils/equipment, record if any',
                    'name' => 'temporary_repairs',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Chemicals Segregation
        [
            'type' => 'section',
            'label' => 'Chemicals Segregation',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Food grade, non-food grade & cleaning compounds stored properly; secondary containers properly marked',
                    'name' => 'chemicals_segregation',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Pest Control
        [
            'type' => 'section',
            'label' => 'Pest Control',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Look for evidence of pests; misplaced/damaged traps; check records',
                    'name' => 'pest_control',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Plant Cleanliness (Housekeeping)
        [
            'type' => 'section',
            'label' => 'Plant Cleanliness (Housekeeping)',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Utensils, gloves, tools are in place; line clearance procedure followed; racks water bottles',
                    'name' => 'plant_cleanliness',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Pallets (Wooden & Plastic) Condition
        [
            'type' => 'section',
            'label' => 'Pallets (Wooden & Plastic) Condition',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Pallets intact; no sticking nails; stored properly',
                    'name' => 'pallets_condition',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Traceability System
        [
            'type' => 'section',
            'label' => 'Traceability System',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Partial ingredient rack: active lots on forms match lot codes on bags/containers',
                    'name' => 'traceability_system',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
        // Sección: Best By Date
        [
            'type' => 'section',
            'label' => 'Best By Date',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'All ingredients stored are marked with Best By date, on case or tag',
                    'name' => 'best_by_date_marking',
                    'options' => [
                        'pass' => 'Pass',
                        'fail' => 'Fail',
                    ],
                    'required' => true,
                    'extra' => [
                        'findings' => [
                            'label' => 'Observations',
                            'type'  => 'textarea',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
