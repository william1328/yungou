<?php

/**
 *  框架插件系统管理器不能删除,
 *  复制统管全局插件的 插拔.
 *  附录： 那个叫 LAWSON 的便利店很贵的。我们不要去
 *  附录： 昨天还看到了一个叫 小野田宽郎 的日本人，很碉堡啊。
 *  <战线> booobusy@gmail.com,526541010@qq.com
 */

 
 defined("G_EXECMODE") or die ("I'm sorry, you don't have the access");

 //指派动作
 $action = isset($_GET['action']) ?  $_GET['action'] : false;

 //可操作动作，返回 JSON
 $actions = array(
 	"add",			//
 	"showview",		//显示主界面
 	"install",		//安装插件
 	"uninstall",	//卸载插件
 	"listinfo",		//调用本地安装列表
 	"weblistinfo",//调用公网上的插件列表
    "template",		//调用本地安装模板列表
    "webtemplate",//调用公网上的模板列表
 	"oneinfo",		//获取单个插件
 	"freeget",		//获取免费插件
 	"Ploneinfo"		//外部获取单个插件基本信息
 );

 //判断权限
 if(!_PluginCheckAdmin(1)){
 	_SendMsgJson("status",-1);
	_SendMsgJson("msg","User don't have the access",1);
 }


 //判断动作
 if(!in_array($action, $actions)){
 	_SendMsgJson("status",-1);
	_SendMsgJson("msg","not action.",1);
 }

 /*
 //判断插件是否存在  卸载用
 if(!isset($package[$pname])){
 	_SendMsgJson("status",-1);
	_SendMsgJson("msg","not plugin.",1);
 }
 */


 $action = ("Plugin_Manager_".$action);
 $action(); return;

 /*********************/


 //界面显示
 function Plugin_Manager_showview(){
	include "cloudapp.list.tpl.php";exit;
 }

 //安装
 function Plugin_Manager_install(){
        $package = &_PluginGetAll();
        $pname   = basename($_GET['data']);
        if(isset($package[$pname])){
           _message("插件已经存在.");
        }
        $install_path = G_PLUGIN.$pname."/install.php";
        if(file_exists($install_path)){
            include $install_path;
        }else{
            _message("插件安装文件错误.");
        }      
        return;
 }

 //卸载
 function Plugin_Manager_uninstall(){

 }

 //添加
 function Plugin_Manager_add(){
 
 	 $name = isset($_GET['name']) ?  $_GET['name'] : false;
	 //POST 获取购买数据，
	 //成功后添加进本地插件包
	 //前台显示可安装插件

 }

 //获取本地全部
 function Plugin_Manager_listinfo(){

    
	_SendMsgJson("status",1);
	_SendMsgJson("msg",_PluginGetAll(),1);
 }

  //获取网络全部
 function Plugin_Manager_weblistinfo(){

    //$package = &_PluginGetAll();
 	$ctx = stream_context_create(array('http' => array('timeout' => 3)));
	$de_plugin=0;//默认0获取内部测试的插件时$de_plugin=1
	$url = "http://www.yungoucms.com/plugin/plugin.php?action=weblistinfo&de_plugin=".$de_plugin;

	
	$i = 3;
	while($i--){
		$result = @file_get_contents($url,false,$ctx);
		if($result)break;
	}
	$result=json_decode($result);

	file_put_contents("pluginlist","result_".var_export($result,1),FILE_APPEND);
	foreach($result as $key=>$v){
		if(is_object($v)){
		  $result[$key] = (array)$v;
		}
		

		
		//unset($result[$key]['Pstatus']);	
	}
	if(count($result)==0){break;}
	file_put_contents("pluginlist","result_".var_export($result,1),FILE_APPEND);		
	$result=json_encode($result);

	echo '{"status":1,"msg":'.$result.'}';
	exit;

	
	
 }
