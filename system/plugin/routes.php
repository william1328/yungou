<?php 

/** 
 *	插件自定义路由管理器 
 *  author <战线> booobusy@gmail.com
 *  time   2015年1月8日19:22:34
 */ 

$routes = array();

$routes['plugin-pay-alipay-send'] 		= array("api"=>"Pay","select"=>"alipay","action"=>"send");
$routes['plugin-pay-alipay-callback']  = array("api"=>"Pay","select"=>"alipay","action"=>"callback");


$routes['plugin-oauth-login-qq']  = array("api"=>"Oauth","action"=>"login","data"=>"qq");
$routes['plugin-oauth-login-weibo']  = array("api"=>"Oauth","action"=>"login","data"=>"weibo");


$routes['plugin-Manager-showview']  = array("api"=>"Manager","action"=>"showview");


//云购计算方式插件后台路由
$routes['plugin-CloudWay-setway']  = array("api"=>"CloudWay","action"=>"setway");
$routes['plugin-CloudWay-postway']  = array("api"=>"CloudWay","action"=>"postway");
$routes['plugin-CloudWay-optway']  = array("api"=>"CloudWay","action"=>"optway");
$routes['plugin-CloudWay-autoway']  = array("api"=>"CloudWay","action"=>"autoway");

//支付路由
/**分润支付宝路由**/
$routes['plugin-Pay-return-uqdalipay']  = array("api"=>"Pay","action"=>"return","data"=>"uqdalipay");
/**支付宝路由**/
$routes['plugin-Pay-return-alipayReturnUrl']  = array("api"=>"Pay","action"=>"return","data"=>"eyJwYXlfY2xhc3MiOiJhbGlwYXkiLCJwYXlfZnVuIjoicWlhbnRhaSJ9");
$routes['plugin-Pay-return-alipayNotifyUrl']  = array("api"=>"Pay","action"=>"return","data"=>"eyJwYXlfY2xhc3MiOiJhbGlwYXkiLCJwYXlfZnVuIjoiaG91dGFpIn0=");

/**手机支付宝路由**/
$routes['plugin-Pay-return-malipayReturnUrl']  = array("api"=>"Pay","action"=>"return","data"=>"eyJwYXlfY2xhc3MiOiJtYWxpcGF5IiwicGF5X2Z1biI6InFpYW50YWkifQ==");
$routes['plugin-Pay-return-malipayNotifyUrl']  = array("api"=>"Pay","action"=>"return","data"=>"eyJwYXlfY2xhc3MiOiJtYWxpcGF5IiwicGF5X2Z1biI6ImhvdXRhaSJ9");

/**聚宝支付路由**/
$routes['plugin-Pay-return-jubaopayReturnUrl']  = array("api"=>"Pay","action"=>"return","data"=>"eyJwYXlfY2xhc3MiOiJqdWJhb3BheSIsInBheV9mdW4iOiJxaWFudGFpIn0=");
$routes['plugin-Pay-return-jubaopayNotifyUrl']  = array("api"=>"Pay","action"=>"return","data"=>"eyJwYXlfY2xhc3MiOiJqdWJhb3BheSIsInBheV9mdW4iOiJob3V0YWkifQ==");