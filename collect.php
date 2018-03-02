<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

(new Dotenv())->load(__DIR__ . '/.env');

$auth = new stdClass();
$auth->login = getenv('PLACETOPAY_LOGIN');
$auth->nonce = bin2hex(openssl_random_pseudo_bytes(16));
$auth->seed = date('c');
$auth->tranKey = base64_encode(hash('sha256', $auth->nonce . $auth->seed . getenv('PLACETOPAY_SECRET_KEY'), true));
$auth->nonce = base64_encode($auth->nonce);

$card = new stdClass();
$card->number = '4111111111111111';
$card->expirationMonth = '12';
$card->expirationYear = '18';
$card->cvv = '123';

$instrument = new stdClass();
$instrument->card = $card;

$amount = new stdClass();
$amount->currency = 'USD';
$amount->total = 50;

$payer = new stdClass();
$payer->name = 'Jhon';
$payer->email = 'jhon@example.org';

$payment = new stdClass();
$payment->reference = uniqid();
$payment->description = 'A payment collect example';
$payment->amount = $amount;

$request = new stdClass();
$request->auth = $auth;
$request->instrument = $instrument;
$request->payer = $payer;
$request->payment = $payment;
$request->locale = 'es_EC';
$request->ipAddress = '127.0.0.1';
$request->userAgent = 'Mozilla 5.0 ...';

$client = new Client([
    'base_uri' => getenv('PLACETOPAY_GATEWAY_BASE_URL'),
]);

try {
    $response = $client->post('collect', [
        'json' => $request,
    ]);
} catch (ClientException $exception) {
    $response = $exception->getResponse();
} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;

    exit(1);
}

print_r(json_decode($response->getBody()->getContents()));

exit(0);