//复制目录
function copydir($dirfrom, $dirto, $cover='') {
	//如果遇到同名文件无法复制，则直接退出
	if(is_file($dirto)){
		die("同名文件无法复制".$dirto);
	}
	//如果目录不存在，则建立之
	if(!file_exists($dirto)){
		mkdir($dirto);
	}

	$handle = opendir($dirfrom); //打开当前目录

	//循环读取文件
	while(false !== ($file = readdir($handle))) {
		if($file != '.' && $file != '..'){ //排除"."和"."
			//生成源文件名
			$filefrom = $dirfrom.$file;
			//生成目标文件名
			$fileto = $dirto.$file;
			if(is_dir($filefrom)){ //如果是子目录，则进行递归操作
				copydir($filefrom.DIRECTORY_SEPARATOR, $fileto.DIRECTORY_SEPARATOR, $cover);
			} else { //如果是文件，则直接用copy函数复制
				if(!empty($cover)) {
					if(!copy($filefrom, $fileto)) {
						$copyfailnum++;
						echo 'copy'.$filefrom.'to'.$fileto.'failed'."<br />";
					}else{
						//copy 成功
					}
				} else {
					if(fileext($fileto) == 'html' && file_exists($fileto)) {
							//文件==html 不copy
					} else {
						if(!copy($filefrom, $fileto)) {
							$copyfailnum++;
							echo 'copy'.$filefrom.'to'.$fileto.'failed'."<br />";
						}else{
							//copy 成功
						}
					}
				}
			}
		}
	}
}// 
 
 //下载免费插件
function Plugin_Manager_freeget(){
	$package = &_PluginGetAll();
	$PID=isset($_POST['PID']) ? basename($_POST['PID']) : 0;	
	$url = "http://www.yungoucms.com/plugin/plugin.php?action=loadpugin&PID=".$PID;
		
	$i = 3;
	while($i--){
		$result = @file_get_contents($url,false);
		if($result)break;
	}	
	$result=json_decode($result);
	if(is_object($result)){
	  $result= (array)$result;
	}
	$pluginname=$result['pname'];
	if(isset($package[$pluginname])){
		$message="检测到您的插件列表已经安装过此插件，不能再次安装！";	
		echo  json_encode(array("status"=>-1,"msg"=>$message));
		// echo '{"status":-1,"msg":'.$message.'}';
		exit;		
	}else{
		//插件包配置文件		
		$addplugin=array();
		$addplugin['Name']=$result['pname'];
		$addplugin['Status']=0;
		$addplugin['Action']="/?plugin=true&api=".$result['pname']."&action=config";
		$addplugin['Author']=$result['pdesigner'];
		$addplugin['Email']=$result['pdesigner'];
		$addplugin['Version']=$result['pversion'];
		$addplugin['Index']=$result['pindex'];//入口文件
		$addplugin['Install']="/?plugin=true&api=".$result['pname']."&action=install";
		$addplugin['Uninstall']=1;
		$addplugin['Photo']=G_UPLOAD_PATH."/banner/".$result['pname'].".png";
		$addplugin['Desc']=$result['ptitle'];		
		$package[$pluginname]=$addplugin;	

		$clz=get_defined_constants();	
		$html = "<?php ".PHP_EOL;
		$html .= '
		/** 
		 *	插件配置管理器
		 *	插件状态分为: 
		 *  
		 *	0.  未安装
		 *	1.	使用中
		 *	2.	已停止
		 *  3.  已卸载
		 *	
		 */'.PHP_EOL.PHP_EOL;
		$html .= "return ".var_export($package,true).";";
		file_put_contents(G_PLUGIN."package.php", $html);	


		
		//获取插件包
		$fileurl = "http://www.yungoucms.com/".$result['pzip'];
		
		$pluginzip_path = G_CACHES.'caches_plugin'.DIRECTORY_SEPARATOR.$result['pname'];
		$pluginzip= $pluginzip_path.".zip";
		file_put_contents($pluginzip,file_get_contents($fileurl));	

		System::load_app_class('pclzip', 'sys', 'no');				
		$PclZip = new PclZip($pluginzip);
		//解压路径
		$zip_source_path = $pluginzip_path;
		$PclZip -> extract(PCLZIP_OPT_PATH,$zip_source_path,PCLZIP_OPT_REPLACE_NEWER) or die("Error : ".$PclZip->errorInfo(true));

		//拷贝文件夹到根目录
		$copy_from = $zip_source_path.DIRECTORY_SEPARATOR;
		$copy_to = G_APP_PATH;	
		$pathcss=$copy_to.$copy_from;
		$copyfailnum = 0;
		copydir($copy_from, $copy_to,'cover');
	    if(is_file($pathcss)){
	        die("同名文件无法复制".$pathcss);
	    }
	    //如果目录不存在，则建立之
	    if(!file_exists($pathcss)){
	        mkdir($pathcss);
	    }		

		$message="下载成功，请到已安装列表中安装插件！";
		echo  json_encode(array("status"=>1,"msg"=>$message));
		// echo '{"status":1,"msg":'.$message.'}';
		exit;
	}
	
	
}



