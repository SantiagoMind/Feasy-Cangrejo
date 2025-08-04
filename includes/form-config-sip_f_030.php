<?php
return [
    'title' => 'Inspección Mensual de Premisas y Seguridad',
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
        // Sección: Exterior – Building
        [
            'type' => 'section',
            'label' => 'Exterior – Building',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Parking Area clean',
                    'name' => 'parking_area_clean',
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
                    'label' => 'Loading docks free from debris and well maintained',
                    'name' => 'loading_docks_clear',
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
                    'label' => 'Garbage disposal bins are emptied adequately',
                    'name' => 'garbage_bins_emptied',
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
                    'label' => 'Doors and fence safe, video cameras working',
                    'name' => 'doors_fence_secure',
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
        // Sección: Shipping & Receiving Area
        [
            'type' => 'section',
            'label' => 'Shipping & Receiving Area',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Walls, floors, ceilings, doors undamaged and safe, no gaps',
                    'name' => 'shipping_walls_floors',
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
                    'label' => 'No evidence of pests',
                    'name' => 'no_pests_shipping',
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
                    'label' => 'Drains are operational',
                    'name' => 'drains_operational_shipping',
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
                    'label' => 'Lights are operational & covered with protective coating',
                    'name' => 'lights_operational_shipping',
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
                    'label' => 'No evidence of pins & staples attached to walls',
                    'name' => 'no_pins_staples_shipping',
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
        // Sección: All Processing Areas
        [
            'type' => 'section',
            'label' => 'All Processing Areas',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Doors, walls, floors, ceilings undamaged and safe',
                    'name' => 'processing_doors_walls',
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
                    'label' => 'Sinks have hot and cold water',
                    'name' => 'sinks_hot_cold',
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
                    'label' => 'Lights are operational & covered with protective coating',
                    'name' => 'lights_operational_processing',
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
                    'label' => 'Drains are operational',
                    'name' => 'drains_operational_processing',
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
                    'label' => 'Soap dispenser filled',
                    'name' => 'soap_dispenser_filled',
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
                    'label' => 'Sanitizer dispenser filled',
                    'name' => 'sanitizer_dispenser_filled',
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
                    'label' => 'Paper towels readily available',
                    'name' => 'paper_towels_available',
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
                    'label' => 'Video cameras working',
                    'name' => 'video_cameras_processing',
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
        // Sección: Raw Ingredients Walk-In Cooler
        [
            'type' => 'section',
            'label' => 'Raw Ingredients Walk-In Cooler',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Walls, floors, ceilings, doors undamaged and safe',
                    'name' => 'cooler_walls_floors',
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
                    'label' => 'No condensation',
                    'name' => 'no_condensation',
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
                    'label' => 'All raw ingredients are covered and off the floor',
                    'name' => 'raw_ingredients_covered',
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
        // Sección: Male & Female Locker Room
        [
            'type' => 'section',
            'label' => 'Male & Female Locker Room',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Doors, walls, floors, ceilings undamaged and safe',
                    'name' => 'locker_room_conditions',
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
                    'label' => 'Lights operational & covered with protective coverings',
                    'name' => 'locker_room_lights',
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
        // Sección: All Offices
        [
            'type' => 'section',
            'label' => 'All Offices',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Doors, walls, floors, ceilings undamaged and safe',
                    'name' => 'offices_doors_walls',
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
                    'label' => 'Lights operational & covered with protective coverings',
                    'name' => 'offices_lights',
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
                    'label' => 'No evidence of pins & staples attached to walls',
                    'name' => 'no_pins_staples_offices',
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
        // Sección: All Hallways
        [
            'type' => 'section',
            'label' => 'All Hallways',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Doors, walls, floors, ceilings undamaged and safe',
                    'name' => 'hallways_doors_walls',
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
                    'label' => 'Lights operational & covered with protective coverings',
                    'name' => 'hallways_lights',
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
                    'label' => 'No evidence of pins & staples attached to walls',
                    'name' => 'no_pins_staples_hallways',
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
        // Sección: Lunch Room
        [
            'type' => 'section',
            'label' => 'Lunch Room',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Doors, walls, floors, ceilings undamaged and safe',
                    'name' => 'lunchroom_doors_walls',
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
                    'label' => 'Sink has hot and cold water available',
                    'name' => 'lunchroom_sink',
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
                    'label' => 'Soap dispenser filled with soap',
                    'name' => 'lunchroom_soap',
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
                    'label' => 'Paper towel readily available',
                    'name' => 'lunchroom_paper_towels',
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
                    'label' => 'Lights operational & covered with protective covering',
                    'name' => 'lunchroom_lights',
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
        // Sección: Male & Female Restrooms
        [
            'type' => 'section',
            'label' => 'Male & Female Restrooms',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Doors, walls, floors, ceilings undamaged and safe',
                    'name' => 'restrooms_doors_walls',
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
                    'label' => 'Sink has hot and cold water available',
                    'name' => 'restrooms_sink',
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
                    'label' => 'Soap dispenser filled with soap',
                    'name' => 'restrooms_soap',
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
                    'label' => 'Paper towels readily available',
                    'name' => 'restrooms_paper_towels',
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
                    'label' => 'Lights operational & covered with protective covering',
                    'name' => 'restrooms_lights',
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
        // Sección: Warehouse (Packing Material Area)
        [
            'type' => 'section',
            'label' => 'Warehouse (Packing Material Area)',
            'fields' => [
                [
                    'type' => 'radio',
                    'label' => 'Doors, walls, floors, ceilings undamaged and safe',
                    'name' => 'warehouse_doors_walls',
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
                    'label' => 'Raw Ingredients are off the floor',
                    'name' => 'raw_ingredients_off_floor',
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
                    'label' => 'Packaging materials are off the floor',
                    'name' => 'packaging_off_floor',
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
                    'label' => 'Lights operational & covered with protective covering',
                    'name' => 'warehouse_lights',
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