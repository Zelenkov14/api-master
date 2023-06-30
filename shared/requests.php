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

// Composes DeviceAttributes parameter needed to fetch data
function composeDeviceAttributes($flight, $ring, $build, $arch, $sku, $type) {
    $branch = branchFromBuild($build);
    $blockUpgrades = 0;
    $flightEnabled = 1;
    $isRetail = 0;

    if(is_array($arch)) {
        $arch = $arch[0];
    }

    if($sku == 125 || $sku == 126)
        $blockUpgrades = 1;

    $dvcFamily = 'Windows.Desktop';
    $insType = 'Client';
    if($sku == 119) {
        $dvcFamily = 'Windows.Team';
    }
    if(uupApiIsServer($sku)) {
        $dvcFamily = 'Windows.Server';
        $insType = 'Server';
        $blockUpgrades = 1;
    }
    /*/ Hololens
    if($sku == 135) {
        $dvcFamily = 'Windows.Holographic';
        $insType = 'FactoryOS';
    }*/
    // HubOS Andromeda Lite
    if($sku == 180 || $sku == 184 || $sku == 189) {
        $dvcFamily = 'Windows.Core';
        $insType = 'FactoryOS';
    }

    $fltContent = 'Mainline';
    $fltRing = 'External';
    $flight = 'Active';

    if($ring == 'RETAIL') {
        $fltBranch = '';
        $fltContent = $flight;
        $fltRing = 'Retail';
        $flightEnabled = 0;
        $isRetail = 1;
    }

    if($ring == 'WIF') {
        $fltBranch = 'Dev';
    }

    if($ring == 'WIS') {
        $fltBranch = 'Beta';
    }

    if($ring == 'RP') {
        $fltBranch = 'ReleasePreview';
    }

    if($ring == 'DEV') {
        $fltBranch = 'Dev';
        $ring = 'WIF';
    }

    if($ring == 'BETA') {
        $fltBranch = 'Beta';
        $ring = 'WIS';
    }

    if($ring == 'RELEASEPREVIEW') {
        $fltBranch = 'ReleasePreview';
        $ring = 'RP';
    }

    if($ring == 'MSIT') {
        $fltBranch = 'MSIT';
        $fltRing = 'Internal';
    }

    if($ring == 'CANARY') {
        $fltBranch = 'CanaryChannel';
        $ring = 'WIF';
    }

    $bldnum = explode('.', $build);
    $bldnum = $bldnum[2];

    if($bldnum < 17763) {
        if($ring == 'RP') $flight = 'Current';
        $fltBranch = 'external';
        $fltContent = $flight;
        $fltRing = $ring;
    }

    $attrib = array(
        'App=WU_OS',
        'AppVer='.$build,
        'AttrDataVer=208',
        'AllowInPlaceUpgrade=1',
        'AllowUpgradesWithUnsupportedTPMOrCPU=1',
        'BlockFeatureUpdates='.$blockUpgrades,
        'BranchReadinessLevel=CB',
        'CurrentBranch='.$branch,
        'DataExpDateEpoch_CU23H2='.(time()+82800),
        'DataExpDateEpoch_CU23H2Setup='.(time()+82800),
        'DataExpDateEpoch_NI22H2='.(time()+82800),
        'DataExpDateEpoch_NI22H2Setup='.(time()+82800),
        'DataExpDateEpoch_CO21H2='.(time()+82800),
        'DataExpDateEpoch_CO21H2Setup='.(time()+82800),
        'DataExpDateEpoch_23H2='.(time()+82800),
        'DataExpDateEpoch_22H2='.(time()+82800),
        'DataExpDateEpoch_21H2='.(time()+82800),
        'DataExpDateEpoch_21H1='.(time()+82800),
        'DataExpDateEpoch_20H1='.(time()+82800),
        'DataExpDateEpoch_19H1='.(time()+82800),
        'DataVer_RS5=2000000000',
        'DefaultUserRegion=191',
        'DeviceFamily='.$dvcFamily,
        'EKB19H2InstallCount=1',
        'EKB19H2InstallTimeEpoch=1255000000',
        'FlightingBranchName='.$fltBranch,
        //'FlightContent='.$fltContent,
        'FlightRing='.$fltRing,
        'Free=gt64',
        'GStatus_CU23H2=2',
        'GStatus_CU23H2Setup=2',
        'GStatus_NI22H2=2',
        'GStatus_NI22H2Setup=2',
        'GStatus_CO21H2=2',
        'GStatus_CO21H2Setup=2',
        'GStatus_23H2=2',
        'GStatus_22H2=2',
        'GStatus_21H2=2',
        'GStatus_21H1=2',
        'GStatus_20H1=2',
        'GStatus_20H1Setup=2',
        'GStatus_19H1=2',
        'GStatus_19H1Setup=2',
        'GStatus_RS5=2',
        'GenTelRunTimestamp_19H1='.(time()-3600),
        'InstallDate=1438196400',
        'InstallLanguage=en-US',
        'InstallationType='.$insType,
        'IsDeviceRetailDemo=0',
        'IsFlightingEnabled='.$flightEnabled,
        'IsRetailOS='.$isRetail,
        'MediaBranch=',
        'MediaVersion='.$build,
        'CloudPBR=1',
        'DUScan=1',
        'OEMModel=Asus ROG Maximus Z690 Extreme',
        'OEMModelBaseBoard=ROG MAXIMUS Z690 EXTREME',
        'OEMName_Uncleaned=ASUSTeK COMPUTER INC.',
        'OemPartnerRing=UPSFlighting',
        'OSArchitecture='.$arch,
        'OSSkuId='.$sku,
        'OSUILocale=en-US',
        'OSVersion='.$build,
        'ProcessorIdentifier=Intel64 Family 6 Model 151 Stepping 2',
        'ProcessorManufacturer=GenuineIntel',
        'ProcessorModel=12th Gen Intel(R) Core(TM) i9-12900K',
        'ReleaseType='.$type,
        'SdbVer_20H1=2000000000',
        'SdbVer_19H1=2000000000',
        'SecureBootCapable=1',
        'TelemetryLevel=3',
        'TimestampEpochString_CU23H2='.(time()-3600),
        'TimestampEpochString_CU23H2Setup='.(time()-3600),
        'TimestampEpochString_NI22H2='.(time()-3600),
        'TimestampEpochString_NI22H2Setup='.(time()-3600),
        'TimestampEpochString_CO21H2='.(time()-3600),
        'TimestampEpochString_CO21H2Setup='.(time()-3600),
        'TimestampEpochString_23H2='.(time()-3600),
        'TimestampEpochString_22H2='.(time()-3600),
        'TimestampEpochString_21H2='.(time()-3600),
        'TimestampEpochString_21H1='.(time()-3600),
        'TimestampEpochString_20H1='.(time()-3600),
        'TimestampEpochString_19H1='.(time()-3600),
        'TPMVersion=2',
        'UpdateManagementGroup=2',
        'UpdateOfferedDays=0',
        'UpgEx_CU23H2=Green',
        'UpgEx_NI22H2=Green',
        'UpgEx_CO21H2=Green',
        'UpgEx_23H2=Green',
        'UpgEx_22H2=Green',
        'UpgEx_21H2=Green',
        'UpgEx_21H1=Green',
        'UpgEx_20H1=Green',
        'UpgEx_19H1=Green',
        'UpgEx_RS5=Green',
        'UpgradeAccepted=1',
        'UpgradeEligible=1',
        'UserInPlaceUpgrade=1',
        'Version_RS5=2000000000',
        'WuClientVer='.$build,
    );

    if(uupApiConfigIsTrue('fetch_sync_current_only')) {
        $attrib[] = 'MediaBranch='.$branch;
    }

    if($ring == 'MSIT' && uupApiConfigIsTrue('allow_corpnet')) {
        $attrib[] = 'DUInternal=1';
    }

    return htmlentities('E:'.implode('&', $attrib));
}

