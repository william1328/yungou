<?php 

class AutoLottery {

	public function __construct() {	
		$this->model=System::load_app_model("UserPay_cloud","common");
        $this->modela = System::load_app_model("UserPay","common");//加载购买商品model
        $this->orderdb = System::load_app_model("order","common");//加载订单model		
	}	
				
	//ajax 商品揭晓
	public function autolottery_ret_install($shop_info=''){		
		$shop_info['xsjx_time']=$shop_info['xsjx_time'].'.000';	
        include dirname(__FILE__)."/"."CloudTocode".".class.php";
        $tocode = new CloudTocode();       		
		$tocode->run_tocode($shop_info['xsjx_time'],100,$shop_info['canyurenshu']);
		$code =$tocode->go_code;		
		$content = addslashes($tocode->go_content);
		$counttime = $tocode->count_time;       
		$where="`ogid` = '$shop_info[id]' and `ogocode` LIKE  '%$code%'";    
		$oidinfo = $this->modela->cloud_gocode($where); 
		$wherewords="`oid`=$oidinfo[oid]"; 
		$u_go_info = $this->orderdb->ready_order($wherewords); 
		$u_go_info=$u_go_info[0];
		
		if($u_go_info){			
		    $u_info = $this->modela->SelectUserUid($u_go_info['ouid']);
			$u_info['username'] = _htmtocode($u_info['username']);
			$q_user = serialize($u_info);
			$q_uid = $u_info['uid'];				
		}else{
			$reg_code = $this->suan_zd_code($shop_info['id'],$code);	
			if(!$reg_code){echo '-2';exit;}
			$code=$reg_code;
			$where="`ogid` = '$shop_info[id]' and `ogocode` LIKE  '%$reg_code%'";    
			$oidinfo = $this->modela->cloud_gocode($where); 
			$wherewords="`oid`=$oidinfo[oid]"; 
			$u_go_info = $this->orderdb->ready_order($wherewords); 
			$u_go_info=$u_go_info[0];   
            $u_info = $this->modela->SelectUserUid($u_go_info['ouid']);
			$u_info['username'] = addslashes($u_info['username']);
			$q_user = serialize($u_info);
			$q_uid = $u_info['uid'];
		}			
			
		$update_cgoods = "`q_uid` = '$q_uid',
			`q_user` = '$q_user',
			`q_user_code` = '$code',
			`q_content`	= '$content',
			`q_counttime` ='$counttime',
			`q_end_time` = '$shop_info[xsjx_time]',
			`q_showtime` = 'Y'
			 where `id` = '$shop_info[id]'";
          	$q_1 =$this->model->UpdateCgoods($update_cgoods);
								 
			if($u_go_info){
				$wherewords="`oid` = '$u_go_info[oid]' and `ouid` = '$u_go_info[ouid]' and `ogid` = '$shop_info[id]' and `oqishu` = '$shop_info[qishu]' and `ofstatus`='0'";
				$data=array();
				$data['owin']=$code;
				$data['ofstatus']='1';
				$q  = $this->orderdb->update_order($wherewords,$data);							 
			}else{
			  $q_2 = true;
			}
			 $post_arr= array("uid"=>$u_go_info['ouid'],"gid"=>$shop_info['id'],"send"=>1);
			_g_triggerRequest(WEB_PATH.'/index/send/send_shop_code',false,$post_arr);
			$q_3 = $this->autolottery_install($shop_info);
			if($q_1&&$q_3){					
					echo '-6';exit;
			}else{				
				echo '-2';exit;				
			}					
	
	}
	
	private function suan_zd_code($gid,$r_code){
		$wherewords="`ogid`='$gid'";   
		$codesinfo = $this->modela->cloud_gocodel($wherewords); 		
		foreach($codesinfo as $key=>$cv){
			$codes[$key]['ogocode']= $cv['ogocode'];
		}
		
		if(empty($codes))return false;		
		$html = '';
		foreach($codes as $key=>$cv){
			$html .= $cv['ogocode'].',';
		}			
		if(empty($codes))return false;		
		$codes = explode(',',$html);	
		array_pop($codes);
		asort($codes);	//正序		
		unset($html);		
		$go_code  = $r_code;
		if($go_code > end($codes)){
				$zd_jin_code = end($codes);		
		}else{			
			$t=90000000;
			foreach($codes as $k=>$v){				
					$s = abs($go_code-$v);				
					if($s <= $t){			
						$t = $s; $zd_jin_code = $v;				
					}else{
						break;
					}
			}		
		}			
		unset($codes);	
		return $zd_jin_code;
	}
	
	private function autolottery_install($shop=null){
		$update_cloud = System::load_app_model("UserPay_cloud","common");//加载云购购买商品model	
		if($shop['qishu'] < $shop['maxqishu']){	
		    $maxinfo = $update_cloud->SelectCgoods_gid($shop['gid']);
			if(!$maxinfo){
				$maxinfo=array("qishu"=>$shop['qishu']);
			}
			$goods = System::load_app_model("goods","common");//加载云购购买商品model			
			$q_1 =$goods->cloud_goods_next($maxinfo);		    
           /**             
			$time = time();
			System::load_app_fun("content",G_ADMIN_DIR);		
			$goods = $shop;
			$qishu = $goods['qishu']+1;
			$shenyurenshu = $goods['zongrenshu'] - $goods['def_renshu'];
			//$query_table = content_get_codes_table();
            			
			$id = $this->db->insert_id();		
			$q_2 = content_get_go_codes($goods['zongrenshu'],3000,$id);
            */
			if($q_1){
				return true;
			}else{
				return false;
			}
		}
		return true;
	}
}