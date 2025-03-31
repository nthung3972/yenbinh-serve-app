<?php

return [
    'code' => [
        'reverse_code_status' => [
            'CODE_SUCCESS' => 200,
            'CODE_FAIL' => 500,
            'CODE_BAD_REQUEST' => 422,
            'CODE_NOT_FOUND' => 404,
            'PERMISSION' => 403,
            'AUTHENTICATE' => 401,
            'FAIL' => 400,
        ]
    ],

    'paginate' => env('PAGINATE', 10),

    's3_path' => [
        'update_building_image' => 'building/information/image',
        //TODO
    ],

    'vehicle_status' => [
        'ACTIVE' => 0,
        'INACTVE' => 1
    ]
];