// Returns the most possible branch for selected build
function branchFromBuild($build) {
    $build = explode('.', $build);
    $build = $build[2];

    switch($build) {
        case 15063:
            $branch = 'rs2_release';
            break;

        case 16299:
            $branch = 'rs3_release';
            break;

        case 17134:
            $branch = 'rs4_release';
            break;

        case 17763:
            $branch = 'rs5_release';
            break;

        case 17784:
            $branch = 'rs5_release_svc_hci';
            break;

        case 18362:
        case 18363:
            $branch = '19h1_release';
            break;

        case 19041:
        case 19042:
        case 19043:
        case 19044:
        case 19045:
        case 19046:
            $branch = 'vb_release';
            break;

        case 20279:
            $branch = 'fe_release_10x';
            break;

        case 20348:
        case 20349:
        case 20350:
            $branch = 'fe_release';
            break;

        case 22000:
            $branch = 'co_release';
            break;

        case 22621:
            $branch = 'ni_release';
            break;

        default:
            $branch = 'rs_prerelease';
            break;
    }

    return $branch;
}

// Composes POST data for gathering list of urls for download
function composeFileGetRequest($updateId, $device, $info, $rev = 1, $type = 'Production') {
    $uuid = genUUID();

    $createdTime = time();
    $expiresTime = $createdTime + 120;

    $created = gmdate(DATE_W3C, $createdTime);
    $expires = gmdate(DATE_W3C, $expiresTime);

    //$branch = branchFromBuild($info['checkBuild']);

    $deviceAttributes = composeDeviceAttributes(
        $info['flight'],
        $info['ring'],
        $info['checkBuild'],
        $info['arch'],
        $info['sku'],
        $type
    );

    return <<<XML
<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope">
    <s:Header>
        <a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/GetExtendedUpdateInfo2</a:Action>
        <a:MessageID>urn:uuid:$uuid</a:MessageID>
        <a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured</a:To>
        <o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <Created>$created</Created>
                <Expires>$expires</Expires>
            </Timestamp>
            <wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization">
                <TicketType Name="MSA" Version="1.0" Policy="MBI_SSL">
                    <Device>$device</Device>
                </TicketType>
            </wuws:WindowsUpdateTicketsToken>
        </o:Security>
    </s:Header>
    <s:Body>
        <GetExtendedUpdateInfo2 xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService">
            <updateIDs>
                <UpdateIdentity>
                    <UpdateID>$updateId</UpdateID>
                    <RevisionNumber>$rev</RevisionNumber>
                </UpdateIdentity>
            </updateIDs>
            <infoTypes>
                <XmlUpdateFragmentType>FileUrl</XmlUpdateFragmentType>
                <XmlUpdateFragmentType>FileDecryption</XmlUpdateFragmentType>
                <XmlUpdateFragmentType>EsrpDecryptionInformation</XmlUpdateFragmentType>
                <XmlUpdateFragmentType>PiecesHashUrl</XmlUpdateFragmentType>
                <XmlUpdateFragmentType>BlockMapUrl</XmlUpdateFragmentType>
            </infoTypes>
            <deviceAttributes>$deviceAttributes</deviceAttributes>
        </GetExtendedUpdateInfo2>
    </s:Body>
</s:Envelope>
XML;
}

