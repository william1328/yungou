<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");
class alipay_url {
	private $error = null;	
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	
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
	if($_GET['trade_status']=='TRADE_SUCCESS') 
	{
		_message('支付成功！',WEB_PATH."/member/cart/paysuccess");
		
	}else{
			_message('支付失败！',WEB_PATH);
	}		
		 } 
	}
	
	public function houtai(){	
        include dirname(__FILE__).DIRECTORY_SEPARATOR."lib/alipay_notify.class.php";	
		
		$pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = 'alipay' and `pay_start` = '1'");
		$key =  $pay_type['pay_key'];		//支付KEY
		$partner =  $pay_type['pay_uid'];		//支付商号ID
		
		$alipay_config_sign_type = strtoupper('MD5');		//签名方式 不需修改
		$alipay_config_input_charset = strtolower('utf-8'); //字符编码格式		
		$alipay_config_cacert =  dirname(__FILE__).DIRECTORY_SEPARATOR."lib/cacert.pem";	//ca证书路径地址
		$alipay_config_transport   = 'http';
		
		$alipay_config=array(
			"partner"      =>$partner,
			"key"          =>$key,
			"sign_type"    =>$alipay_config_sign_type,
			"input_charset"=>$alipay_config_input_charset,
			"cacert"       =>$alipay_config_cacert,
			"transport"    =>$alipay_config_transport
		);
		
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if(!$verify_result) {$this->error = false;exit;} //验证失败
		
		$out_trade_no = $_POST['out_trade_no'];	//商户订单号
		$trade_no = $_POST['trade_no'];			//支付宝交易号
		$trade_status = $_POST['trade_status'];	//交易状态

		//开始处理及时到账和担保交易订单
		if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS' || $trade_status == 'WAIT_SELLER_SEND_GOODS') {
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
        		    $up_q1 = $this->db->Query("UPDATE `@#_orders` SET `opay` = '支付宝', `ostatus` = '2', `oremark` = '支付宝充值' where `oid` = '$dingdaninfo[oid]' and `ocode` = '$dingdaninfo[ocode]'");
        			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
    		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '支付宝充值', '$c_money', '$time')");   

                    if($up_q1&&$up_q2&&$up_q3){
						/**充值之后的其他处理**/
						pay_recharge_next($c_money,$uid);		
						$this->db->sql_commit();
						$this->error = true;exit;						
                     }else{
						$this->error = false;exit;						      
                     }                  
        		}else{
            		 if($recorddingdan){
            		    $c_money = intval($recorddingdan['money']);
                        $uid = $recorddingdan['uid'];                        
            			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
        		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '支付宝充值', '$c_money', '$time')");             		  
					    $recordupdate= $this->db->Query("UPDATE  `@#_user_money_record` set `status` ='2' WHERE (`id`='{$recorddingdan[id]}')"); 
						if($up_q2&&$up_q3&&$recordupdate){			
							$this->db->sql_commit();
						}else{
							$this->db->sql_rollback();
							$this->error = false;exit;
						}        			   
					    $pay = System::load_app_class('UserPay', 'common');		
						$pay->fufen = $recorddingdan['score']; 	
                        $scookies = unserialize($recorddingdan['scookies']);						
            			$pay->scookie = $scookies;	        
            			$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
            			if($ok != 'ok'){
							$this->error = false;exit;
            			}			
            			$check = $pay->go_pay(1); 
                        if($check&&$up_q2&&$up_q3){	
							$this->db->sql_commit();
							_setcookie("Cartlist", null); 							
							$this->error = true;exit;
                         }else{
							$this->error = false;exit;     
                         }                                     		                    
            		 }else{
						$this->error = false;exit;	                     
            		 }                   
        		}		                
                    
             }else{
				$this->error = false;exit;                                          
             } 
		
		}//开始处理订单结束
				

	}//function end
	
}//

?>