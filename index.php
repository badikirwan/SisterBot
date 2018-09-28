<?php
/**
 * Created by PhpStorm.
 * User: badikirwan
 * Date: 9/18/18
 * Time: 8:49 PM
 */

require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// set false for production
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = "+y169x0ULGJdiLC123g4Is/99oH4Tdh0Vz4sDtw2/xxIu3/sS8lqbeaM5XGgNZZfzI7K1+NQVRqtcRdE6ZMxUTR6Wc25xS+/5E7kLbSnBQhgfSksK2/pLnPMIARgHg72scF2Ls689AmS/t22iddAKAdB04t89/1O/w1cDnyilFU=";
$channel_secret = "ddab49c58166e70490f26e06ab7e36fc";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

// buat route untuk url homepage
$app->get('/', function($req, $res)
{
    echo "Welcome at Slim Framework";
});

// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature, $channel_secret)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);

    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }

    // kode aplikasi nanti disini
    $data = json_decode($body, true);
    if(is_array($data['events'])) {
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                {
                    switch ($keyword = $event['message']['text']) 
                    {
                        case 'Hello':
                            $message = 'Hello juga';
                            $result = $bot->replyText($event['replyToken'], $message);
                            break;
                        
                        default:
                            //$message = 'Maaf, saya tidak mengerti. Bisa diulangi ?';
                            //$result = $bot->replyText($event['replyToken'], $message);
                            break;
                    }
                    // send same message as reply to user
                    //$result = $bot->replyText($event['replyToken'], $event['message']['text']);

                    // or we can use replyMessage() instead to send reply message
                    //$textMessageBuilder = new TextMessageBuilder($result1);
                    //$result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);

                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }
            }
        }
    }

});

$app->run();

