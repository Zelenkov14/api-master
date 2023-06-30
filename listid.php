<?php
/*
Copyright 2019 whatever127

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

function uupApiPrivateInvalidateFileinfoCache() {
    $cache1 = new UupDumpCache('listid-0', false);
    $cache2 = new UupDumpCache('listid-1', false);

    $cache1->delete();
    $cache2->delete();
}

function uupApiPrivateGetFromFileinfo($sortByDate = 0) {
    $dirs = uupApiGetFileinfoDirs();
    $fileinfo = $dirs['fileinfoData'];
    $fileinfoRoot = $dirs['fileinfo'];

    $files = scandir($fileinfo);
    $files = preg_grep('/\.json$/', $files);

    consoleLogger('Parsing database info...');

    $cacheFile = $fileinfoRoot.'/cache.json';
    $cacheV2Version = 1;

    $database = uupApiReadJson($cacheFile);

    if(isset($database['version'])) {
        $version = $database['version'];
    } else {
        $version = 0;
    }

    if($version == $cacheV2Version && isset($database['database'])) {
        $database = $database['database'];
    } else {
        $database = array();
    }

    if(empty($database)) $database = array();

    $newDb = array();
    $builds = array();
    foreach($files as $file) {
        if($file == '.' || $file == '..')
            continue;

        $uuid = preg_replace('/\.json$/', '', $file);

        if(!isset($database[$uuid])) {
            $info = uupApiReadFileinfoMeta($uuid);

            $title = isset($info['title']) ? $info['title'] : 'UNKNOWN';
            $build = isset($info['build']) ? $info['build'] : 'UNKNOWN';
            $arch = isset($info['arch']) ? $info['arch'] : 'UNKNOWN';
            $created = isset($info['created']) ? $info['created'] : null;

            $temp = array(
                'title' => $title,
                'build' => $build,
                'arch' => $arch,
                'created' => $created,
            );

            $newDb[$uuid] = $temp;
        } else {
            $title = $database[$uuid]['title'];
            $build = $database[$uuid]['build'];
            $arch = $database[$uuid]['arch'];
            $created = $database[$uuid]['created'];

            $newDb[$uuid] = $database[$uuid];
        }

        $temp = array(
            'title' => $title,
            'build' => $build,
            'arch' => $arch,
            'created' => $created,
            'uuid' => $uuid,
        );

        $tmp = explode('.', $build);
        if(isset($tmp[1])) {
            $tmp[0] = str_pad($tmp[0], 10, '0', STR_PAD_LEFT);
            $tmp[1] = str_pad($tmp[1], 10, '0', STR_PAD_LEFT);
            $tmp = $tmp[0].$tmp[1];
        } else {
            consoleLogger($uuid.'.json appears to be broken and may be useless.');
            $tmp = 0;
        }

        if($sortByDate) {
            $tmp = $created.$tmp;
        }

        $buildAssoc[$tmp][] = $arch.$title.$uuid;
        $builds[$tmp.$arch.$title.$uuid] = $temp;
    }

    if(empty($buildAssoc)) return [];

    krsort($buildAssoc);
    $buildsNew = array();

    foreach($buildAssoc as $key => $val) {
        sort($val);
        foreach($val as $id) {
            $buildsNew[] = $builds[$key.$id];
        }
    }

    $builds = $buildsNew;
    consoleLogger('Done parsing database info.');

    if($newDb != $database) {
        if(!file_exists('cache')) mkdir('cache');

        $cacheData = array(
            'version' => $cacheV2Version,
            'database' => $newDb,
        );

        $success = @file_put_contents(
            $cacheFile,
            json_encode($cacheData)."\n"
        );

        if(!$success) consoleLogger('Failed to update database cache.');
    }

    return $builds;
}

function uupListIds($search = null, $sortByDate = 0) {
    uupApiPrintBrand();

    $sortByDate = $sortByDate ? 1 : 0;

    $res = "listid-$sortByDate";
    $cache = new UupDumpCache($res, false);
    $builds = $cache->get();
    $cached = ($builds !== false);

    if(!$cached) {
        $builds = uupApiPrivateGetFromFileinfo($sortByDate);
        if($builds === false) return ['error' => 'NO_FILEINFO_DIR'];

        $cache->put($builds, 60);
    }

    if(count($builds) && $search != null) {
        if(!preg_match('/^regex:/', $search)) {
            $searchSafe = preg_quote($search, '/');

            if(preg_match('/^".*"$/', $searchSafe)) {
                $searchSafe = preg_replace('/^"|"$/', '', $searchSafe);
            } else {
                $searchSafe = str_replace(' ', '.*', $searchSafe);
            }
        } else {
            $searchSafe = preg_replace('/^regex:/', '', $search);
        }

        //I really hope that this will not backfire at me
        @preg_match("/$searchSafe/", "");
        if(preg_last_error()) {
            return array('error' => 'SEARCH_NO_RESULTS');
        }

        foreach($builds as $key => $val) {
            $buildString[$key] = $val['title'].' '.$val['build'].' '.$val['arch'];
        }

        $remove = preg_grep('/.*'.$searchSafe.'.*/i', $buildString, PREG_GREP_INVERT);
        $removeKeys = array_keys($remove);

        foreach($removeKeys as $value) {
            unset($builds[$value]);
        }

        if(empty($builds)) {
            return array('error' => 'SEARCH_NO_RESULTS');
        }

        unset($remove, $removeKeys, $buildString);
    }

    return array(
        'apiVersion' => uupApiVersion(),
        'builds' => $builds,
    );
}
