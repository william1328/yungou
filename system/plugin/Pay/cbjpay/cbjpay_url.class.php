<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");
include dirname(__FILE__).DIRECTORY_SEPARATOR."lib/DesUtils.php";	
include dirname(__FILE__).DIRECTORY_SEPARATOR."lib/ConfigUtil.php";	

class cbjpay_url {
	public function __construct(){			
		$this->db=System::load_sys_class('model');		
	} 	
	
	public function qiantai($status='comeback'){	
		sleep(2);
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
		$pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = 'cbjpay' and `pay_start` = '1'"); 
 		$resp = $_REQUEST['resp'];
 		if (null == $resp) {
			return;
		}

		$desKey = ConfigUtil::get_val_by_key ( "desKey" );
		$md5Key = ConfigUtil::get_val_by_key ( "md5Key" );
		
		$params = $this->xml_to_array ( base64_decode ( $resp ) );

		$ownSign = $this->generateSign ( $params, $md5Key );
		$params_json = json_encode ( $params );
		if ($params ['SIGN'] [0] == $ownSign) {
			// 验签正确
			//echo "签名验证正确!" . "\n";
			$des = new DesUtils (); // （秘钥向量，混淆向量）
			$decryptArr = $des->decrypt ( $params ['DATA'] [0], $desKey ); // 加密字符串
			$params ['data'] = $decryptArr;
			$respone = $this->xml_to_array($decryptArr);			
		  $return1=$respone['RETURN']['CODE'];
		  $return2=$respone['RETURN']['DESC'];
		
			if($return1=='0000'||$return2=='成功'){			//数据库操作

			$out_trade_no = $respone['TRADE']['ID'];
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
        		    $up_q1 = $this->db->Query("UPDATE `@#_orders` SET `opay` = '2', `oremark` = '京东移动支付充值', `ostatus` = '2' where `oid` = '$dingdaninfo[oid]' and `ocode` = '$dingdaninfo[ocode]'");
        			$up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + $c_money where (`uid` = '$uid')");				
    		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '京东移动支付充值', '$c_money', '$time')");     

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
        		    	$up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('$uid', '1', '账户', '京东移动支付充值', '$c_money', '$time')");   
						$recordupdate= $this->db->Query("UPDATE  `@#_user_money_record` set `status` ='2' WHERE (`id`='{$recorddingdan[id]}')");
						if($up_q2&&$up_q3&&$recordupdate){			
							$this->db->sql_commit();
						}else{
							$this->db->sql_rollback();
							$return_tag="fail";
							$this->qiantai($return_tag);  
						} 						
						$pay = System::load_app_class('UserPay', 'common');	
						
 						$pay->fufen = $recorddingdan['score'];                       
     					$scookies = unserialize($recorddingdan['scookies']);		
            			$pay->scookie = $scookies;	        
            			$ok = $pay->init($uid,$pay_type['pay_id'],'go_record');	//云购商品	

						if($ok != 'ok'){
						$return_tag="fail";
						$this->qiantai($return_tag);  	
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
                    
             }else{
				$return_tag="fail";
				$this->qiantai($return_tag);  	                                      
             } 

				}else{
					$return_tag="fail";
					$this->qiantai($return_tag);  	
			}
			
		} else {
			$return_tag="fail";
			$this->qiantai($return_tag);  	
		}
		}	


		

	public function xml_to_array($xml) {
		$array = ( array ) (simplexml_load_string ( $xml ));
		foreach ( $array as $key => $item ) {
			$array [$key] = $this->struct_to_array ( ( array ) $item );
		}
		return $array;
	}
	public function struct_to_array($item) {
		if (! is_string ( $item )) {
			$item = ( array ) $item;
			foreach ( $item as $key => $val ) {
				$item [$key] = $this->struct_to_array ( $val );
			}
		}
		return $item;
	}
	
	/**
	 * 签名
	 */
	public function generateSign($data, $md5Key) {
		$sb = $data ['VERSION'] [0] . $data ['MERCHANT'] [0] . $data ['TERMINAL'] [0] . $data ['DATA'] [0] . $md5Key;
		
		return md5 ( $sb );
	}
	
	
}//

?>