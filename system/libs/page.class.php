<?php
/**
 * page.class.php	分页类
 *
 * @copyright			(C) 2005-2010 战线
 * @license				V3.1.2
 * @lastmodify			2014-05-21
 */
 


class  page {

	private $total;					//总条数
	private $num;					//显示条数
	private $url;					//没有&page的URL
	private $limit;					//限制显示条数
	private $page;					//当前页数
	private $pagetotal;				//一共多少页数	
	private $pageurl;				//外部定义分页url

	public function config($total,$num,$pageurl=NULL){		
		$this->total=$total;		
		$this->num=$num;
		$this->pageurl=$pageurl;		
		$this->pagetotal=ceil($this->total/$this->num);
		$this->page = $this->GetPageNum();
		
	//	$this->page=$this->get_url_page($page);
		$this->limit=$this->setlimit();
	}	
	
	private function css(){
		$html = "<style>";
		$html .="#Page_Ul{float:left;}";
		
		$html .="#Page_Ul li{float:left;display:block}";
		$html .="#Page_Ul #Page_Total{border:1px solid #ccc;padding:5px 8px;}";
		$html .="#Page_Ul li a{padding:5px 8px; border:1px solid #ccc;}";
		$html.="</style>";
		echo $html;	
	}
	
	//主入口
	public  function show($style='',$css=false,$ret=''){
		$css ? $this->css() : FALSE;
		$style=strtolower(trim($style));		
		switch($style){		
			case "simple" :		
				return $this->ordinary($ret);			
				break;			
			case "two" :
				return $this->pagelist($ret);
				break;				
			default:			
				return $this->pagelist($ret);	
				break;		
		}		
	}
	
	
	// 普通的样式
	private function ordinary($ret){
		$Prev=$this->page-1;
		$next=$this->page+1;
		$html_l="<ul id='Page_Ul'>";
		$html='';				
		if($Prev!=0){
			$html.="<li id='Page_Prev'><a href=\"{$this->url[0]}{$Prev}{$this->url[1]}\">上一页</a></li>";	
		}else{
			$html.="<li id='Page_Prev'><a href=\"javascript:void(0);\">上一页</a></li>";	
		}	
		if($next<=$this->pagetotal){
			$html.="<li id='Page_Next'><a href=\"{$this->url[0]}{$next}{$this->url[1]}\">下一页</a></li>";
		}else{
			$html.="<li id='Page_Next'><a href=\"javascript:void(0);\">下一页</a></li>";	
		}
		$html.="<li id='Page_One'><a href=\"{$this->url[0]}1{$this->url[1]}\">首页</a></li>";	
		$html.="<li id='Page_End'><a href=\"{$this->url[0]}{$this->pagetotal}{$this->url[1]}\">尾页</a></li>";	
		$html_r="</ul>";
		if($this->total==0){
			return;
		}else{
			if($ret=='li'){
				return $html;
			}else{
				return $html_l.$html.$html_r;
			}
		}
	}
	
