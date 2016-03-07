<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
include dirname(__FILE__).DIRECTORY_SEPARATOR."lib/ResponseHandler.class.php";
include dirname(__FILE__).DIRECTORY_SEPARATOR."lib/RequestHandler.class.php";
include dirname(__FILE__).DIRECTORY_SEPARATOR."lib/client/ClientResponseHandler.class.php";
include dirname(__FILE__).DIRECTORY_SEPARATOR."lib/client/TenpayHttpClient.class.php";
include dirname(__FILE__).DIRECTORY_SEPARATOR."lib/function.php";
ini_set("display_errors","OFF");
ini_set('max_execution_time','150');
class tenpay_url  {
	public function __construct(){			
		$this->db=System::load_sys_class('model');
	} 	

	public function qiantai($status='comeback'){		
		if($status=='success'){
		_setcookie("Cartlist", null);
header("Location:".WEB_PATH."/member/account/userbalance");
			   exit;
		}
		if($status=='comeback')
		{
				_setcookie("Cartlist", null);
		header("Location:".WEB_PATH."/member/account/userbalance");
			   exit;	
			
		}
		
	}

	public function houtai(){			
		$pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = 'tenpay' and `pay_start` = '1'");
		$key = $pay_type['pay_key'];		//支付KEY
		$partner =$pay_type['pay_account'];		//支付商号ID
		

		/* 创建支付应答对象 */
		$resHandler = new ResponseHandler();
		$resHandler->setKey($key);
		
		//判断签名
		// if(!$resHandler->isTenpaySign()) {	
			// $return_tag="fail";
			// $this->qiantai($return_tag); exit;
		// }		
		//通知ID
		$notify_id = $resHandler->getParameter("notify_id");
		//通过通知ID查询，确保通知来至财付通
		//创建查询请求
		$queryReq = new RequestHandler();
		$queryReq->init();
		$queryReq->setKey($key);
		$queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
		$queryReq->setParameter("partner", $partner);
		$queryReq->setParameter("notify_id", $notify_id);
		
	
		
		//通信对象
		$httpClient = new TenpayHttpClient();
		$httpClient->setTimeOut(5);
		//设置请求内容
		$httpClient->setReqContent($queryReq->getRequestURL());
		//后台调用
		if($httpClient->call()) {			
			//设置结果参数
			$queryRes = new ClientResponseHandler();
			$queryRes->setContent($httpClient->getResContent());
			$queryRes->setKey($key);	
		}else{				
			$return_tag="fail";
			$this->qiantai($return_tag); exit;
		}
	

		//及时到账
		if($resHandler->getParameter("trade_mode") == "1"){	
			//只有签名正确,retcode为0，trade_state为0才是支付成功
			if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
				//log_result("即时到帐验签ID成功");				
				//取结果参数做业务处理
				$out_trade_no = $resHandler->getParameter("out_trade_no");
				//财付通订单号
				$transaction_id = $resHandler->getParameter("transaction_id");
				//金额,以分为单位
				$total_fee = $resHandler->getParameter("total_fee");
				//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
				$discount = $resHandler->getParameter("discount");
				
				//------------------------------
				//处理业务开始
				//------------------------------
				
				//处理数据库逻辑
				//注意交易单不要重复处理
				//注意判断返回金额		
				
				$total_fee_t = $total_fee/100;		
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
        		    $up_q1 = $this->db->Query("UPDATE `@#_orders` SET `opay` = '2', `ostatus` = '2',`oremark`='财付通充值' where `oid` = '$dingdaninfo[oid]' and `ocode` = '$dingdaninfo[ocode]'");
        			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
    		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '财付通充值', '$c_money', '$time')");                 
                    if($up_q1&&$up_q2&&$up_q3){
						/**充值之后的其他处理**/
						pay_recharge_next($c_money,$uid);							
						$this->db->sql_commit();
						$return_tag="success";
						$this->qiantai($return_tag);  
                     }else{
                      $return_tag="fail";
				$this->qiantai($return_tag);     
                     }                  
        		}else{
            		 if($recorddingdan){
            		    $c_money = intval($recorddingdan['money']);
                        $uid = $recorddingdan['uid'];                        
            			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
        		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '财付通充值', '$c_money', '$time')");             		  
						$recordupdate= $this->db->Query("UPDATE  `@#_user_money_record` set `status` ='2' WHERE (`id`='{$recorddingdan[id]}')");
						if($up_q2&&$up_q3&&$recordupdate){			
							$this->db->sql_commit();
						}else{
							$this->db->sql_rollback();
							$wxstatus = array("status"=>"fail","code"=>"","msg"=>"fail");
							echo $wxstatus=json_encode($wxstatus); exit;
						}        			    
						$pay = System::load_app_class('UserPay', 'common');
						$pay->fufen = $recorddingdan['score']; 						
                        $scookies = unserialize($recorddingdan['scookies']);		
            			$pay->scookie = $scookies;	        
            			$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
            			if($ok != 'ok'){
            				_setcookie("Cartlist", null);
           					echo "fail";exit;	//商品购买失败			
            			}			
            			$check = $pay->go_pay(1); 
                        if($check&&$up_q2&&$up_q3){
							$this->db->sql_commit();
							_setcookie("Cartlist", null);
							$return_tag="success";
							$this->qiantai($return_tag); 
                         }else{
							$return_tag="fail";
							$this->qiantai($return_tag);     
                         }                                     		                    
            		 }else{
						$return_tag="fail";
						$this->qiantai($return_tag);                       
            		 }                   
        		}				
				//------------------------------
				//处理业务完毕
				//------------------------------
				//log_result("即时到帐后台回调成功");				
			}else{				
				$return_tag="fail";
				$this->qiantai($return_tag); 
			}
		}else{			
			$return_tag="fail";
			$this->qiantai($return_tag); 
		}
				
		}//function end
	}	
}//