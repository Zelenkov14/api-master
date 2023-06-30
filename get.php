<?php
/*
Copyright 2021 whatever127

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
require_once dirname(__FILE__).'/shared/requests.php';
require_once dirname(__FILE__).'/shared/packs.php';
require_once dirname(__FILE__).'/shared/cache.php';
require_once dirname(__FILE__).'/shared/fileinfo.php';

/*
$updateId       = Update Identifier
$usePack        = Desired language
$desiredEdition = Desired edition

$requestType    = 0 = uncached request,;
                  1 = use cache if available;
                  2 = offline information retrieval
*/

function uupGetFiles(
    $updateId = 'c2a1d787-647b-486d-b264-f90f3782cdc6',
    $usePack = 0,
    $desiredEdition = 0,
    $requestType = 0
) {
    uupApiPrintBrand();

    if(!$updateId) {
        return array('error' => 'UNSPECIFIED_UPDATE');
    }

    if(!uupApiCheckUpdateId($updateId)) {
        return array('error' => 'INCORRECT_ID');
    }

    $edition = is_array($desiredEdition) ? implode('_', $desiredEdition) : $desiredEdition;
    $res = "api-get-{$updateId}_{$usePack}_{$edition}_{$requestType}";
    $cache = new UupDumpCache($res);
    $fromCache = $cache->get();
    if($fromCache !== false) return $fromCache;

    $info = uupApiReadFileinfo($updateId);
    if(empty($info)) {
        $info = array(
            'ring' => 'WIF',
            'flight' => 'Active',
            'arch' => 'amd64',
            'checkBuild' => '10.0.16251.0',
            'sku' => '48',
            'files' => array(),
        );
    }

    if(isset($info['build'])) {
        $build = explode('.', $info['build']);
        $build = $build[0];
    } else {
        $build = 9841;
    }

    if(!isset($info['sku'])) {
        $info['sku'] = 48;
    }

    if($usePack) {
        $genPack = uupGetGenPacks($build, $info['arch'], $updateId);
        if(empty($genPack)) return array('error' => 'UNSUPPORTED_COMBINATION');

        if(!isset($genPack[$usePack])) {
            return array('error' => 'UNSUPPORTED_LANG');
        }
    }

    $appEdition = 0;

    if(!is_array($desiredEdition)) {
        $desiredEdition = strtoupper($desiredEdition);
        $fileListSource = $desiredEdition;

        switch($desiredEdition) {
            case '0':
                if($usePack) {
                    $fileListSource = 'GENERATEDPACKS';

                    $filesPacksList = array();
                    foreach($genPack[$usePack] as $val) {
                        foreach($val as $package) {
                            $filesPacksList[] = $package;
                        }
                    }

                    array_unique($filesPacksList);
                    sort($filesPacksList);
                }
                break;

            case 'WUBFILE': break;

            case 'UPDATEONLY': break;

            case 'APP': $appEdition = 1;

            default:
                if(!isset($genPack[$usePack][$desiredEdition])) {
                    return array('error' => 'UNSUPPORTED_COMBINATION');
                }

                $filesPacksList = $genPack[$usePack][$desiredEdition];
                $fileListSource = 'GENERATEDPACKS';
                break;
        }
    } else {
        $fileListSource = 'GENERATEDPACKS';
        $filesPacksList = array();
        foreach($desiredEdition as $edition) {
            $edition = strtoupper($edition);

            if(!isset($genPack[$usePack][$edition])) {
                return array('error' => 'UNSUPPORTED_COMBINATION');
            }

            $filesPacksList = array_merge($filesPacksList, $genPack[$usePack][$edition]);
        }
    }

    $rev = 1;
    if(preg_match('/_rev\./', $updateId)) {
        $rev = preg_replace('/.*_rev\./', '', $updateId);
        $updateId = preg_replace('/_rev\..*/', '', $updateId);
    }

    $updateSku = $info['sku'];
    $updateArch = (isset($info['arch'])) ? $info['arch'] : 'UNKNOWN';
    $updateBuild = (isset($info['build'])) ? $info['build'] : 'UNKNOWN';
    $updateName = (isset($info['title'])) ? $info['title'] : 'Unknown update: '.$updateId;
    $sha256capable = isset($info['sha256ready']);
    $hasUpdates = false;

    if(isset($info['releasetype'])) {
        $type = $info['releasetype'];
    }
    if(!isset($type)) {
        $type = 'Production';
        if($updateSku == 189 || $updateSku == 135) foreach($info['files'] as $val) {
            if(preg_match('/NonProductionFM/i', $val['name'])) $type = 'Test';
        }
    }

    if($requestType < 2) {
        $filesInfoList = uupGetOnlineFiles($updateId, $rev, $info, $requestType, $type);
    } else {
        $filesInfoList = uupGetOfflineFiles($info);
    }

    if(isset($filesInfoList['error'])) {
        return $filesInfoList;
    }

    $diffs = preg_grep('/.*_Diffs_.*|.*_Forward_CompDB_.*|\.cbsu\.cab$/i', array_keys($filesInfoList));
    foreach($diffs as $val) {
        if(isset($filesInfoList[$val])) unset($filesInfoList[$val]);
    }

    $baseless = preg_grep('/^baseless_/i', array_keys($filesInfoList));
    foreach($baseless as $val) {
        if(isset($filesInfoList[$val])) unset($filesInfoList[$val]);
    }

    $expresscab = preg_grep('/Windows(10|11)\.0-KB.*-EXPRESS|SSU-.*-EXPRESS/i', array_keys($filesInfoList));

    $expresspsf = array();
    foreach($expresscab as $val) {
        $name = preg_replace('/-EXPRESS.cab$/i', '', $val);
        $expresspsf[] = $name;
        if(isset($filesInfoList[$val])) unset($filesInfoList[$val]);
    }
    unset($index, $name, $expresscab);

    foreach($expresspsf as $val) {
        if(isset($filesInfoList[$val.'.cab'])) {
            if(isset($filesInfoList[$val.'.psf'])) unset($filesInfoList[$val.'.psf']);
        }
    }
    unset($expresspsf);

    $psf = array_keys($filesInfoList);
    $psf = preg_grep('/\.psf$/i', $psf);

    $psfk = preg_grep('/Windows(10|11)\.0-KB.*/i', $psf);
    $psfk = preg_grep('/.*-EXPRESS/i', $psfk, PREG_GREP_INVERT);
    if($build < 17763) $psfk = preg_grep('/Windows(10|11)\.0-KB.*_\d\.psf$/i', $psfk, PREG_GREP_INVERT);
    foreach($psfk as $key => $val) {
        if(isset($psf[$key])) unset($psf[$key]);
    }
    unset($psfk);

    $removeFiles = array();
    foreach($psf as $val) {
        $name = preg_replace('/\.psf$/i', '', $val);
        $removeFiles[] = $name;
        unset($filesInfoList[$val]);
    }
    unset($index, $name, $psf);

    $temp = preg_grep('/'.$updateArch.'_.*|arm64\.arm_.*|arm64\.x86_.*/i', $removeFiles);
    foreach($temp as $key => $val) {
        if(isset($filesInfoList[$val.'.cab'])) unset($filesInfoList[$val.'.cab']);
        unset($removeFiles[$key]);
    }
    unset($temp);

    foreach($removeFiles as $val) {
        if(isset($filesInfoList[$val.'.esd'])) {
            if(isset($filesInfoList[$val.'.cab'])) unset($filesInfoList[$val.'.cab']);
        }
    }
    unset($removeFiles);

    $msu = array_keys($filesInfoList);
    $msu = preg_grep('/\.msu$/i', $msu);
    $removeMSUs = array();
    foreach($msu as $val) {
        $name = preg_replace('/\.msu$/i', '', $val);
        $removeMSUs[] = $name;
    }
    unset($index, $name, $msu);

    $filesInfoKeys = array_keys($filesInfoList);
    $updatesRegex = '/Windows(10|11)\.0-KB|SSU-.*?\....$/i';

    switch($fileListSource) {
        case 'UPDATEONLY':
            $skipPackBuild = 1;
            $removeFiles = preg_grep('/Windows(10|11)\.0-KB.*-baseless/i', $filesInfoKeys);

            foreach($removeFiles as $val) {
                if(isset($filesInfoList[$val])) unset($filesInfoList[$val]);
            }
            unset($removeFiles);

            foreach($removeMSUs as $val) {
                if(isset($filesInfoList[$val.'.cab']) && isset($filesInfoList[$val.'.msu'])) {
                    unset($filesInfoList[$val.'.msu']);
                }
            }
            unset($removeMSUs);

            $filesInfoKeys = array_keys($filesInfoList);
            $temp = preg_grep('/.*?AggregatedMetadata.*?\.cab|.*?DesktopDeployment.*?\.cab/i', $filesInfoKeys);

            $filesInfoKeys = preg_grep($updatesRegex, $filesInfoKeys);
            if(count($filesInfoKeys) == 0) {
                return array('error' => 'NOT_CUMULATIVE_UPDATE');
            }

            if($build > 21380) $filesInfoKeys = array_merge($filesInfoKeys, $temp);
            unset($temp);
            $hasUpdates = true;
            break;

        case 'WUBFILE':
            $skipPackBuild = 1;
            $filesInfoKeys = preg_grep('/WindowsUpdateBox.exe/i', $filesInfoKeys);
            break;
    }

    $uupCleanFunc = 'uupCleanName';
    if($updateSku == 189) $uupCleanFunc = 'uupCleanWCOS';
    if($updateSku == 135) $uupCleanFunc = 'uupCleanHolo';

    if($fileListSource == 'GENERATEDPACKS') {
        foreach($removeMSUs as $val) {
            if(isset($filesInfoList[$val.'.cab']) && isset($filesInfoList[$val.'.msu'])) {
                unset($filesInfoList[$val.'.msu']);
            }
        }
        unset($removeMSUs);
        $filesInfoKeys = array_keys($filesInfoList);

        $temp = preg_grep('/Windows(10|11)\.0-KB.*-baseless/i', $filesInfoKeys, PREG_GREP_INVERT);
        if($appEdition) {
            $temp = preg_grep('/.*?AggregatedMetadata.*?\.cab|.*?DesktopDeployment.*?\.cab/i', $temp);
        } else if($build > 21380) {
            $temp = preg_grep('/Windows(10|11)\.0-KB|SSU-.*?\....$|.*?AggregatedMetadata.*?\.cab|.*?DesktopDeployment.*?\.cab/i', $temp);
        } else {
            $temp = preg_grep($updatesRegex, $temp);
        }

        $hasUpdates = !empty(preg_grep($updatesRegex, $temp));
        $filesPacksList = array_merge($filesPacksList, $temp);

        $newFiles = array();
        $failedFile = false;
        if($sha256capable) {
            $tmp = [];
            foreach($filesInfoList as $key => $val) {
                $tmp[$val['sha256']] = $key;
            }

            foreach($filesPacksList as $val) {
                if(isset($tmp[$val])) {
                    $name = $tmp[$val];
                    $newFiles[$name] = $filesInfoList[$name];
                } else if(isset($filesInfoList[$val])) {
                    $name = $val;
                    $newFiles[$name] = $filesInfoList[$name];
                } else {
                    $failedFile = true;
                    consoleLogger("Missing file: $val");
                }
            }
        } else {
            foreach($filesPacksList as $val) {
                $name = $uupCleanFunc($val);
                $filesPacksKeys[] = $name;

                if(isset($filesInfoList[$name])) {
                    $newFiles[$name] = $filesInfoList[$name];
                } else {
                    $failedFile = true;
                    consoleLogger("Missing file: $name");
                }
            }
        }

        if($failedFile) {
            return array('error' => 'MISSING_FILES');
        }

        $filesInfoList = $newFiles;
        $filesInfoKeys = array_keys($filesInfoList);
    }

    if(empty($filesInfoKeys)) {
        return array('error' => 'NO_FILES');
    }

    $filesNew = array();
    foreach($filesInfoKeys as $val) {
       $filesNew[$val] = $filesInfoList[$val];
       $filesNew[$val]['url'] = uupApiFixDownloadLink($filesInfoList[$val]['url']);
    }

    $files = $filesNew;
    ksort($files);

    consoleLogger('Successfully parsed the information.');

    $data = [
        'apiVersion' => uupApiVersion(),
        'updateName' => $updateName,
        'arch' => $updateArch,
        'build' => $updateBuild,
        'sku' => $updateSku,
        'hasUpdates' => $hasUpdates,
        'files' => $files,
    ];

    if($requestType > 0) {
        $cacheData = $data;
        $cache->put($cacheData, 30);
    }

    return $data;
}