	// 默认的样式-列表样式
	private function pagelist($ret){	
		
		$listnum=floor(7/2);		
		$html_l="<ul id='Page_Ul'>";
		$html='';
		$html.="<li id='Page_Total'>{$this->total}条";
		$html.="<li id='Page_One'><a href=\"{$this->url[0]}1{$this->url[1]}\">首页</a></li>";
		if($this->page==1){
			$html.="<li id='Page_Prev'><a href=\"{$this->url[0]}".($this->page).$this->url[1]."\">上一页</a></li>";
		}else{
			$html.="<li id='Page_Prev'><a href=\"{$this->url[0]}".($this->page-1).$this->url[1]."\">上一页</a></li>";
		}
		for($i=$listnum;$i>=1;$i--){
			$page=$this->page-$i;
			
			if($page<1){
				continue;			
			}else{
				$html.="<li class='Page_Num'><a href=\"{$this->url[0]}{$page}{$this->url[1]}\">{$page}</a></li>";
			}
			
		}
		
		$html.="<li class='Page_This'>{$this->page}</li>";
		
		for($i=1;$i<=$listnum;$i++){
			
			$page=$this->page+$i;
			if($page<=$this->pagetotal){
				$html.="<li class='Page_Num'><a href=\"{$this->url[0]}{$page}{$this->url[1]}\">{$page}</a></li>";
			}else{
				continue;	
			}
		}
		if($this->page==$this->pagetotal){
			$html.="<li id='Page_Next'><a href=\"{$this->url[0]}".($this->page).$this->url[1]."\">下一页</a></li>";
		}else{
			$html.="<li id='Page_Next'><a href=\"{$this->url[0]}".($this->page+1).$this->url[1]."\">下一页</a></li>";
		}	
		$html.="<li id='Page_End'><a href=\"{$this->url[0]}{$this->pagetotal}{$this->url[1]}\">尾页</a></li>";
		$html_r="</ul>";
		
		if($this->total==0){
			return;
		}else{
			if($ret=='li'){
				return $html;
			}else{
				return $html_l.$html.$html_r;
			}
		}
	
	}
	
	
	private function GetPageNum(){

		$url = G_PARAM_URL;
		$Rconfig = System::load_sys_config('param');
		if(isset($_GET[$Rconfig['page_q']])){					
			$url =  preg_replace("/&".$Rconfig['page_q']."=([0-9]{1,10})/i","&page=",$url);
			$this->url = array(
				0=>WEB_PATH."/".$url,
				1=>""
			);
			return abs($_GET[$Rconfig['page_q']]);
		}
	
		preg_match("/\/".$Rconfig['page_p']."([0-9]{1,10})/i", $url,$matches);
		if(isset($matches[1])){
			$page = abs($matches[1]);
			$url = explode($matches[0], $url);
			
		}else{
			$page = 1;
			$url = array(0=>G_PARAM_URL,1=>"");
		}		
		
		$this->url = array(
			0=>WEB_PATH."/".$url[0]."/".$Rconfig['page_p'],
			1=>$url[1]
		);
		return $page;

	}
	
	//获取URL
	private function geturl(){
		
		
		/*		
		echo $this->param_url;
		$p = $this->route_config['page'];		
		echo "<br>";		
		preg_match("/p[0-9]{1,10}/i", $this->param_url,$matches);			
		var_dump($matches);
		
			
		if(isset($matches[0])){
			$this->route_url['page'] = abs($matches[0]);
		}else{
			$this->route_url['page'] = 1;
		*/

		
		//echo $this->pageurl;
		$rouyt = ROUTE_M."/".ROUTE_C."/".ROUTE_A;
		if($rouyt != $this->pageurl){
			if(strpos($this->pageurl,"-") === FALSE){		
				$this->pageurl = preg_replace("/\//", "-1/", $this->pageurl,1);			
			}
		}
		$urls = WEB_PATH."/".$this->pageurl;
		$urls = explode("-".$this->page,$urls);		
		if(!isset($urls[1]))$urls[1] = "";
		return $url=array(0=>$urls[0]."-",1=>$urls[1]);
			
		$url=array(0=>'',1=>'');			
		$urls = WEB_PATH."/".$this->pageurl;
		//$urls = str_ireplace("/index.php/","/",$urls);
	
		$urls=trim($urls,'/');
		$parse=parse_url($urls);
		if(isset($parse['query'])){		
			parse_str($parse['query'],$parses);		
			unset($parses['p']);			
			if(empty($parses)){
				$urls=$parse['path']."?";
			}else{				
				$urls=$parse['path']."?".http_build_query($parses).'&';				
				$urls = str_ireplace("%2f",'/',$urls);
				$urls = str_ireplace("=&",'/&',$urls);
			}	
		}else{
			$urls=$parse['path']."?";
		}		
		$urls  =   preg_replace("#\/\/#","/",$urls);
	 	$url[0]=$urls.'p=';	
		return $url;
	}
	
		
	//设置LIMIT
	public function setlimit($all=0){
		return ($all == 1) ? ($this->page-1)*$this->num.",".$this->num : " LIMIT ".($this->page-1)*$this->num.",".$this->num." ";
	}
	
	
	//使外部能访问limit
	public function __get($value){
		return $this->$value;		
	}

}