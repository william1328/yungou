<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");
class hcpay_url  {
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	
	public function qiantai($status='comeback'){	
		sleep(2);
		$out_trade_no = $_POST['BillNo'];	//商户订单号

					if($status==true){
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
		$pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = 'hcpay' and `pay_start` = '1'");
		$trade_MD5key =  $pay_type['pay_key'];		//MD5私钥		
		$out_trade_no = $_POST["BillNo"];//订单号		
		$trade_Amount = $_POST["Amount"];//金额		
		$trade_Succeed = $_POST["Succeed"];//支付状态		
		$trade_Result = $_POST["Result"];//支付结果		
		$trade_SignMD5info = $_POST["SignMD5info"]; //取得的MD5校验信息		
		$Remark = $_POST["Remark"];//备注		
	    $trade_md5src = $out_trade_no."&".$trade_Amount."&".$trade_Succeed."&".$trade_MD5key;//校验源字符串 
		$trade_md5sign = strtoupper(md5($trade_md5src)); //MD5检验结果		
		 if ($trade_SignMD5info==$trade_md5sign){//MD5验证成功
		  //开始处理汇潮支付结果
			if($trade_Result == 'SUCCESS'&& $trade_Succeed == '88'){			
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
        		    $up_q1 = $this->db->Query("UPDATE `@#_orders` SET `opay` = '2', `ostatus` = '2','oremark'='汇潮支付充值' where `oid` = '$dingdaninfo[oid]' and `ocode` = '$dingdaninfo[ocode]'");
        			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
    		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '汇潮支付充值', '$c_money', '$time')");                 
                    if($up_q1&&$up_q2&&$up_q3){
						/**充值之后的其他处理**/
						pay_recharge_next($c_money,$uid);							
						$this->db->sql_commit();
						 $this->qiantai(true); 
                        echo "success";exit;  
                     }else{
						  $this->qiantai(false); 
                       echo "fail";exit;      
                     }                  
        		}else{
            		 if($recorddingdan){
            		    $c_money = intval($recorddingdan['money']);
                        $uid = $recorddingdan['uid'];                        
            			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
        		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '汇潮支付充值', '$c_money', '$time')");             		  
						$recordupdate= $this->db->Query("UPDATE  `@#_user_money_record` set `status` ='2' WHERE (`id`='{$recorddingdan[id]}')");
						if($up_q2&&$up_q3&&$recordupdate){			
							$this->db->sql_commit();
						}else{
							$this->db->sql_rollback();
							echo "fail";exit;	//商品购买失败	
						}       			    
						$pay = System::load_app_class('UserPay', 'common');	
                        $scookies = unserialize($recorddingdan['scookies']);
						$pay->fufen = $recorddingdan['score']; 							
            			$pay->scookie = $scookies;	        
            			$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
            			if($ok != 'ok'){
            				_setcookie("Cartlist", null);
							  $this->qiantai(false); 
           					echo "fail";exit;	//商品购买失败			
            			}			
            			$check = $pay->go_pay(1); 
                        if($check&&$up_q2&&$up_q3){
						   $this->db->sql_commit();
                            _setcookie("Cartlist", null);
						    $this->qiantai(true); 
                            echo "success";exit;
                         }else{
							$this->qiantai(false); 
                            echo "fail";exit;      
                         }                                     		                    
            		 }else{
						   $this->qiantai(false); 
            		  echo "fail";exit;                       
            		 }                   
        		}		                
                    
             }else{
				   $this->qiantai(false); 
                echo "fail";exit;                                            
             } 			
				}//开始处理订单结束
		 }
         else{
			   $this->qiantai(false); 
		     echo "fail";exit;
		 }		 
	}//function end
	
}//
