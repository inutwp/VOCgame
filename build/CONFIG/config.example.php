<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Environment Config
    |--------------------------------------------------------------------------
    |
    */
    'env' => [
        'appID' => 1,
        'appVersion' => 'v1.0.0'
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Config
    |--------------------------------------------------------------------------
    |
    */
   	'database' => [
		'host'	=> 'localhost',
		'user'	=> 'root',
		'pass' 	=> 'password',
		'db' 	=> 'database',
		'port' 	=> '3306'
	],

    /*
    |--------------------------------------------------------------------------
    | Require Class List
    |--------------------------------------------------------------------------
    |
    */
    'autoload' => [
        'helper',
        'db',
        'api'
    ],

    /*
    |--------------------------------------------------------------------------
    | Function Allowed List
    |--------------------------------------------------------------------------
    |
    */
    'function' => [
        'CreateSession',
		'GetVoucher',
        'TopUp',
		'CommitPayment'
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Declare
    |--------------------------------------------------------------------------
    |
    | errorCode : http error code
    | errorDesc: description of error
    | priority : priority error, urgent,high,normal
    | errorMessage : error message content
    |
    */
    'error' => [
        'ise' => [
            'errorCode' => 500,
            'errorDesc' => 'internal server error',
            'priority' => 'high',
            'errorMessage' => 'internal server error'
        ],
        'bad' => [
            'errorCode' => 400,
            'errorDesc' => 'bad request',
            'priority' => 'high',
            'errorMessage' => 'bad request'
        ],
        'end' => [
            'errorCode' => 512,
            'errorDesc' => 'error not define',
            'priority' => 'urgent',
            'errorMessage' => 'not define error %s'
        ],
        'db' => [
            'errorCode' => 513,
            'errorDesc' => 'error connecting database',
            'priority' => 'urgent',
            'errorMessage' => 'database error'
        ],
        'auth' => [
            'errorCode' => 420,
            'errorDesc' => 'authentication failed',
            'priority' => 'high',
            'errorMessage' => 'authentication failed'
        ],
        'sto' => [
            'errorCode' => 433,
            'errorDesc' => 'session time out',
            'priority' => 'low',
            'errorMessage' => 'session time out'
        ],
        'sessid' => [
            'errorCode' => 434,
            'errorDesc' => 'sessid not provided',
            'priority' => 'low',
            'errorMessage' => 'sessid not provided'
        ],
        'param' => [
            'errorCode' => 435,
            'errorDesc' => 'param not provided',
            'priority' => 'low',
            'errorMessage' => 'param %s not provided'
        ],
        'phone' => [
            'errorCode' => 514,
            'errorDesc' => 'phone number not registered',
            'priority' => 'low',
            'errorMessage' => 'nomor tidak terdaftar, check kembali nomor yang anda masukkan'
        ],
        'cd' => [
            'errorCode' => 515,
            'errorDesc' => 'time limit request',
            'priority' => 'low',
            'errorMessage' => 'mohon tunggu 5 menit untuk melanjutkan transaksi'
        ],
        'pna' => [
            'errorCode' => 516,
            'errorDesc' => 'product not available',
            'priority' => 'low',
            'errorMessage' => 'produk yang anda minta untuk saat ini sedang tidak dapat diakses'
        ],
        'ana' => [
            'errorCode' => 436,
            'errorDesc' => 'access not allowed',
            'priority' => 'urgent',
            'errorMessage' => 'access not allowed'
        ],
        'fund' => [
            'errorCode' => 517,
            'errorDesc' => 'cant process payment',
            'priority' => 'urgent',
            'errorMessage' => 'tidak dapat melanjutkan pembayaran %s. saldo tidak cukup'
        ],
        'ltrans' => [
            'errorCode' => 518,
            'errorDesc' => 'limit transaction',
            'priority' => 'low',
            'errorMessage' => 'maaf transaksi anda dibatasi'
        ],
        'cdt' => [
            'errorCode' => 519,
            'errorDesc' => 'time limit transaction',
            'priority' => 'low',
            'errorMessage' => 'mohon tunggu 15 menit untuk transaksi berikutnnya'
        ],
        'fgcemp' => [
            'errorCode' => 520,
            'errorDesc' => 'empty response',
            'priority' => 'low',
            'errorMessage' => 'empty response'
        ]
    ]
];