// Composes POST data for fetching the latest update information from Windows Update
function composeFetchUpdRequest($device, $encData, $arch, $flight, $ring, $build, $sku = 48, $type = 'Production') {
    $uuid = genUUID();

    $createdTime = time();
    $expiresTime = $createdTime + 120;
    $cookieExpiresTime = $createdTime + 604800;

    $created = gmdate(DATE_W3C, $createdTime);
    $expires = gmdate(DATE_W3C, $expiresTime);
    $cookieExpires = gmdate(DATE_W3C, $cookieExpiresTime);

    $branch = branchFromBuild($build);

    $mainProduct = 'Client.OS.rs2';
    if(uupApiIsServer($sku)) {
        $mainProduct = 'Server.OS';
    }
    /*/ Hololens
    if($sku == 135) {
        $mainProduct = 'HoloLens.OS.RS2';
    }*/
    // HubOS
    if($sku == 180) {
        $mainProduct = 'WCOSDevice2.OS';
    }
    // Andromeda
    if($sku == 184) {
        $mainProduct = 'WCOSDevice1.OS';
    }
    // Lite
    if($sku == 189) {
        $mainProduct = 'WCOSDevice0.OS';
    }

    if($arch == 'all') {
        $arch = array(
            'amd64',
            'x86',
            'arm64',
            'arm',
        );
    }

    if(!is_array($arch)) {
        $arch = array($arch);
    }

    $products = array();
    foreach($arch as $currArch) {
        $products[] = "PN=$mainProduct.$currArch&Branch=$branch&PrimaryOSProduct=1&Repairable=1&V=$build&ReofferUpdate=1";
        $products[] = "PN=Adobe.Flash.$currArch&Repairable=1&V=0.0.0.0";
        $products[] = "PN=Microsoft.Edge.Stable.$currArch&Repairable=1&V=0.0.0.0";
        $products[] = "PN=Microsoft.NETFX.$currArch&V=2018.12.2.0";
        $products[] = "PN=Windows.Appraiser.$currArch&Repairable=1&V=$build";
        $products[] = "PN=Windows.AppraiserData.$currArch&Repairable=1&V=$build";
        $products[] = "PN=Windows.EmergencyUpdate.$currArch&V=$build";
        $products[] = "PN=Windows.FeatureExperiencePack.$currArch&Repairable=1&V=0.0.0.0";
        $products[] = "PN=Windows.ManagementOOBE.$currArch&IsWindowsManagementOOBE=1&Repairable=1&V=$build";
        $products[] = "PN=Windows.OOBE.$currArch&IsWindowsOOBE=1&Repairable=1&V=$build";
        $products[] = "PN=Windows.UpdateStackPackage.$currArch&Name=Update Stack Package&Repairable=1&V=$build";
        $products[] = "PN=Hammer.$currArch&Source=UpdateOrchestrator&V=0.0.0.0";
        $products[] = "PN=MSRT.$currArch&Source=UpdateOrchestrator&V=0.0.0.0";
        $products[] = "PN=SedimentPack.$currArch&Source=UpdateOrchestrator&V=0.0.0.0";
        $products[] = "PN=UUS.$currArch&Source=UpdateOrchestrator&V=0.0.0.0";
    }

    $callerAttrib = array(
        'Profile=AUv2',
        'Acquisition=1',
        'Interactive=1',
        'IsSeeker=1',
        'SheddingAware=1',
        'Id=MoUpdateOrchestrator',
    );

    $products = htmlentities(implode(';', $products));
    $callerAttrib = htmlentities('E:'.implode('&', $callerAttrib));

    $deviceAttributes = composeDeviceAttributes(
        $flight,
        $ring,
        $build,
        $arch,
        $sku,
        $type
    );

    $syncCurrent = uupApiConfigIsTrue('fetch_sync_current_only');
    $syncCurrentStr = $syncCurrent ? 'true' : 'false';

    return <<<XML
<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope">
    <s:Header>
        <a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/SyncUpdates</a:Action>
        <a:MessageID>urn:uuid:$uuid</a:MessageID>
        <a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx</a:To>
        <o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <Created>$created</Created>
                <Expires>$expires</Expires>
            </Timestamp>
            <wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization">
                <TicketType Name="MSA" Version="1.0" Policy="MBI_SSL">
                    <Device>$device</Device>
                </TicketType>
            </wuws:WindowsUpdateTicketsToken>
        </o:Security>
    </s:Header>
    <s:Body>
        <SyncUpdates xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService">
            <cookie>
                <Expiration>$cookieExpires</Expiration>
                <EncryptedData>$encData</EncryptedData>
            </cookie>
            <parameters>
                <ExpressQuery>false</ExpressQuery>
                <InstalledNonLeafUpdateIDs>
                    <int>1</int>
                    <int>10</int>
                    <int>105939029</int>
                    <int>105995585</int>
                    <int>106017178</int>
                    <int>107825194</int>
                    <int>10809856</int>
                    <int>11</int>
                    <int>117765322</int>
                    <int>129905029</int>
                    <int>130040030</int>
                    <int>130040031</int>
                    <int>130040032</int>
                    <int>130040033</int>
                    <int>133399034</int>
                    <int>138372035</int>
                    <int>138372036</int>
                    <int>139536037</int>
                    <int>139536038</int>
                    <int>139536039</int>
                    <int>139536040</int>
                    <int>142045136</int>
                    <int>158941041</int>
                    <int>158941042</int>
                    <int>158941043</int>
                    <int>158941044</int>
                    <int>159776047</int>
                    <int>160733048</int>
                    <int>160733049</int>
                    <int>160733050</int>
                    <int>160733051</int>
                    <int>160733055</int>
                    <int>160733056</int>
                    <int>161870057</int>
                    <int>161870058</int>
                    <int>161870059</int>
                    <int>17</int>
                    <int>19</int>
                    <int>2</int>
                    <int>23110993</int>
                    <int>23110994</int>
                    <int>23110995</int>
                    <int>23110996</int>
                    <int>23110999</int>
                    <int>23111000</int>
                    <int>23111001</int>
                    <int>23111002</int>
                    <int>23111003</int>
                    <int>23111004</int>
                    <int>2359974</int>
                    <int>2359977</int>
                    <int>24513870</int>
                    <int>28880263</int>
                    <int>3</int>
                    <int>30077688</int>
                    <int>30486944</int>
                    <int>5143990</int>
                    <int>5169043</int>
                    <int>5169044</int>
                    <int>5169047</int>
                    <int>59830006</int>
                    <int>59830007</int>
                    <int>59830008</int>
                    <int>60484010</int>
                    <int>62450018</int>
                    <int>62450019</int>
                    <int>62450020</int>
                    <int>69801474</int>
                    <int>8788830</int>
                    <int>8806526</int>
                    <int>9125350</int>
                    <int>9154769</int>
                    <int>98959022</int>
                    <int>98959023</int>
                    <int>98959024</int>
                    <int>98959025</int>
                    <int>98959026</int>
                </InstalledNonLeafUpdateIDs>
                <OtherCachedUpdateIDs/>
                <SkipSoftwareSync>false</SkipSoftwareSync>
                <NeedTwoGroupOutOfScopeUpdates>true</NeedTwoGroupOutOfScopeUpdates>
                <AlsoPerformRegularSync>true</AlsoPerformRegularSync>
                <ComputerSpec/>
                <ExtendedUpdateInfoParameters>
                    <XmlUpdateFragmentTypes>
                        <XmlUpdateFragmentType>Extended</XmlUpdateFragmentType>
                        <XmlUpdateFragmentType>LocalizedProperties</XmlUpdateFragmentType>
                    </XmlUpdateFragmentTypes>
                    <Locales>
                        <string>en-US</string>
                    </Locales>
                </ExtendedUpdateInfoParameters>
                <ClientPreferredLanguages/>
                <ProductsParameters>
                    <SyncCurrentVersionOnly>$syncCurrentStr</SyncCurrentVersionOnly>
                    <DeviceAttributes>$deviceAttributes</DeviceAttributes>
                    <CallerAttributes>$callerAttrib</CallerAttributes>
                    <Products>$products</Products>
                </ProductsParameters>
            </parameters>
        </SyncUpdates>
    </s:Body>
</s:Envelope>
XML;
}

// Composes POST data for Get Cookie request
function composeGetCookieRequest($device) {
    $uuid = genUUID();

    $createdTime = time();
    $expiresTime = $createdTime + 120;

    $created = gmdate(DATE_W3C, $createdTime);
    $expires = gmdate(DATE_W3C, $expiresTime);

    return <<<XML
<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope">
    <s:Header>
        <a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/GetCookie</a:Action>
        <a:MessageID>urn:uuid:$uuid</a:MessageID>
        <a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx</a:To>
        <o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <Created>$created</Created>
                <Expires>$expires</Expires>
            </Timestamp>
            <wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization">
                <TicketType Name="MSA" Version="1.0" Policy="MBI_SSL">
                    <Device>$device</Device>
                </TicketType>
            </wuws:WindowsUpdateTicketsToken>
        </o:Security>
    </s:Header>
    <s:Body>
        <GetCookie xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService">
            <oldCookie>
                <Expiration>$created</Expiration>
            </oldCookie>
            <lastChange>$created</lastChange>
            <currentTime>$created</currentTime>
            <protocolVersion>2.0</protocolVersion>
        </GetCookie>
    </s:Body>
</s:Envelope>
XML;
}
