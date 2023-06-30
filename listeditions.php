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

function uupListEditions($lang = 'en-us', $updateId = 0) {
    if($updateId) {
        $info = uupUpdateInfo($updateId, false, true);
    }

    if(!$lang) {
        return array('error' => 'UNSUPPORTED_LANG');
    }

    if(isset($info['info'])) $info = $info['info'];

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
    $fancyEditionNames = $fancyTexts['fancyEditionNames'];

    if($lang) {
        $lang = strtolower($lang);
        if(!isset($genPack[$lang])) {
            return array('error' => 'UNSUPPORTED_LANG');
        }
    }

    $editionList = array();
    $editionListFancy = array();
    foreach(array_keys($genPack[$lang]) as $edition) {
        if($edition == 'LXP') continue;
        if($edition == 'FOD') continue;

        if(isset($fancyEditionNames[$edition])) {
            $fancyName = $fancyEditionNames[$edition];
        } else {
            $fancyName = $edition;
        }

        $editionList[] = $edition;
        $editionListFancy[$edition] = $fancyName;
    }

    return array(
        'apiVersion' => uupApiVersion(),
        'editionList' => $editionList,
        'editionFancyNames' => $editionListFancy,
    );
}
