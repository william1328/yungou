<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');

ini_set("display_errors","OFF");

class malipay_url{
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	

	
	/* 同步通知 */
	public function qiantai(){
		sleep(2);
		$out_trade_no = $_GET['out_trade_no'];	//商户订单号
		$dingdaninfo = $this->db->GetOne("select `ostatus` from `@#_orders` where `ocode` = '$out_trade_no'");
		if($dingdaninfo){
			if($dingdaninfo['ostatus']==2){
				_message('充值成功！',WEB_PATH);
			}else{
				_message('充值失败！',WEB_PATH);
			}
		}else{
			$recorddingdan = $this->db->GetOne("select `status` from `@#_user_money_record` where `code` = '$out_trade_no'");
			if($recorddingdan){
				if($recorddingdan['status']==2){
					$recorddel = $this->db->Query("DELETE FROM `@#_user_money_record` WHERE (`code`='$out_trade_no')"); 
						_setcookie("Cartlist", null);
			_message("支付成功！",WEB_PATH."/member/cart/paysuccess");	
				}else{
					_message('支付失败！',WEB_PATH);
				}
			}else{
				_message('没有这个订单！',WEB_PATH);
			}                
		 } 			
	}/* function end */
	
	
	/* 异步通知 */
	public function houtai(){	
		include_once dirname(__FILE__).DIRECTORY_SEPARATOR."lib/alipay_notify.class.php";
		//计算得出通知验证结果
		$pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = 'malipay' and `pay_start` = '1'");
		
		//合作身份者id，以2088开头的16位纯数字
		$alipay_config['partner']   = $pay_type['pay_uid'];


		//收款支付宝账号，一般情况下收款账号就是签约账号
		$alipay_config['seller_id']	=  $pay_type['pay_account'];

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
	
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();


		if($_POST['trade_status'] == 'TRADE_FINISHED'||$_POST['trade_status'] == 'TRADE_SUCCESS') {
			$ret = $this->updata_order($pay_type);						
			if($ret == 'success'){
				echo 'success';exit;
			}
			if($ret == 'fail'){
				echo 'fail';exit;
			}	
		}else{				
			echo 'fail';exit;				
		}
	
	}
	
	
	/*支付与充值处理*/
	private function updata_order($pay_type){	
	
		$out_trade_no = $_POST['out_trade_no'];	
		$this->db->sql_begin();
		//查询充值订单
		$dingdaninfo = $this->db->GetOne("select * from `@#_orders` where `ocode` = '$out_trade_no' and `ostatus` = '1' for update");
		$time = time();	
		if(!$dingdaninfo){
			$recorddingdan = $this->db->GetOne("select * from `@#_user_money_record` where `code` = '$out_trade_no' and `status` = '1' for update");                
		 } 
		 if($dingdaninfo||$recorddingdan){
			$c_money = intval($dingdaninfo['omoney']);
			$uid = $dingdaninfo['ouid'];
			if($dingdaninfo){
				$up_q1 = $this->db->Query("UPDATE `@#_orders` SET `opay` = '2', `ostatus` = '2',`oremark` = '网银在线充值' where `oid` = '$dingdaninfo[oid]' and `ocode` = '$dingdaninfo[ocode]'");
				$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
				$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '充值', '$c_money', '$time')");                 
				if($up_q1&&$up_q2&&$up_q3){
					/**充值之后的其他处理**/
					pay_recharge_next($c_money,$uid);								
					$this->db->sql_commit();
					return 'success';						
				 }else{
					return 'fail';					      
				 }                  
			}else{
				 if($recorddingdan){
					$c_money = intval($recorddingdan['money']);
					$uid = $recorddingdan['uid'];                        
					$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
					$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '充值', '$c_money', '$time')");             		  
					$recordupdate= $this->db->Query("UPDATE  `@#_user_money_record` set `status` ='2' WHERE (`id`='{$recorddingdan[id]}')");
					if($up_q2&&$up_q3&&$recordupdate){			
						$this->db->sql_commit();
					}else{
						$this->db->sql_rollback();
						return 'fail';	
					}					
					$pay = System::load_app_class('UserPay', 'common');	
					$pay->fufen = $recorddingdan['score']; 
					$scookies = unserialize($recorddingdan['scookies']);		
					$pay->scookie = $scookies;	        
					$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
					if($ok != 'ok'){
						return 'fail';	
					}			
					$check = $pay->go_pay(1); 
					if($check&&$up_q2&&$up_q3){
						$this->db->sql_commit();
						_setcookie("Cartlist", null); 						
						return 'success';	
					 }else{
						return 'fail';	     
					 }                                     		                    
				 }else{
					return 'fail';		                     
				 }                   
			}		                
				
		 }else{
			return 'fail';	                                          
		 }

	}
	
}