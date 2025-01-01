<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ClarionApp\Backend\Controllers\ComposerController;
use ClarionApp\Backend\Controllers\AppController;
use ClarionApp\Backend\Controllers\UserController;
use ClarionApp\Backend\Controllers\NetworkController;
use ClarionApp\Backend\Controllers\SystemController;

Route::get('/Description.xml', function() {
  $hostname = gethostname();
  $parts = explode('-', $hostname);
  $mac = $parts[1];
?>
<?xml version="1.0"?>
<root xmlns="urn:schemas-upnp-org:device-1-0">
  <device>
    <deviceType>urn:schemas-upnp-org:device:Basic:1</deviceType>
    <presentationURL><?=config('clarion.frontend_url') ?></presentationURL>
    <friendlyName>Clarion <?=$mac ?></friendlyName>
    <manufacturer>Metaverse Systems</manufacturer>
    <manufacturerURL>https://metaverse.systems/</manufacturerURL>
    <modelName>Clarion</modelName>
    <modelNumber>0.1</modelNumber>
    <modelURL>https://clarion.app</modelURL>
    <serialNumber><?=explode('-', config('clarion.node_id'))[4] ?></serialNumber>
    <UDN>uuid:<?=config('clarion.node_id') ?></UDN>
    <serviceList>
      <service>
        <serviceType>urn:schemas-upnp-org:service:ConnectionManager:1</serviceType>
        <serviceId>urn:upnp-org:serviceId:ClarionConnectionManager</serviceId>
        <controlURL><?=config('app.url')?>/api/clarion/network</controlURL>
        <eventSubURL></eventSubURL>
      </service>
    </serviceList>
  </device>
</root>
<?php
});

Route::group(['prefix'=>'api/clarion/system', 'middleware'=>'api'], function () {
    Route::get('user/exists', [UserController::class, 'userExists']);
    Route::resource('user', UserController::class)->only(['store']);
    Route::post('user/login', [UserController::class, 'login']);
    Route::post('network/create', [SystemController::class, 'create']);
    Route::post('network/join', [SystemController::class, 'join']);
});

Route::group(['prefix'=>'api/clarion/system', 'middleware' => 'auth:api'], function () {
  Route::post('composer/install', [ComposerController::class, 'install']);
  Route::post('composer/uninstall', [ComposerController::class, 'uninstall']);

  Route::post('app/install', [AppController::class, 'install']);
  Route::post('app/uninstall', [AppController::class, 'uninstall']);
  Route::get('app', [AppController::class, 'index']);

  Route::resource('user', UserController::class)->except(['store']);
});

Route::group(['prefix'=>'api/clarion/network', 'middleware'=>'api'], function () {
  Route::get('/', [NetworkController::class, 'index']);
  Route::get('local_nodes', [NetworkController::class, 'localNodesIndex']);
  Route::post('join', [NetworkController::class, 'join']);
  Route::post('complete_join', [NetworkController::class, 'completeJoin']);
});

Route::group(['prefix'=>'api/clarion/network', 'middleware'=>'auth:api'], function () {
  Route::post('accept', [NetworkController::class, 'accept']);
  Route::get('requests', [NetworkController::class, 'requestsIndex']);
});

Route::group(['prefix'=>'api/docs', 'middleware'=>'api'], function () {
  Route::get('packages', function() {
    $packages = ClarionApp\Backend\ApiManager::getPackageDescriptions();
    return response()->json($packages);
  });
});