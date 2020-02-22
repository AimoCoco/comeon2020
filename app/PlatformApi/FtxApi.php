<?php
/**
 * Created by PhpStorm.
 * User: gundam
 * Date: 2020/2/8
 * Time: 2:41 PM
 */

namespace App\PlatformApi;


use Illuminate\Support\Facades\Log;

class FtxApi
{
    const API_URL = 'https://ftx.com';
    const API_PATH = '/api/';
    const SYMBOL = 'XBTUSD';

    private $apiKey;
    private $apiSecret;
    private $subName;

    private $ch;

    public $error;
    public $printErrors = false;
    public $errorCode;
    public $errorMessage;

    public function __construct($apiKey = '', $apiSecret = '', $subName = '') {

        if (empty($apiSecret) || empty($apiSecret)) {
            $apiKey = config('auth.ftx.key1');
            $apiSecret = config('auth.ftx.secret1');
        }
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        if ($subName) {
            $this->subName = $subName;
        }

        $this->curlInit();

    }

    /**
     * 期货-列出所有期货
     * @return array|bool|mixed
     */
    public function getFutures()
    {
        $data['function'] = "futures";
        $data['params'] = array();

        return $this->publicQuery($data)['result'];
    }

    /**
     * 期货-获取某期货
     * @return array|bool|mixed
     */
    public function getOneFuture($coin)
    {
        $data['function'] = "/futures/" . $coin;
        $data['params'] = array();

        return $this->publicQuery($data)['result'];
    }

    public function getAllBalance()
    {
        $data['method'] = "GET";
        $data['function'] = "wallet/balances";
        $data['params'] = array();

        return $this->authQuery($data);
    }

    public function getAllBalances()
    {
        $data['method'] = "GET";
        $data['function'] = "wallet/all_balances";
        $data['params'] = array();

        return $this->authQuery($data);
    }

    public function orderWithMarket($side = 'sell', $size = 0)
    {
        $data['method'] = "POST";
        $data['function'] = "orders";
        $data['params'] = [
            'market' => 'BTC-PERP',
            'side' => $side,
            'type' => 'market',
            'size' => $size,
            'price' => null,
        ];

        return $this->authQuery($data);

//        "result" => [
//            "avgFillPrice" => null
//            "clientId" => null
//            "createdAt" => "2020-02-22T06:38:26.387359+00:00"
//            "filledSize" => 0.0
//            "future" => "BTC-PERP"
//            "id" => 3354497268
//            "ioc" => true
//            "market" => "BTC-PERP"
//            "postOnly" => false
//            "price" => null
//            "reduceOnly" => false
//            "remainingSize" => 0.001
//            "side" => "sell"
//            "size" => 0.001
//            "status" => "new"
//            "type" => "market"
//          ]
//        "success" => true
    }

    public function getOrdersHistory()
    {
        $data['method'] = "GET";
        $data['function'] = "orders/history";
        $data['params'] = [
            'market' => 'BTC-PERP'
        ];

        return $this->authQuery($data);
    }

    public function getOptionsPositions()
    {
        $data['method'] = "GET";
        $data['function'] = "options/positions";
        $data['params'] = [];

        return $this->authQuery($data);
    }

    public function getOptionsAccountInfo()
    {
        $data['method'] = "GET";
        $data['function'] = "options/account_info";
        $data['params'] = [];

        return $this->authQuery($data);
    }

    /*
    * Curl Init
    *
    * Init curl header to support keep-alive connection
    */
    private function curlInit() {

        $this->ch = curl_init();

    }

    /*
    * Curl Error
    *
    * @return false
    */
    private function curlError() {

        if ($errno = curl_errno($this->ch)) {
            $this->errorCode = $errno;
            $errorMessage = curl_strerror($errno);
            $this->errorMessage = $errorMessage;
            if($this->printErrors) echo "cURL error ({$errno}) : {$errorMessage}\n";
            return true;
        }

        return false;
    }

    /*
    * Platform Error
    *
    * @return false
    */
    private function platformError($return) {

        $this->errorCode = 500;
        $this->errorMessage = json_encode($return);
        if($this->printErrors) echo "platform error : {$this->errorMessage}\n";
        Log::debug($this->errorMessage);

        return true;
    }

    /*
    * Generate Nonce
    *
    * @return string
    */
    private function generateNonce() {

        $nonce = (string) number_format(round(microtime(true) * 1000), 0, '.', '');

        return $nonce;

    }

    /*
    * Public Query
    *
    * Query for public queries only
    *
    * @param $data consists function,params
    *
    * @return return array
    */
    private function publicQuery($data) {

        $function = $data['function'];
        $params = http_build_query($data['params']);
        $url = self::API_URL . self::API_PATH . $function . "?" . $params;;

        $headers = array();

        $headers[] = 'Connection: Keep-Alive';
        $headers[] = 'Keep-Alive: 90';

        curl_reset($this->ch);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        $return = curl_exec($this->ch);

        if(!$return) {
            $this->curlError();
            $this->error = true;
            return false;
        }

        $return = json_decode($return,true);

        if(!isset($return['success']) || !$return['success']) {
            $this->platformError($return);
            $this->error = true;
            return false;
        }

        $this->error = false;
        $this->errorCode = false;
        $this->errorMessage = false;

        return $return;

    }

    /*
     * Auth Query
     *
     * Query for authenticated queries only
     *
     * @param $data consists method (GET,POST,DELETE,PUT),function,params
     *
     * @return return array
     */
    private function authQuery($data) {

        $method = $data['method'];
        $function = $data['function'];
        $params = http_build_query($data['params']);
        $path = self::API_PATH . $function;
        $url = self::API_URL . self::API_PATH . $function;
        if($method == "GET" && count($data['params']) >= 1) {
            $url .= "?" . $params;
            $path .= "?" . $params;
        }
        $nonce = $this->generateNonce();
        if($method == "GET") {
            $post = "";
        }
        else {
            $post = $this->json($data['params']);
        }

        $payload = $nonce . $method . $path . $post;
        $sign = hash_hmac('sha256', $payload, $this->apiSecret);

        $headers = array();

        $headers[] = "FTX-KEY: {$this->apiKey}";
        $headers[] = "FTX-TS: {$nonce}";
        $headers[] = "FTX-SIGN: {$sign}";
        if ($this->subName) {
            $headers[] = "FTX-SUBACCOUNT: {$this->subName}";
        }

        $headers[] = 'Connection: Keep-Alive';
        $headers[] = 'Keep-Alive: 90';

        curl_reset($this->ch);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        if($data['method'] == "POST") {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
            $headers[] = 'Content-Type: application/json';
        }
        if($data['method'] == "DELETE") {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
            $headers[] = 'X-HTTP-Method-Override: DELETE';
        }
        if($data['method'] == "PUT") {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
            $headers[] = 'X-HTTP-Method-Override: PUT';
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        $return = curl_exec($this->ch);

        if(!$return) {
            $this->curlError();
            $this->error = true;
            return false;
        }

        $return = json_decode($return,true);

        if(!isset($return['success']) || !$return['success']) {
            $this->platformError($return);
            $this->error = true;
            return false;
        }

        $this->error = false;
        $this->errorCode = false;
        $this->errorMessage = false;

        return $return['result'];

    }

    public function json($data, $params = array()) {
        $options = array(
            'convertArraysToObjects' => JSON_FORCE_OBJECT,
            // other flags if needed...
        );
        $flags = 0;
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $params) && $params[$key]) {
                $flags |= $options[$key];
            }
        }
        return json_encode($data, $flags);
    }
}