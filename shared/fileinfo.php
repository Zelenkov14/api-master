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

require_once dirname(__FILE__).'/utils.php';

function uupApiGetFileinfoDirs() {
    $dirs = [];
    $dirs['fileinfo'] = 'fileinfo';
    $dirs['fileinfoMeta'] = $dirs['fileinfo'].'/metadata';
    $dirs['fileinfoData'] = $dirs['fileinfo'].'/full';

    foreach($dirs as $dir) {
        if(!file_exists($dir)) mkdir($dir);
    }

    return $dirs;
}

function uupApiGetFileinfoName($updateId, $meta = false) {
    $fileName = $updateId.'.json';
    $dirs = uupApiGetFileinfoDirs();

    $fileinfoMeta = $dirs['fileinfoMeta'].'/'.$fileName;
    $fileinfoData = $dirs['fileinfoData'].'/'.$fileName;

    return $meta ? $fileinfoMeta : $fileinfoData;
}

function uupApiFileInfoExists($updateId) {
    return file_exists(uupApiGetFileinfoName($updateId));
}

function uupApiWriteFileinfoMeta($updateId, $info) {
    if(isset($info['files']))
        unset($info['files']);

    $file = uupApiGetFileinfoName($updateId, true);
    return uupApiWriteJson($file, $info);
}

function uupApiWriteFileinfo($updateId, $info) {
    $file = uupApiGetFileinfoName($updateId);

    if(uupApiWriteJson($file, $info) === false)
        return false;

    return uupApiWriteFileinfoMeta($updateId, $info);
}

function uupApiReadFileinfoMeta($updateId) {
    $file = uupApiGetFileinfoName($updateId, true);

    if(file_exists($file))
        return uupApiReadJson($file);

    $info = uupApiReadFileinfo($updateId, false);
    if($info === false)
        return false;

    if(isset($info['files']))
        unset($info['files']);

    if(uupApiWriteFileinfoMeta($updateId, $info) === false)
        return false;

    return $info;
}

function uupApiReadFileinfo($updateId, $meta = false) {
    if(!uupApiFileInfoExists($updateId))
        return false;

    if($meta === true)
        return uupApiReadFileinfoMeta($updateId);

    $file = uupApiGetFileinfoName($updateId);
    $info = uupApiReadJson($file);

    return $info;
}
