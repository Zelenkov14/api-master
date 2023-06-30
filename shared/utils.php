<?php
/*
Copyright 2020 whatever127

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

function uupApiPrintBrand() {
    global $uupApiBrandPrinted;

    if(!isset($uupApiBrandPrinted)) {
        consoleLogger('UUP dump API v'.uupApiVersion());
        $uupApiBrandPrinted = 1;
    }
}

function randStr($length = 4) {
    $characters = '0123456789abcdef';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function genUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        rand(0, 0xffff),
        rand(0, 0xffff),

        rand(0, 0xffff),

        rand(0, 0x0fff) | 0x4000,

        rand(0, 0x3fff) | 0x8000,

        rand(0, 0xffff),
        rand(0, 0xffff),
        rand(0, 0xffff)
    );
}

function sendWuPostRequest($url, $postData) {
    $req = curl_init($url);

    $proxy = uupDumpApiGetConfig();
    if(isset($proxy['proxy'])) {
        curl_setopt($req, CURLOPT_PROXY, $proxy['proxy']);
    }

    curl_setopt($req, CURLOPT_HEADER, 0);
    curl_setopt($req, CURLOPT_POST, 1);
    curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($req, CURLOPT_ENCODING, '');
    curl_setopt($req, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($req, CURLOPT_TIMEOUT, 15);
    curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($req, CURLOPT_HTTPHEADER, array(
        'User-Agent: Windows-Update-Agent/10.0.10011.16384 Client-Protocol/2.50',
        'Content-Type: application/soap+xml; charset=utf-8',
    ));

    $out = curl_exec($req);
    $error = curl_getinfo($req, CURLINFO_RESPONSE_CODE);

    curl_close($req);

    /*
    Replace an expired cookie with a new one by replacing it in existing
    postData. This has to be done this way, because handling it properly would
    most likely require a rewrite of half of the project.
    */
    if($error == 500 && preg_match('/<ErrorCode>(ConfigChanged|CookieExpired)<\/ErrorCode>/', $out)) {
        $oldCookie = uupEncryptedData();
        @unlink(dirname(__FILE__).'/cookie.json');
        $postData = str_replace($oldCookie, uupEncryptedData(), $postData);

        return sendWuPostRequest($url, $postData);
    }

    $outDecoded = html_entity_decode($out);
    preg_match('/<NewCookie>.*?<\/NewCookie>|<GetCookieResult>.*?<\/GetCookieResult>/', $outDecoded, $cookieData);

    if(!empty($cookieData)) {
        preg_match('/<Expiration>.*<\/Expiration>/', $cookieData[0], $expirationDate);
        preg_match('/<EncryptedData>.*<\/EncryptedData>/', $cookieData[0], $encryptedData);

        $expirationDate = preg_replace('/<Expiration>|<\/Expiration>/', '', $expirationDate[0]);
        $encryptedData = preg_replace('/<EncryptedData>|<\/EncryptedData>/', '', $encryptedData[0]);

        $fileData = array(
            'expirationDate' => $expirationDate,
            'encryptedData' => $encryptedData,
        );

        @file_put_contents(dirname(__FILE__).'/cookie.json', json_encode($fileData));
    }

    return $out;
}

function consoleLogger($message, $showTime = 1) {
    if(php_sapi_name() != 'cli') return
    $currTime = '';
    if($showTime) {
        $currTime = '['.date('Y-m-d H:i:s T', time()).'] ';
    }

    $msg = $currTime.$message;
    fwrite(STDERR, $msg."\n");
}

function uupDumpApiGetConfig() {
    if(!file_exists('config.ini')) {
        return null;
    }

    return parse_ini_file('config.ini');
}

function uupApiCheckUpdateId($updateId) {
    return preg_match(
        '/^[\da-fA-F]{8}-([\da-fA-F]{4}-){3}[\da-fA-F]{12}(_rev\.\d+)?$/',
        $updateId
    );
}

function uupApiIsServer($skuId) {
    $serverSkus = [
        7, 8, 12, 13, 79, 80, 120, 145, 146,
        147, 148, 159, 160, 406, 407, 408
    ];

    return in_array($skuId, $serverSkus);
}

function uupApiBuildMajor($build) {
    if($build == null)
        return null;

    if(!str_contains($build, '.'))
        return intval($build);

    return intval(explode('.', $build)[0]);
}

function uupApiFixDownloadLink($link) {
    return $link;
}

function uupApiReadJson($path) {
    $data = @file_get_contents($path);

    if(empty($data))
        return false;

    return json_decode($data, true);
}

function uupApiWriteJson($path, $data) {
    return file_put_contents($path, json_encode($data)."\n");
}

function uupApiPacksExist($updateId) {
    return file_exists('packs/'.$updateId.'.json.gz');
}

function uupApiConfigIsTrue($config) {
    $data = uupDumpApiGetConfig();

    if(!isset($data[$config]))
        return false;

    return $data[$config] == true;
}
