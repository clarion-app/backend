<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/Description.xml', function() {
?>
<?xml version="1.0"?>
<root xmlns="urn:schemas-upnp-org:device-1-0">
  <device>
    <deviceType>urn:schemas-upnp-org:device:Basic:1</deviceType>
    <presentationURL><?=env('APP_URL') ?></presentationURL>
    <friendlyName>Clarion</friendlyName>
    <manufacturer>Metaverse Systems</manufacturer>
    <manufacturerURL>https://metaverse.systems/</manufacturerURL>
    <modelName>Clarion</modelName>
    <modelNumber>0.1</modelNumber>
    <modelURL>https://clarion.app</modelURL>
    <serialNumber><?=explode('-', env('CLARION_NODE_ID'))[4] ?></serialNumber>
    <UDN>uuid:<?=env('CLARION_NODE_ID') ?></UDN>
  </device>
</root>
<?php
});