function uupGetOnlineFiles($updateId, $rev, $info, $cacheRequests, $type) {
    $res = "api-get-online-{$updateId}_rev.$rev";
    $cache = new UupDumpCache($res);
    $fromCache = $cache->get();
    $cached = ($fromCache !== false);

    if($cached) {
        $out = $fromCache['out'];
        $fetchTime = $fromCache['fetchTime'];
    } else {
        $fetchTime = time();
        consoleLogger('Fetching information from the server...');
        $postData = composeFileGetRequest($updateId, uupDevice(), $info, $rev, $type);
        $out = sendWuPostRequest('https://fe3cr.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured', $postData);
        consoleLogger('Information has been successfully fetched.');
    }

    consoleLogger('Parsing information...');
    $xmlOut = @simplexml_load_string($out);
    if($xmlOut === false) {
        $cache->delete();
        return array('error' => 'XML_PARSE_ERROR');
    }

    $xmlBody = $xmlOut->children('s', true)->Body->children();

    if(!isset($xmlBody->GetExtendedUpdateInfo2Response)) {
        consoleLogger('An error has occurred');
        return array('error' => 'EMPTY_FILELIST');
    }

    $getResponse = $xmlBody->GetExtendedUpdateInfo2Response;
    $getResult = $getResponse->GetExtendedUpdateInfo2Result;

    if(!isset($getResult->FileLocations)) {
        consoleLogger('An error has occurred');
        return array('error' => 'EMPTY_FILELIST');
    }

    $uupCleanFunc = 'uupCleanName';
    if($info['sku'] == 189) $uupCleanFunc = 'uupCleanWCOS';
    if($info['sku'] == 135) $uupCleanFunc = 'uupCleanHolo';

    $sha256capable = isset($info['sha256ready']);

    $fileLocations = $getResult->FileLocations;
    $info = $info['files'];

    $files = array();
    foreach($fileLocations->FileLocation as $val) {
        $sha1 = bin2hex(base64_decode((string)$val->FileDigest));
        $sha256 = isset($info[$sha1]['sha256']) ? $info[$sha1]['sha256'] : null;
        $url = (string)$val->Url;

        preg_match('/files\/(.{8}-.{4}-.{4}-.{4}-.{12})/', $url, $guid);
        $guid = $guid[1];

        if(empty($info[$sha1]['name'])) {
            $name = $guid;
            $size = -1;
        } else {
            $name = $info[$sha1]['name'];
            $size = $info[$sha1]['size'];
        }

        if($sha256capable) {
            $tempname = uupCleanSha256($name);
            if(isset($files[$tempname])) {
                if($size > $files[$tempname]['size']) {
                    $smaller = uupAppendSha1($tempname, $files[$tempname]['sha1']);
                    $files[$smaller] = $files[$tempname];
                    unset($files[$tempname]);
                    $newName = $tempname;
                } else {
                    $newName = uupAppendSha1($tempname, $sha1);
                }
            } else {
                $newName = $tempname;
            }
        } else {
            $newName = $uupCleanFunc($name);
        }

        if(!isset($fileSizes[$newName])) $fileSizes[$newName] = -2;

        if($size > $fileSizes[$newName]) {
            preg_match('/P1=(.*?)&/', $url, $expire);
            if(isset($expire[0])) {
                $expire = $expire[1];
            }

            $expire = intval($expire);

            if($size < 0) {
                $temp = ($expire - $fetchTime) / 600;
                $size = ($temp - 1) * 31457280;
                if($size < 0) $size = 0;
                unset($temp);
            }

            $fileSizes[$newName] = $size;

            $temp = array();
            $temp['sha1'] = $sha1;
            $temp['sha256'] = $sha256;
            $temp['size'] = $size;
            $temp['url'] = $url;
            $temp['uuid'] = $guid;
            $temp['expire'] = $expire;
            $temp['debug'] = $val->asXML();

            $files[$newName] = $temp;
        }
    }

    if($cacheRequests == 1 && $cached == 0) {
        $cacheData = [
            'out' => $out,
            'fetchTime' => $fetchTime,
        ];

        $cache->put($cacheData, 90);
    }

    return $files;
}

