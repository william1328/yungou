<?php

/**
 *  param.calss.php 	路由参数处理类
 *
 * @copyright			(C) 2005-2010 BUSY
 * @license
 * @lastmodify			2012-6-1
 */

class param {

	private $route_config;
	private $domain;
	private $expstr = '/';
	private $route=array();
	private $route_url=array();
	private $param_url = '';
	private $plugin = false;

	public function __construct() {

			$this->route_config = System::load_sys_config('param');
			$this->domain = System::load_sys_config('domain');
			$this->prourl();
			$this->setDefine();
			$this->sub_addslashes();
	}

	public function __get($key){
		if(isset($this->$key)){
			return $this->$key;
		}else{
			return NULL;
		}
	}


	private function setDefine(){
		if(!defined("G_IS_MOBILE")){
			$this->isMobile() ? define('G_IS_MOBILE',1) : define('G_IS_MOBILE',0);
		}
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			define('G_IS_AJAX',1);			
		}else{
			define('G_IS_AJAX',0);
		}
	}

	private function prourl(){

	
		$is_m_domain = isset($this->domain[$_SERVER['HTTP_HOST']]['type']) ? define('G_IS_MOBILE',1) : false;

		
		/*
		$_SERVER['PATH_INFO']
		$_SERVER['ORIG_PATH_INFO']
		$_SERVER['REDIRECT_PATH_INFO']
		$_SERVER["REDIRECT_URL"]
		*/


		if(isset($_SERVER['REDIRECT_PATH_INFO']) && !isset($_SERVER['PATH_INFO'])){
			$_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_PATH_INFO'] == $_SERVER['PHP_SELF'] ? "" : $_SERVER['REDIRECT_PATH_INFO'];
			reset($_SERVER);
		}
		if(isset($_SERVER['ORIG_PATH_INFO']) && !isset($_SERVER['PATH_INFO'])){
			$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'] == $_SERVER['PHP_SELF'] ? "" : $_SERVER['ORIG_PATH_INFO'];
			reset($_SERVER);
		}

		
		if(isset($_SERVER['PATH_INFO']) && ($_SERVER['PATH_INFO']!='/') && !empty($_SERVER['PATH_INFO'])){
			return $this->prourlexp('pathinfo',$_SERVER['PATH_INFO']);
		}
		if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])){
			$this->prourlexp('query',$_SERVER['QUERY_STRING']);
			if(!empty($this->route_url[1])){
				return;
			}
		}

		if(isset($this->domain[$_SERVER['HTTP_HOST']])){
			if($is_m_domain){
				return;
			}else{
				$this->route_url[1] = $this->domain[$_SERVER['HTTP_HOST']]['module'];
				$this->route_url[2] = $this->domain[$_SERVER['HTTP_HOST']]['action'];
				$this->route_url[3] = $this->domain[$_SERVER['HTTP_HOST']]['func'];
				return;
			}
		}

		if(!$is_m_domain){
			$this->isMobile() ? define('G_IS_MOBILE',1) : define('G_IS_MOBILE',0);
		}

		if(G_IS_MOBILE){
			foreach($this->domain as $key=>$v){
				if(isset($v['type']) &&  $v['type'] == 'mobile'){
					header("location: ".dirname(G_HTTP.$key.$_SERVER['SCRIPT_NAME']));
					exit;
				}
			}
		}
		return;
	}


	private function prourlexp($type,$path){

		if(stripos(trim($path,'/'),$this->route_config['plugin_begin_route']) === 0){
			$type = "plugin";
		}


		$path = ltrim($path,'/');
		switch($type){
			case 'pathinfo' :
				$path = ltrim($path,'/');
				$path = preg_replace("/^index.php\//i",'',$path);
				$path = rtrim($path,$this->expstr);			
			break;
			case 'query' :
				//reset()
				$path = ltrim($path,'/');
				$path = rtrim($path,$this->expstr);				
				list($key, $val) = each($_GET);			
				if($key && !$val){
					$path = trim($key,'/');
				}				
			break;
			case 'plugin' :       
				$this->plugin = trim($path,'/');
				define('G_PARAM_URL',$this->plugin);
				return 	$this->plugin;			
			break;
			default :
			break;
		}


		$this->param_url= $path;
		define('G_PARAM_URL',$this->param_url);
		if(isset($this->route_config['routes'])){
			if(isset($this->route_config['routes'][$path])){
				$path=$this->route_config['routes'][$path];
			}else{
				$path .= $this->expstr;
				foreach ($this->route_config['routes'] as $key => $val){
					$key = str_replace(':any', '.*', str_replace(':num', '[0-9]+',$key))."\/?$";
					//str_replace(':page', '\m?[0-9]{0,10}',$key)

					if (preg_match('#^'.$key.'$#', $path)){

						if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE){
							$val = preg_replace('#^'.$key.'$#',$val,$path);
						}
						$path=$val;
					}
				}

			}
		}

		$this->route_url=explode($this->expstr,trim($path,$this->expstr));

	

		array_unshift($this->route_url,NULL);
		unset($this->route_url[0]);

		$end=end($this->route_url);
		if(stripos($end,'.')!==false){
			$end=explode('.',$end);
			$this->route_url[count($this->route_url)]=$end[0];
		}
			
		if(count($this->route_url) == 1 && strpos($this->route_url[1],'=') !== false){
			$this->route_url[1] = null;
			$this->route_url[2] = null;
			$this->route_url[3] = null;
			
		}
		

		/*
			preg_match_all("/p(.*)/i", $path,$matches,PREG_SET_ORDER);
			$this->route_url['p']=$matches[0][1];
		*/

	}


	private function sub_addslashes(){
		if(!get_magic_quotes_gpc()) {
				$_POST = new_addslashes($_POST);
				$_GET = new_addslashes($_GET);
				$_REQUEST = new_addslashes($_REQUEST);
				$_COOKIE = new_addslashes($_COOKIE);
				$this->route_url = new_addslashes($this->route_url);
		}else{
			$this->route_url = new_addslashes($this->route_url);
		}

	}

	/**
	 * 获取模型
	 */
	public function route_m() {
		if(empty($this->route_url[1])){
			$this->route_url[1]=$this->route_config['default']['m'];
		}
		define('G_MODULE_PATH',WEB_PATH.'/'.$this->route_url[1]);
		if(G_MODULE_PATH == WEB_PATH.'/'.G_ADMIN_DIR){

			$asasasad = create_function('', '
			ob_clean();
			echo \'
			<!doctype html>
			<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<title>YunGouCMS</title>
					<meta name="keywords" content="云购网,云购源码,云购系统,1元云购,一元云购" />
					<meta name="description" content="韬龙网络公司是目前国内唯一的云购源码授权服务提供商。旗下的云购系统是国内领先的PHP云购源码，是精仿1元云购、一元云购的云购网，保留版权的前提下可免费使用、免费升级，如需要去版权请购买授权和服务。" />
				</head>
				<style>
					html,body{padding: 0; margin: 0;height: 100%; background:#FF7D27 ; font-family: "微软雅黑";overflow: hidden;text-align: center; perspective:1200px;}

					.border{
						width:60%; margin: auto auto; cursor: pointer;  margin-top: 100px;
						text-align: center; color: #f60; background: #fff; border-radius: 30px; line-height: 85px;

						transform:rotateY(0deg);
						-webkit-animation:"photoRotate3" 3s ease-in-out 1;
					}


					@-webkit-keyframes "photoRotate3" {
					    0% {
					        -webkit-transform:rotateY(0deg);
					    }
					    33% {
					        -webkit-transform:rotateY(-30deg);
					        background: #f60;
					    }
					    66% {
					        -webkit-transform:rotateY(30deg);
							 background: #f60;
					    }
					    100% {
					        -webkit-transform:rotateY(0deg);
					    }
					}

					@-webkit-keyframes "photoRotate4" {
					    0% {
					        margin-top: 0px;filter:alpha(opacity=0); opacity:0;
					    }
					    100% {
					        margin-top: 100px;filter:alpha(opacity=100); opacity:1;
					    }
					}


					h3{
						  -webkit-animation:"photoRotate4" 1s;
						  border-radius: 5px;
						  background-color: #31C552;
						  color: #fff !important;
						  display: inline-block;
						  padding: 8px 50px;
						  height: 36px;
						  line-height: 36px;
						  width: auto !important;
						  transition: all 0.5s;
						  position: relative;
						  cursor: pointer;
						  margin-top: 100px;
					}
					.footer{
						 font-size: 12px; color: #111; position: absolute; bottom:0px; line-height:25px;text-align: center; width: 100%;
					}
				</style>
				<body>
					<div class="border">
						<h1>{ 你还未购买授权哟 }</h1>
					</div>
					<h3 id="button">购买授权</h3>
					<div class="footer">
						Copyright © 2012 - <script type="text/javascript">document.write(new Date().getFullYear())</script> <a href="http://www.yungoucms.com">YunGouCMS</a> All Rights Reserved
					</div>
					<script type="text/javascript">
						document.getElementById("button").onclick = function(){
							window.open("http://www.yungoucms.com?go=app");
						}
					</script>
				</body>
			</html>
			\'; exit;');

			$xzcfdfsdf = create_function('$asasasad', '
				$codes = strtolower(System::load_sys_config("code","code"));$codes = substr($codes,15,15).strrev(substr($codes,45,15)).strrev(substr($codes,0,15)).substr($codes,30,15);
				$auths = array(); for($i=0;$i<20;$i++){$auths["domain"]  .= substr($codes,$i*3,2);$auths["endtime"] .= substr($codes,$i*3+2,1);}
				preg_match("/[\w][\w-]*\.(?:com|net|cn|cc|集团|top|ren|xyz|space|com\.cn|tm|在线|club|wang|me|website|pw|la|mobi|so|co|info|biz|hk|tv|tel|org|travel|gov\.cn|net\.cn|org\.cn|ac\.cn|name|中国|中文网|移动|公司|网络|asia|cool|company|city|email|market|software|ninja|我爱你|bike|today|life|ae|af|am|co\.il|in|io|mn|ph|pk|ps|tl|uz|vn|co\.nz|co\.uk|cz|gg|gl|gr|im|je|md|me\.uk|org\.uk|pl|si|sx|vg|ag|bz|cl|ec|gd|gs|gy|hn|ht|lc|ms|mx|pe|tc|vc|ac|bi|cm|mg|mu|sc|as|com\.sb|cx|fm|ki|nf|sh|tk|ws)(\/|$)/isU", G_WEB_PATH, $domain);
				$domain = $domain ? rtrim($domain[0],"/") : false;

				$auths["domain"] = strrev(substr($auths["domain"],0,20)).strrev(substr($auths["domain"],20,20));
				($auths["domain"] !== sha1(md5(strrev($domain)))) ? (rand(1,2) == 2) ? $asasasad() : false : false;

				for($i=0;$i<10;$i++){
					$auths["endtime"] .= chr(substr($auths["endtime"],$i*2,2) - 42);
					if($i==9){
						$auths["endtime"] = substr($auths["endtime"], 20);
					}
				}
				$auths["endtime"] -= (ceil(strlen($domain)/2) << strlen($domain));
				(md5($auths["endtime"]) != "284a9923cdbad2058ab6878027a801ef") ? $asasasad() : false;
				unset($auths);
				unset($codes);
				unset($domain);

			');
			$xzcfdfsdf($asasasad);unset($xzcfdfsdf);unset($asasasad);


		}

		return $this->route_url[1];
	}

	/**
	 * 获取控制器
	 */
	public function route_c() {
		if(empty($this->route_url[2])){
			$this->route_url[2]=$this->route_config['default']['c'];
		}return $this->route_url[2];

	}

	/**
	 * 获取事件
	 */
	public function route_a() {

		if(empty($this->route_url[3])){
			$this->route_url[3]=$this->route_config['default']['a'];
		}
		return $this->route_url[3];

		preg_match("/-[0-9]{1,10}$/i", $this->route_url[3],$matches);

		if(isset($matches[0])){
			$this->route_url['page'] = abs($matches[0]);
		}else{
			$this->route_url['page'] = 1;
		}

		$this->route_url['url'] = $this->param_url;
		$this->route_url[3] = explode("-",$this->route_url[3]);
		return $this->route_url[3] = $this->route_url[3][0];
	}



	/**
	 *	是否移动设备
	 */
	private function isMobile(){
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
	        return true;
	    }
	    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
	    if (isset ($_SERVER['HTTP_VIA'])){
	        // 找不到为flase,否则为true
	        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
	    }
	    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
	    if (isset ($_SERVER['HTTP_USER_AGENT'])){
	        $clientkeywords = array ('nokia',
	            'sony',
	            'ericsson',
	            'mot',
	            'samsung',
	            'htc',
	            'sgh',
	            'lg',
	            'sharp',
	            'sie-',
	            'philips',
	            'panasonic',
	            'alcatel',
	            'lenovo',
	            'iphone',
	            'ipod',
	            'blackberry',
	            'meizu',
	            'android',
	            'netfront',
	            'symbian',
	            'ucweb',
	            'windowsce',
	            'palm',
	            'operamini',
	            'operamobi',
	            'openwave',
	            'nexusone',
	            'cldc',
	            'midp',
	            'wap',
	            'mobile'
	            );
	        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
	        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
	            return true;
	        }
	    }
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT'])){
				// 如果只支持wml并且不支持html那一定是移动设备
				// 如果支持wml和html但是wml在html之前则是移动设备
				if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
				{
					return true;
				}
		}
		return false;
	}

}