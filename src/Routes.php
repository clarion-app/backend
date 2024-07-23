<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ClarionApp\Backend\Controllers\ComposerController;
use ClarionApp\Backend\Controllers\AppController;
use ClarionApp\Backend\Controllers\UserController;

Route::get('/Description.xml', function() {
?>
<?xml version="1.0"?>
<root xmlns="urn:schemas-upnp-org:device-1-0">
  <device>
    <deviceType>urn:schemas-upnp-org:device:Basic:1</deviceType>
    <presentationURL><?=config('clarion.frontend_url') ?></presentationURL>
    <friendlyName>Clarion</friendlyName>
    <manufacturer>Metaverse Systems</manufacturer>
    <manufacturerURL>https://metaverse.systems/</manufacturerURL>
    <modelName>Clarion</modelName>
    <modelNumber>0.1</modelNumber>
    <modelURL>https://clarion.app</modelURL>
    <serialNumber><?=explode('-', config('clarion.node_id'))[4] ?></serialNumber>
    <UDN>uuid:<?=config('clarion.node_id') ?></UDN>
  </device>
</root>
<?php
});

Route::post('/api/composer/install', [ComposerController::class, 'install']);
Route::post('/api/composer/uninstall', [ComposerController::class, 'uninstall']);

Route::post('/api/app/install', [AppController::class, 'install']);
Route::post('/api/app/uninstall', [AppController::class, 'uninstall']);
Route::get('/api/app', [AppController::class, 'index']);

Route::resource('/api/user', UserController::class);