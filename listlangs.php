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
require_once dirname(__FILE__).'/shared/packs.php';
require_once dirname(__FILE__).'/updateinfo.php';

function uupListLangs($updateId = 0) {
    if($updateId) {
        $info = uupUpdateInfo($updateId, false, true);
    }

    if(isset($info['info'])) {
        $info = $info['info'];
        unset($info['files']);
    }

    if(isset($info['build'])) {
        $build = explode('.', $info['build']);
        $build = $build[0];
    } else {
        $build = 15063;
    }

    if(!isset($info['arch'])) {
        $info['arch'] = null;
    }

    $genPack = uupGetGenPacks($build, $info['arch'], $updateId);
    $fancyTexts = uupGetInfoTexts();
    $fancyLangNames = $fancyTexts['fancyLangNames'];

    $langList = array();
    $langListFancy = array();
    foreach($genPack as $key => $val) {
        if(!count(array_diff(array_keys($val), array('LXP')))) {
            continue;
        }
        if(!count(array_diff(array_keys($val), array('FOD')))) {
            continue;
        }

        if(isset($fancyLangNames[$key])) {
            $fancyName = $fancyLangNames[$key];
        } else {
            $fancyName = $key;
        }

        $langList[] = $key;
        $langListFancy[$key] = $fancyName;
    }
   
    if(isset($info)) {
        return array(
            'apiVersion' => uupApiVersion(),
            'langList' => $langList,
            'langFancyNames' => $langListFancy,
            'updateInfo' => $info
        );
    } else {
        return array(
            'apiVersion' => uupApiVersion(),
            'langList' => $langList,
            'langFancyNames' => $langListFancy
        );
    }
}
