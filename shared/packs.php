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

require_once dirname(__FILE__).'/../listid.php';

function uupGetInfoTexts() {
    $fancyLangNames = array(
        'neutral' => 'Any Language',
        'ar-sa' => 'Arabic (Saudi Arabia)',
        'bg-bg' => 'Bulgarian',
        'cs-cz' => 'Czech',
        'da-dk' => 'Danish',
        'de-de' => 'German',
        'el-gr' => 'Greek',
        'en-gb' => 'English (United Kingdom)',
        'en-us' => 'English (United States)',
        'es-es' => 'Spanish (Spain)',
        'es-mx' => 'Spanish (Mexico)',
        'et-ee' => 'Estonian',
        'fi-fi' => 'Finnish',
        'fr-ca' => 'French (Canada)',
        'fr-fr' => 'French (France)',
        'he-il' => 'Hebrew',
        'hr-hr' => 'Croatian',
        'hu-hu' => 'Hungarian',
        'it-it' => 'Italian',
        'ja-jp' => 'Japanese',
        'ko-kr' => 'Korean',
        'lt-lt' => 'Lithuanian',
        'lv-lv' => 'Latvian',
        'nb-no' => 'Norwegian (Bokmal)',
        'nl-nl' => 'Dutch',
        'pl-pl' => 'Polish',
        'pt-br' => 'Portuguese (Brazil)',
        'pt-pt' => 'Portuguese (Portugal)',
        'ro-ro' => 'Romanian',
        'ru-ru' => 'Russian',
        'sk-sk' => 'Slovak',
        'sl-si' => 'Slovenian',
        'sr-latn-rs' => 'Serbian (Latin)',
        'sv-se' => 'Swedish',
        'th-th' => 'Thai',
        'tr-tr' => 'Turkish',
        'uk-ua' => 'Ukrainian',
        'zh-cn' => 'Chinese (Simplified)',
        'zh-hk' => 'Chinese (Hong Kong)',
        'zh-tw' => 'Chinese (Traditional)',
    );

    $fancyEditionNames = array(
        'APP' => 'Microsoft Store Inbox Apps',
        'FOD' => 'Features on Demand (Capabilities)',
        'CLOUD' => 'Windows S',
        'CLOUDN' => 'Windows S N',
        'CLOUDE' => 'Windows Lean',
        'CLOUDEDITION' => 'Windows SE',
        'CLOUDEDITIONN' => 'Windows SE N',
        'CORE' => 'Windows Home',
        'CORECOUNTRYSPECIFIC' => 'Windows Home China',
        'COREN' => 'Windows Home N',
        'CORESINGLELANGUAGE' => 'Windows Home Single Language',
        'EDUCATION' => 'Windows Education',
        'EDUCATIONN' => 'Windows Education N',
        'ENTERPRISE' => 'Windows Enterprise',
        'ENTERPRISEN' => 'Windows Enterprise N',
        'HOLOGRAPHIC' => 'Windows Holographic',
        'LITE' => 'Windows 10X',
        'PPIPRO' => 'Windows Team',
        'PROFESSIONAL' => 'Windows Pro',
        'PROFESSIONALN' => 'Windows Pro N',
        'SERVERSTANDARD' => 'Windows Server Standard',
        'SERVERSTANDARDCORE' => 'Windows Server Standard, Core',
        'SERVERDATACENTER' => 'Windows Server Datacenter',
        'SERVERDATACENTERCORE' => 'Windows Server Datacenter, Core',
        'SERVERAZURESTACKHCICOR' => 'Azure Stack HCI',
        'SERVERTURBINE' => 'Windows Server Datacenter Azure',
        'SERVERTURBINECOR' => 'Windows Server Datacenter Azure, Core',
        'SERVERSTANDARDACOR' => 'Windows Server Standard SAC',
        'SERVERDATACENTERACOR' => 'Windows Server Datacenter SAC',
        'SERVERARM64' => 'Windows Server ARM64',
    );

    $allEditions = array(
        'ANALOGONECORE',
        'ANDROMEDA',
        'CLOUD',
        'CLOUDE',
        'CLOUDEN',
        'CLOUDN',
        'CORE',
        'CORECOUNTRYSPECIFIC',
        'COREN',
        'CORESINGLELANGUAGE',
        'CORESYSTEMSERVER',
        'EDUCATION',
        'EDUCATIONN',
        'EMBEDDED',
        'EMBEDDEDE',
        'EMBEDDEDEEVAL',
        'EMBEDDEDEVAL',
        'ENTERPRISE',
        'ENTERPRISEEVAL',
        'ENTERPRISEG',
        'ENTERPRISEGN',
        'ENTERPRISEN',
        'ENTERPRISENEVAL',
        'ENTERPRISES',
        'ENTERPRISESEVAL',
        'ENTERPRISESN',
        'ENTERPRISESNEVAL',
        'HOLOGRAPHIC',
        'HUBOS',
        'IOTENTERPRISE',
        'IOTENTERPRISES',
        'IOTOS',
        'IOTUAP',
        'LITE',
        'MOBILECORE',
        'ONECOREUPDATEOS',
        'PPIPRO',
        'PROFESSIONAL',
        'PROFESSIONALCOUNTRYSPECIFIC',
        'PROFESSIONALEDUCATION',
        'PROFESSIONALEDUCATIONN',
        'PROFESSIONALN',
        'PROFESSIONALSINGLELANGUAGE',
        'PROFESSIONALWORKSTATION',
        'PROFESSIONALWORKSTATIONN',
        'SERVERARM64',
        'SERVERARM64CORE',
        'SERVERAZURECOR',
        'SERVERAZURECORCORE',
        'SERVERAZURENANO',
        'SERVERAZURENANOCORE',
        'SERVERCLOUDSTORAGE',
        'SERVERCLOUDSTORAGECORE',
        'SERVERDATACENTER',
        'SERVERDATACENTERACOR',
        'SERVERDATACENTERACORCORE',
        'SERVERDATACENTERCOR',
        'SERVERDATACENTERCORCORE',
        'SERVERDATACENTERCORE',
        'SERVERDATACENTEREVAL',
        'SERVERDATACENTEREVALCOR',
        'SERVERDATACENTEREVALCORCORE',
        'SERVERDATACENTEREVALCORE',
        'SERVERDATACENTERNANO',
        'SERVERDATACENTERNANOCORE',
        'SERVERHYPERCORE',
        'SERVERRDSH',
        'SERVERRDSHCORE',
        'SERVERSOLUTION',
        'SERVERSOLUTIONCORE',
        'SERVERSTANDARD',
        'SERVERSTANDARDACOR',
        'SERVERSTANDARDACORCORE',
        'SERVERSTANDARDCOR',
        'SERVERSTANDARDCORCORE',
        'SERVERSTANDARDCORE',
        'SERVERSTANDARDEVAL',
        'SERVERSTANDARDEVALCOR',
        'SERVERSTANDARDEVALCORCORE',
        'SERVERSTANDARDEVALCORE',
        'SERVERSTANDARDNANO',
        'SERVERSTANDARDNANOCORE',
        'SERVERSTORAGESTANDARD',
        'SERVERSTORAGESTANDARDCORE',
        'SERVERSTORAGESTANDARDEVAL',
        'SERVERSTORAGESTANDARDEVALCORE',
        'SERVERSTORAGEWORKGROUP',
        'SERVERSTORAGEWORKGROUPCORE',
        'SERVERSTORAGEWORKGROUPEVAL',
        'SERVERSTORAGEWORKGROUPEVALCORE',
        'SERVERAZURESTACKHCICOR',
        'SERVERTURBINE',
        'SERVERTURBINECOR',
        'SERVERWEB',
        'SERVERWEBCORE',
        'STARTER',
        'STARTERN',
    );

    return array(
        'fancyEditionNames' => $fancyEditionNames,
        'fancyLangNames' => $fancyLangNames,
        'allEditions' => $allEditions,
    );
}

function uupGetGenPacks($build = 15063, $arch = null, $updateId = null) {
    if(empty($updateId)) return [];
    if(!file_exists('packs/'.$updateId.'.json.gz')) return [];

    $genPack = @gzdecode(@file_get_contents('packs/'.$updateId.'.json.gz'));
    if(empty($genPack)) return [];

    $genPack = json_decode($genPack, 1);
    return $genPack;
}
