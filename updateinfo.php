<?php
/*
Copyright 2022 UUP dump API authors

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

require_once dirname(__FILE__).'/shared/main.php';
require_once dirname(__FILE__).'/shared/cache.php';
require_once dirname(__FILE__).'/shared/fileinfo.php';

function uupUpdateInfo($updateId, $onlyInfo = 0, $ignoreFiles = false) {
    $info = uupApiReadFileinfo($updateId, $ignoreFiles);
    if($info === false) {
        return ['error' => 'UPDATE_INFORMATION_NOT_EXISTS'];
    }

    $parsedInfo = uupParseUpdateInfo($info, $onlyInfo);
    if(isset($parsedInfo['error'])) {
        return $parsedInfo['error'];
    }

    return array(
        'apiVersion' => uupApiVersion(),
        'info' => $parsedInfo['info'],
    );
}

function uupParseUpdateInfo($info, $onlyInfo = 0) {
    if(empty($info)) {
        return ['error' => 'UPDATE_INFORMATION_NOT_EXISTS'];
    }

    if($onlyInfo) {
        if(isset($info[$onlyInfo])) {
            $returnInfo = $info[$onlyInfo];
        } else {
            return array('error' => 'KEY_NOT_EXISTS');
        }
    } else {
        $returnInfo = $info;
    }

    return array(
        'apiVersion' => uupApiVersion(),
        'info' => $returnInfo,
    );
}
