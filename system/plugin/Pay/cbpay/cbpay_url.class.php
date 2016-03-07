<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");
class cbpay_url  {
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	
	
	public function qiantai($status='comeback'){	

		$v_pstatus =trim($_POST['v_pstatus']);   //  支付状态 ：20（支付成功）；30（支付失败）
		if($status=='recharge'){
		 
			 _message("支付成功！",WEB_PATH."/member/account/userbalance");
		}
		if($status=='comeback')
		{
			_setcookie("Cartlist", null);
	
			_message("支付成功！",WEB_PATH."/member/account/userbalance");
			
		}
		if($status=='buyshop')
		{
			_setcookie("Cartlist", null);
			// header("Location:".WEB_PATH."/member/cart/paysuccess");
			// exit;	

			_message("支付成功！",WEB_PATH."/member/cart/paysuccess");	
		}	if($v_pstatus=='20')
		{
			_setcookie("Cartlist", null);

			_message("支付成功！",WEB_PATH."/member/cart/paysuccess");	
		}
	
	}
	
	
	public function houtai(){
	
		$pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = 'cbpay' and `pay_start` = '1'");
		$key = $pay_type['pay_key'];		//支付KEY

		$v_oid     =trim($_POST['v_oid']);       // 商户发送的v_oid定单编号   
		//_setcookie("v_oid",$v_oid);
		$v_pmode   =trim($_POST['v_pmode']);    // 支付方式（字符串）   
		$v_pstatus =trim($_POST['v_pstatus']);   //  支付状态 ：20（支付成功）；30（支付失败）
		$v_pstring =trim($_POST['v_pstring']);   // 支付结果信息 ： 支付完成（当v_pstatus=20时）；失败原因（当v_pstatus=30时,字符串）； 
		$v_amount  =trim($_POST['v_amount']);     // 订单实际支付金额
		$v_moneytype =trim($_POST['v_moneytype']); //订单实际支付币种    
		$remark1   =trim($_POST['remark1']);      //备注字段1
		$remark2   =trim($_POST['remark2']);     //备注字段2
		$v_md5str  =trim($_POST['v_md5str']);   //拼凑后的MD5校验值  

		/**
		 * 重新计算md5的值
		 */
	                           
		$md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key));

		/**
		 * 判断返回信息，如果支付成功，并且支付结果可信，则做进一步的处理
		 */

	if ($v_md5str==$md5string){
			if($v_pstatus=="20"){
				//支付成功，可进行逻辑处理！
			$this->db->sql_begin();

            //查询充值订单
			$aa="select * from `@#_orders` where `ocode` = '$v_oid' and `ostatus` = '1' for update";
			$dingdaninfo = $this->db->GetOne($aa);

            $time = time();	
			if(!$dingdaninfo){
                $recorddingdan = $this->db->GetOne("select * from `@#_user_money_record` where `code` = '$v_oid' and `status` = '1' for update");                
             } 
			              

             if($dingdaninfo||$recorddingdan){
        		if($dingdaninfo){					
					$c_money = intval($dingdaninfo['omoney']);					
					$uid = $dingdaninfo['ouid'];
        		    $up_q1 = $this->db->Query("UPDATE `@#_orders` SET `opay` = '2', `ostatus` = '2',`oremark` = '网银在线充值' where `oid` = '$dingdaninfo[oid]' and `ocode` = '$dingdaninfo[ocode]'");
        			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
    		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '网银在线充值', '$c_money', '$time')");
		          
                    if($up_q1&&$up_q2&&$up_q3){
						/**充值之后的其他处理**/
						pay_recharge_next($c_money,$uid);							
						$this->db->sql_commit();
						$return_tag='recharge';
                        $this->qiantai($return_tag); 
                     }else{
					   $return_tag=false;
                       $this->qiantai(false);    
                     }                  
        		}else{
            		 if($recorddingdan){
            		    $c_money = intval($recorddingdan['money']);
                        $uid = $recorddingdan['uid'];                        
            			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
        		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '网银在线充值', '$c_money', '$time')");             		  
						$recordupdate= $this->db->Query("UPDATE  `@#_user_money_record` set `status` ='2' WHERE (`id`='{$recorddingdan[id]}')");       			   
						if($up_q2&&$up_q3&&$recordupdate){			
							$this->db->sql_commit();
						}else{
							$this->db->sql_rollback();
							$this->qiantai(false);   
						}   					    
						$pay = System::load_app_class('UserPay', 'common');	
						$pay->fufen = $recorddingdan['score']; 
                        $scookies = unserialize($recorddingdan['scookies']);		
            			$pay->scookie = $scookies;	        
            			$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
            			if($ok != 'ok'){
            				_setcookie("Cartlist", null);
							$return_tag=false;
							$this->qiantai(false);		
            			}			
            			$check = $pay->go_pay(1); 
                        if($check&&$up_q2&&$up_q3){
							$this->db->sql_commit();		
                           _setcookie("Cartlist", null);
							$this->qiantai(true); 						   
                         }else{
							$return_tag=false;
							$this->qiantai(false);     
                         }                                     		                    
            		 }else{
							$return_tag=false;
							$this->qiantai(false);                      
            		 }                   
        		}		                
                    
             }else{
				    _setcookie("Cartlist", null);
				  header("Location:".WEB_PATH."/member/account/userbalance");
			exit;	                                    
             } 
			}else{
				  header("Location:".WEB_PATH."/member/account/userbalance");
				exit;		
				}
	
	}
			
	}//function end
	
}//

?>