function uupGetOfflineFiles($info) {
    if(empty($info['files'])) return array();

    $uupCleanFunc = 'uupCleanName';
    if($info['sku'] == 189) $uupCleanFunc = 'uupCleanWCOS';
    if($info['sku'] == 135) $uupCleanFunc = 'uupCleanHolo';

    $sha256capable = isset($info['sha256ready']);

    consoleLogger('Parsing information...');
    foreach($info['files'] as $sha1 => $val) {
        $name = $val['name'];
        $size = $val['size'];
        $sha256 = isset($val['sha256']) ? $val['sha256'] : null;

        if($sha256capable) {
            $tempname = uupCleanSha256($name);
            if(isset($files[$tempname])) {
                if($size > $files[$tempname]['size']) {
                    $smaller = uupAppendSha1($tempname, $files[$tempname]['sha1']);
                    $files[$smaller] = $files[$tempname];
                    unset($files[$tempname]);
                    $newName = $tempname;
                } else {
                    $newName = uupAppendSha1($tempname, $sha1);
                }
            } else {
                $newName = $tempname;
            }
        } else {
            $newName = $uupCleanFunc($name);
        }

        if(!isset($fileSizes[$newName])) $fileSizes[$newName] = 0;

        if($size > $fileSizes[$newName]) {
            $fileSizes[$newName] = $size;

            $temp = array();
            $temp['sha1'] = $sha1;
            $temp['sha256'] = $sha256;
            $temp['size'] = $size;
            $temp['url'] = null;
            $temp['uuid'] = null;
            $temp['expire'] = 0;
            $temp['debug'] = null;

            $files[$newName] = $temp;
        }
    }

    return $files;
}

