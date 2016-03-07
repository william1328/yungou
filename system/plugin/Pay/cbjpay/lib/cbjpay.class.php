<?php
include dirname(__FILE__).DIRECTORY_SEPARATOR."SignUtil.php";					
include dirname(__FILE__).DIRECTORY_SEPARATOR."DesUtils.php";				
include dirname(__FILE__).DIRECTORY_SEPARATOR."ConfigUtil.php";						
include dirname(__FILE__).DIRECTORY_SEPARATOR."cbjpay_submit.class.php";				

class cbjpay{
	private $config;
	private $url;
 	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 		
	//主入口
	public function config($config=null){
        $pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = '$config[pay_class]' and `pay_start` = '1'");
        $config['pay_uid']=$pay_type['pay_uid'];
        $config['pay_account']=$pay_type['pay_account'];
        $config['pay_key']=$pay_type['pay_key'];
        $config['pay_type']=$pay_type['pay_type'];
        $payreturn1=array();$payreturn2=array();        
        $payreturn1['pay_class']=$pay_type['pay_class'];
        $payreturn1['pay_fun']="qiantai";	    
        $payreturn1=base64_encode(json_encode($payreturn1));  
                             
        $payreturn2['pay_class']=$pay_type['pay_class'];
        $payreturn2['pay_fun']="houtai";	
        $payreturn2=base64_encode(json_encode($payreturn2));          
        $config['pay_ReturnUrl']= G_WEB_PATH.'/index.php/?plugin=true&api=Pay&action=return&data='.$payreturn1;
        $config['pay_NotifyUrl']=G_WEB_PATH.'/index.php/?plugin=true&api=Pay&action=return&data='.$payreturn2;   	
	
		$this->config = $config;
		$this->config_jsdz();
	}

	//及时到账
	private function config_jsdz(){
		$param = array(
					//"serverUrl" 		 => ConfigUtil::get_val_by_key('serverPayUrl'),
					"version" 			 => "1.0",
					"token" 			 => "", 		
					"merchantNum" 		 => ConfigUtil::get_val_by_key('merchantNum'),
					"merchantRemark" 	 => $this->config['shouname'],
					"tradeNum" 			 => $this->config['pay_code'],
					"tradeName" 		 => $this->config['pay_title'],
					"tradeDescription" 	 => $this->config['pay_title'],
					"tradeTime" 		 => date('Y-m-d H:i:s', time()),
					"tradeAmount" 		 => $this->config['pay_money'] * 100,
					//"tradeAmount" 		 => "1",
					"currency" 			 => "CNY",
					"notifyUrl" 		 => $this->config['pay_NotifyUrl'],
					"successCallbackUrl" => $this->config['pay_ReturnUrl'],
					"failCallbackUrl" 	 => $this->config['pay_ReturnUrl']
			);

		$sign = SignUtil::sign($param);
		$param["merchantSign"] = $sign;


		
		
		if ($param["version"] == "1.0") {
			//敏感信息未加密
		} else if ($param["version"] == "2.0") {
			//敏感信息加密
			//获取商户 DESkey
			//对敏感信息进行 DES加密
			$desUtils  = new DesUtils();
			$key = ConfigUtil::get_val_by_key("desKey");
			$param["merchantRemark"] 	 = $desUtils->encrypt($param["merchantRemark"],$key);
			$param["tradeNum"] 			 = $desUtils->encrypt($param["tradeNum"],$key);
			$param["tradeName"] 		 = $desUtils->encrypt($param["tradeName"],$key);
			$param["tradeDescription"] 	 = $desUtils->encrypt($param["tradeDescription"],$key);
			$param["tradeTime"] 		 = $desUtils->encrypt($param["tradeTime"],$key);
			$param["tradeAmount"] 		 = $desUtils->encrypt($param["tradeAmount"],$key);
			$param["currency"] 			 = $desUtils->encrypt($param["currency"],$key);
			$param["notifyUrl"] 		 = $desUtils->encrypt($param["notifyUrl"],$key);
			$param["successCallbackUrl"] = $desUtils->encrypt($param["successCallbackUrl"],$key);
			$param["failCallbackUrl"] 	 = $desUtils->encrypt($param["failCallbackUrl"],$key);
			
		}
		
		$cbjpaySubmit = new CbjpaySubmit($param);
		$this->url = $cbjpaySubmit->buildRequestForm($param,'POST','submit');
	}

	//发送
	public function send_pay(){
		 echo  $this->url;
		 exit;
	}
}