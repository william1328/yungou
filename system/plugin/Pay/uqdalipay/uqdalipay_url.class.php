<?php 

defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");

class uqdalipay_url  {

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
			$recorddingdan = $this->db->GetOne("select `ostatus` from `@#_user_money_record` where `code` = '$out_trade_no'");
			if($recorddingdan){
				if($recorddingdan['ostatus']==2){
					_message('支付成功！',WEB_PATH);
				}else{
					_message('支付失败！',WEB_PATH);
				}
				_message('充值成功',WEB_PATH);
			}else{
				_message('没有这个订单！',WEB_PATH);
			}                
		 } 		
	
	}

	public function houtai(){
		$pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = 'uqdalipay' and `pay_start` = '1'");
		if(!isset($_GET['notify_type']) && $_GET['notify_type'] != 'trade_status_sync'){		
			$this->error = false;return;			
		}		
		if(!isset($_GET['seller_email']) && $_GET['seller_email'] != 'pay@yungoucms.com'){
			$this->error = false;return;			
		}
		
		$out_trade_no = $_GET['out_trade_no'];	//商户订单号
		$trade_no = $_GET['trade_no'];			//支付宝交易号
		$trade_status = $_GET['trade_status'];	//交易状态
	
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
        		    $up_q1 = $this->db->Query("UPDATE `@#_orders` SET `opay` = '分润支付宝', `ostatus` = '2' where `oid` = '$dingdaninfo[oid]' and `ocode` = '$dingdaninfo[ocode]'");
        			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
    		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '充值', '$c_money', '$time')");                 
                    if($up_q1&&$up_q2&&$up_q3){
						/**充值之后的其他处理**/
						pay_recharge_next($c_money,$uid);							
						$this->db->sql_commit();					
                        $this->error = true;return;  
                     }else{
                       $this->error = false;return;       
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
                            $this->error = false;return;  
						}        			    
						$pay = System::load_app_class('UserPay', 'common');	
						$pay->fufen = $recorddingdan['score']; 
                        $scookies = unserialize($recorddingdan['scookies']);		
            			$pay->scookie = $scookies;	        
            			$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	
            			if($ok != 'ok'){
            				_setcookie('Cartlist',NULL);
           					$this->error = false;return;	//商品购买失败			
            			}			
            			$check = $pay->go_pay(1); 
                        if($check&&$up_q2&&$up_q3){
							$this->db->sql_commit();
                            _setcookie("Cartlist", null); 				
                           $this->error = true;return;    
                         }else{
                           $this->error = false;return;      
                         }                                     		                    
            		 }else{
            		  	$this->error = false;return;                       
            		 }                   
        		}		                
                    
             }else{
                $this->error = false;return;                                             
             }           
		
		}
        //开始处理订单结束
  }	
  
  
  
}//