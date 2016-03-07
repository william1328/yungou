<?php
include dirname(__FILE__).DIRECTORY_SEPARATOR."SignUtil.php";					
include dirname(__FILE__).DIRECTORY_SEPARATOR."ConfigUtil.php";						
include dirname(__FILE__).DIRECTORY_SEPARATOR."jdpay_submit.class.php";				

class jdpay{
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
					"version" 			 => "1.1.5",
					"token" 			 => "", 		
					"merchantNum" 		 => ConfigUtil::get_val_by_key('merchantNum'),
					"merchantRemark" 	 => '',
					"tradeNum" 			 => $this->config['pay_code'],
					"tradeName" 		 => $this->config['pay_title'],
					"tradeDescription" 	 => $this->config['pay_title'],
					"tradeTime" 		 => date('Y-m-d H:i:s', time()),
					"tradeAmount" 		 => $this->config['pay_money'] * 100,
					//"tradeAmount" 		 => "1",
					"currency" 			 => "CNY",
					"notifyUrl" 		 => $this->config['pay_NotifyUrl'],
					"ip"				 => _get_ip()
			);

		
		$sign = SignUtil::signWithoutToHex($param);
		$param["merchantSign"] = $sign;
		$param['successCallbackUrl']=$this->config['pay_ReturnUrl'];	
	//file_put_contents('jdpay_class.txt','param'.var_export($param,1)."\n",FILE_APPEND);
		$param['failCallbackUrl']=$this->config['pay_ReturnUrl'];

		$jdpaysubmit = new Jdpaysubmit($param);
		$this->url = $jdpaysubmit->buildRequestForm($param,'POST','submit');
	}

	//发送
	public function send_pay(){
		 echo  $this->url;
		 exit;
	}
}