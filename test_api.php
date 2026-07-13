<?php
$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n" .
                     "Accept: application/json\r\n" .
                     "X-User-Id: 1\r\n",
        'content' => json_encode([
            'action' => 'close', 
            'declarations' => [
                ['payment_method_id' => 1, 'declared_amount' => 0, 'declared_amount_local' => 0]
            ]
        ]),
        'ignore_errors' => true
    ]
];
$context  = stream_context_create($opts);
$result = file_get_contents('http://localhost/capycontrol/public/api/pos/session/close', false, $context);
echo $result;
