<?php 
defined('G_IN_SYSTEM')or exit('No permission resources.');
ini_set("display_errors","OFF");
class wxpay_url extends SystemAction {
	public function __construct(){			
		$this->db=System::load_sys_class('model');
	}
    public function checkpay(){
        $userpaydb = System::load_app_model("UserPay","common");
        $out_trade_no = $_POST["out_trade_no"];
        $dingdaninfo=$userpaydb->get_order($out_trade_no,'n');
        $recorddingdan=$userpaydb->get_money_record($out_trade_no);
        $pay['status']=-1;
        if($dingdaninfo){
            $pay['status']=$dingdaninfo['ostatus'];
        }
        if($recorddingdan){
            $pay['status']=$recorddingdan['status'];
			if($pay['status']==2){			
				$recorddel = $this->db->Query("DELETE FROM `@#_user_money_record` WHERE (`id`='{$recorddingdan[id]}')");  
			}			
        }
       
        if($pay['status']==2){	
		_setcookie("Cartlist", null);		
            echo json_encode(array('code'=>4,'msg'=>"支付成功!"));exit;
        }elseif($pay['status']==1){
            echo json_encode(array('code'=>999,'msg'=>"等待!"));exit;
        }else{
            echo json_encode(array('code'=>0,'msg'=>"支付失败!"));exit;
        }
    }
    public function houtai(){	
		
	    $this->db=System::load_sys_class('model');				
		$pay_type =$this->db->GetOne("SELECT * from `@#_payment` where `pay_class` = 'wxpay'"); 
        include_once dirname(__FILE__).DIRECTORY_SEPARATOR."lib/WxPayPubHelper.php";//引入文件需求
        $notify = new Notify_pub();

        WxPayConf_pub::$APPID=$pay_type['pay_uid'];
        WxPayConf_pub::$MCHID=$pay_type['pay_account'];
        WxPayConf_pub::$KEY=$pay_type['pay_key'];
        //存储微信的回
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		
        $notify->saveData($xml);
		$notify->unarr();
        $arr=$notify->getData();
        $out_trade_no=$arr['out_trade_no'];
        $total_fee_t=$arr['total_fee']/100;	
	
		$checkSign = $notify->checkSign();		
				
		
        //$total_fee_t=$arr['total_fee'];
        //$total_fee_t=10;
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($checkSign == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
		
		/*
        if(is_array($arr)){
            $returnXml = $notify->returnXml();
            echo $returnXml;
        }
		*/


        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        if($checkSign == TRUE){
			
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
               // $log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
            }
            elseif($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                //$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
            }else{                
                //此处应该更新一下订单状态，商户自行增删操作
                //$log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
		  $this->db->sql_begin();
        $updatesql = "SELECT * FROM `@#_orders` WHERE `ocode` = '{$out_trade_no}' and `ostatus` = '1' for update";	
        $dingdaninfo=$this->db->GetOne($updatesql);

			
        //查询充值订单
        $time = time();
        if(!$dingdaninfo){	//如果未查找到订单,转充值        	
          $recorddingdan=$this->db->GetOne("select * from `@#_user_money_record` where `code`='".$out_trade_no."' for update");
		  	if(!$recorddingdan || $recorddingdan['status']==2){exit("xxx");}

            $uid = $recorddingdan['uid'];
            $up_q2 = $this->db->Query("UPDATE `@#_user` SET `money` = `money` + ".$recorddingdan['money']." where (`uid` = '".$uid."')");
            $up_q3 = $this->db->Query("INSERT INTO `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('".$uid."', '1', '账户', '微信支付充值', '".$recorddingdan['money']."', '".$time."')");
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
                $wxstatus = array("status"=>"fail","code"=>"","msg"=>"fail");
                echo $wxstatus=json_encode($wxstatus); exit;
            }
            $check = $pay->go_pay(1);
		
            if($check&&$up_q2&&$up_q3){
				$this->db->sql_commit();
				_setcookie('Cartlist',NULL);
                $wxstatus = array("status"=>"success","code"=>4,"msg"=>"支付成功！");
                echo $wxstatus=json_encode($wxstatus); exit;
            }else{
                $this->db->sql_rollback();
                $wxstatus = array("status"=>"fail","code"=>"","msg"=>"fail");
                echo $wxstatus=json_encode($wxstatus); exit;
            }
        }else{
            $uid = $dingdaninfo['ouid'];
            $up_q11 ="update `@#_orders` SET `opay` = '2',`ostatus` = '2',`oremark`='微信支付充值'  where `oid` = '".$dingdaninfo['oid']."' and `ocode` = '".$out_trade_no."'";
            $up_q22 ="update `@#_user` SET `money` = `money` + ".$dingdaninfo['omoney']."  where `uid` = '".$uid."'";
            $up_q33 ="insert into `@#_user_account` (`uid`, `type`, `pay`, `content`, `money`, `time`) VALUES ('".$uid."', '1', '账户', '微信支付充值', '".$dingdaninfo['omoney']."', '".$time."')";
            $up_q1 = $this->db->Query($up_q11);
            $up_q2 = $this->db->Query($up_q22);
            $up_q3 = $this->db->Query($up_q33);
	
            if($up_q1&&$up_q2&&$up_q3){
				/**充值之后的其他处理**/
				pay_recharge_next($dingdaninfo['omoney'],$uid);				
                $this->db->sql_commit();
                $wxstatus = array("status"=>"success","code"=>4,"msg"=>"充值成功！");
                echo $wxstatus=json_encode($wxstatus); exit;
            }else{
                $this->db->sql_rollback();
                $wxstatus = array("status"=>"fail","code"=>"","msg"=>"充值失败！");
                echo $wxstatus=json_encode($wxstatus); exit;
            }
				}

				// $pay = System::load_app_class('UserPay', 'common');			
                // $pay->pay_success_order($out_trade_no,$pay_type['pay_id'],"微信支付");
				
				
            }

            //商户自行增加处理流程,
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息			
        }		
		
	}
	
}
