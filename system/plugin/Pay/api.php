<?php

 //插件其他操作
$select       = isset($_GET['select']) ? basename($_GET['select']) : false;	  //选择的 支付方式
$action       = isset($_GET['action']) ? basename($_GET['action']) : false;   //选择的 action
 function payway_view_list(){ 
     $db=System::load_sys_class("model");   
     $paysql="select * from `@#_payment` WHERE `pay_start`='1'";    
     $paylist= $db->GetList($paysql);    
   //  include "tpl/payway.tpl.php";		
 }
 
 
 
 
 
  function payway_view_submit(){ 
     $paydata = isset($_GET['data']) ? basename($_GET['data']) : false;   //数据 
     $pay_id=abs($paydata); 
     $db=System::load_sys_class("model");   
     $paysql="select * from `@#_payment` WHERE `pay_id`='{$pay_id}'";    
     $paylist= $db->GetOne($paysql); 
           		
 }

  function payway_view_pay(){ 
     $paydata = isset($_POST['paydata']) ? $_POST['paydata'] : false; //数据 
     if($paydata){
         $paydata = json_decode(base64_decode($paydata)); 
        if(is_object($paydata)){
          $paydata = (array)$paydata;
        }          
       // file_put_contents('123.txt',var_export($paydata,true)."\n",FILE_APPEND);
        //file_put_contents('123.txt',$paydata['pay_class']."\n",FILE_APPEND);
         include dirname(__FILE__)."/".$paydata['pay_class']."/lib/".$paydata['pay_class'].".class.php";
                  
         //新建入口类
        $apiclass = new $paydata['pay_class']();
		$apiclass->config($paydata);
		$apiclass->send_pay(); 
      
        /* 
         $db=System::load_sys_class("model");    
         $paysql="select * from `@#_payment` WHERE `pay_id`='{$pay_id}'";    
         $paylist= $db->GetOne($paysql);   
        */       
         
     }else{
         file_put_contents('123.txt',"false\n",FILE_APPEND);        
     }
     
               		
 }  
    /**支付返回**/ 
   function payway_view_return(){ 
      $returndata = isset($_GET['data']) ? basename($_GET['data']) : false;   //数据    
      $returndata = json_decode(base64_decode($returndata));
      if(is_object($returndata)){
        $returndata = (array)$returndata;
      }
      include  dirname(__FILE__)."/".$returndata['pay_class']."/".$returndata['pay_class']."_url.class.php";
      $payclass= $returndata['pay_class']."_url";
      $apiclass = new  $payclass();
  	  $apiclass->$returndata['pay_fun']();
               		
 } 

//开始绑定 界面操作	
$bandaction = 'payway_view_'.$action;
if(function_exists($bandaction)){
	$bandaction();
}else{
	_SendStatus(404);
}
//分发控制器
/**
if(!$select || !file_exists(dirname(__FILE__)."/".$select."/./".$action.".php")){
	_error("Plugin not {$select}",'这个插件不存在...');	
}

require dirname(__FILE__)."/".$select."/./".$action.".php";
**/