//获取单独插件安装详细信息
function  Plugin_Manager_Ploneinfo(){ 
  
	$PID=isset($_GET['PID']) ? basename($_GET['PID']) : 0;	
	$url = "http://www.yungoucms.com/plugin/plugin.php?action=loadpugin&PID=".$PID;
		
	$i = 3;
	while($i--){
		$result = @file_get_contents($url,false);
		if($result)break;
	}	
	$result=json_decode($result);
	if(is_object($result)){
	  $result= (array)$result;
	}
	$pdesc=$result['pdesc'];
	echo $pdesc;
	exit;   
}


//本地模板
function Plugin_Manager_template(){

    $templates=system::load_sys_config("view","templates");
	$path = G_WEB_PATH.'/'.G_STATICS_DIR.'/templates/';
    foreach($templates as $k=>$v){
        if(!is_dir(G_TEMPLATES.$v['dir'])){
            unset($templates[$k]);
        }else{
        clearstatcache(G_TEMPLATES.$v['dir']);
		$templates[$k]['photo'] = $path.$v['dir']."/images/".$v['dir'].".jpg";
		}
	}

    _SendMsgJson("status",1);
    _SendMsgJson("msg",$templates,1);
}

//服务器模板
function Plugin_Manager_webtemplate(){
    $ctx = stream_context_create(array('http' => array('timeout' => 3)));
    $url = "http://www.yungoucms.com/plugin/plugin.php?action=webtemplate";
    $i = 3;
    while($i--){
        $result = @file_get_contents($url,false,$ctx);
        if($result)break;
    }
    $webtemplate=json_decode($result,true);
    $temp_config=system::load_sys_config("view");
    $templates=$temp_config['templates'];
    foreach($webtemplate as $k=>$v){
        foreach($templates as $val){
            if($val['kid']==$v['id'] and $v['status']=1){
               unset($webtemplate[$k]);
            }
        }
    }
    $webtemplates=json_encode($webtemplate);
    if($i==0){
        _SendMsgJson("status",1);
        _SendMsgJson("msg","{}",1);
    }
    echo '{"status":1,"msg":'.$webtemplates.'}';
    exit;
}





 //获取一个
 function Plugin_Manager_oneinfo(){
    $package = &_PluginGetAll();
	_SendMsgJson("status",1);
	_SendMsgJson("msg",Plugin_Manager_ifplugin($package),1);
 }


 //指派插件判断
 function Plugin_Manager_ifplugin(){
    $package = &_PluginGetAll();
	$pname  = isset($_GET['name']) ?  $_GET['name'] : false;
 	if(!isset($package[$pname])){
 		_SendMsgJson("status",-1);
		_SendMsgJson("msg","not plugin.",1);
	}
	return $package[$pname];
 }
