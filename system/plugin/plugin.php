<?php

/**
 * 	管理插件,插件调用,插件删除，插件添加 ，等功能.
 *  <战线> booobusy@gmail.com
 */


//插件模式路由

require G_PLUGIN."./plugin.fun.php";
_PluginRouteIndex();$plugin = isset($_GET['api']) ? basename($_GET['api']) : _SendStatus(404);


if($plugin == "demo"){
	
	_PluginUpdatePackage('Demo',array("Name"=>"bbbbbbbbbbbb","a"=>"b"));
	_PluginUpdatePackage('Demo',array("Name"=>"CCCCCCCC","a"=>"b"));
	_PluginUpdatePackage('Demo',array("Name"=>"DDDDDDDDDDD","a"=>"b"));
		

	/*
	 session_save_path("1;".G_CACHES."caches_session");
	 session_name("");
	 session_id(base64_encode(_get_ip()));
	 session_start();
	echo session_name();
	echo session_id();
	*/
	return;
}

register_shutdown_function("plugin_shudtown_check");

$pluginData = _PluginGetOne($plugin);

if(!$pluginData){
	echo "Not install Plugin...";return;
}
if($pluginData['Status'] != 1){
	if(!_PluginCheckAdmin(1)){
		echo "Plugin Not start.";return;
	}
}

if(file_exists(G_PLUGIN.$plugin."/./".$pluginData['Index'])){
	require G_PLUGIN.$plugin."/./".$pluginData['Index'];
}else{
	echo "plugin: not found file.";
}