function uupAppendSha1($name, $sha1) {
    $n = strrpos($name, '.');
    if($n === false) $n = strlen($name);
    return substr($name, 0, $n).'_'.substr($sha1, 0, 8).substr($name, $n);
}

function uupCleanSha256($name) {
    $replace = array(
        'prss_signed_appx_' => null,
        '~31bf3856ad364e35' => null,
        '~~.' => '.',
        '~.' => '.',
        '~' => '-',
    );

    return strtr($name, $replace);
}

function uupCleanName($name) {
    $replace = array(
        'cabs_' => null,
        'metadataesd_' => null,
        'prss_signed_appx_' => null,
        '~31bf3856ad364e35' => null,
        '~~.' => '.',
        '~.' => '.',
        '~' => '-',
    );

    $name = strtr($name, 'QWERTYUIOPASDFGHJKLZXCVBNM', 'qwertyuiopasdfghjklzxcvbnm');
    return strtr($name, $replace);
}

function uupCleanWCOS($name) {
    $name = preg_replace('/^(appx)_(messaging_desktop|.*?)_/i', '$1/$2/', $name);
    $name = preg_replace('/^(retail)_(.{3,5})_fre_/i', '$1/$2/fre/', $name);
    return strtr($name, 'QWERTYUIOPASDFGHJKLZXCVBNM', 'qwertyuiopasdfghjklzxcvbnm');
}

function uupCleanHolo($name) {
    $name = preg_replace('/^(appx)_(Cortana_WCOS|FeedbackHub_WCOS|HEVCExtension_HoloLens|MixedRealityViewer_arm64|MoviesTV_Hololens|Outlook_WindowsTeam|WinStore_HoloLens)_/i', '$1/$2/', $name);
    $name = preg_replace('/^(appx)_(.*?)_/i', '$1/$2/', $name);
    $name = preg_replace('/^(retail)_(.{3,5})_fre_/i', '$1/$2/fre/', $name);
    return strtr($name, 'QWERTYUIOPASDFGHJKLZXCVBNM', 'qwertyuiopasdfghjklzxcvbnm');
}
