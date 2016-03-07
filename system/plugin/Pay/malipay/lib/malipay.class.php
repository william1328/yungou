<?php 

class malipay {
	
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
		$config['pay_ReturnUrl']= G_WEB_PATH.'/index.php/plugin-Pay-return-malipayReturnUrl';
		$config['pay_NotifyUrl']=G_WEB_PATH.'/index.php/plugin-Pay-return-malipayNotifyUrl?';     		
        // $config['pay_ReturnUrl']= G_WEB_PATH.'/index.php/?plugin=true&api=Pay&action=return&data='.$payreturn1;
        // $config['pay_NotifyUrl']=G_WEB_PATH.'/index.php/?plugin=true&api=Pay&action=return&data='.$payreturn2;   	

		$this->config = $config;		
		$this->send_maliapy();
	}

	//及时到账
	private function send_maliapy(){
		$config = $this->config;
		// include_once dirname(__FILE__).DIRECTORY_SEPARATOR."alipay.config.php";			
		include_once dirname(__FILE__).DIRECTORY_SEPARATOR."alipay_submit.class.php";			

/**************************请求参数**************************/

        //支付类型
        $payment_type = "1";
        //必填，不能修改
		
		
 
		//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
		//合作身份者id，以2088开头的16位纯数字
		$alipay_config['partner']   = $config['pay_uid'];


		//收款支付宝账号，一般情况下收款账号就是签约账号
		$alipay_config['seller_id']	=  $config['pay_account'];

		//商户的私钥（后缀是.pen）文件相对路径
		$alipay_config['private_key_path']	= dirname(__FILE__).'\rsa_private_key.pem';

		//支付宝公钥（后缀是.pen）文件相对路径
		$alipay_config['ali_public_key_path']= dirname(__FILE__).'\alipay_public_key.pem';


		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


		//签名方式 不需修改
		$alipay_config['sign_type']    = strtoupper('RSA');

		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('utf-8');

		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert']    = dirname(__FILE__).'\cacert.pem';

		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';		


        //服务器异步通知页面路径
        $notify_url = $config['pay_NotifyUrl'];
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $return_url =$config['pay_ReturnUrl'];
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
        $out_trade_no = $config['pay_code'];
		
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $config['pay_title'];
        //必填

        //付款金额
        $total_fee = $config['pay_money'];
        //必填
	
		
        //商品展示地址
        $show_url = WEB_PATH;
        //必填，需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html

        //订单描述
        $body = $config['pay_title'];
        //选填

        //超时时间
        $it_b_pay = '';
        //选填

        //钱包token
        $extern_token = '';
        //选填
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "alipay.wap.create.direct.pay.by.user",
				"partner" => trim($alipay_config['partner']),
				"seller_id" => trim($alipay_config['seller_id']),
				"payment_type"	=> $payment_type,
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"show_url"	=> $show_url,
				"body"	=> $body,
				"it_b_pay"	=> $it_b_pay,
				"extern_token"	=> $extern_token,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		// echo "<pre/>";
		// print_r($parameter);		
		// echo "<pre/>";
		// print_r($alipay_config);	
		// exit;
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$this->url= $alipaySubmit->buildRequestForm($parameter,"get","submit");

	}

	//发送
	public function send_pay(){
		 // echo  $this->url;
		 // exit;		
		 exit($this->url); 
		// header("Location: $url");	
	}
}

?>
