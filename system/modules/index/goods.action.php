<?php 

/** 
 *  普通商品控制器
 *	(c) Copyright 2015 boobusy. All Rights Reserved. 
 */
 
 class goods extends SystemAction {
 	public function __construct(){
		$this->model=System::load_app_model("cloud_goods","common");
		$this->modelg=System::load_app_model("goods","common");
	}	
	
	public function init(){
		
		
		echo "我是商品模板";
		
	}
	public function glist(){
        seo("title",_cfg("web_name")."_".L('goods.list'));
        seo("keywords",L('goods.list'));
        seo("description",L('goods.list'));
		$model_title =L('goods.list')."<br>";
		$page = System::load_sys_class("page");
		$select = ($this->segment(4)) ?  ($this->segment(4)) : '0_0_0';		
		$select = explode("_",$select);		
		$select[] = '0';
		$select[] = '0';
		
		$cid= abs(intval($select[0]));//栏目
		$bid   = abs(intval($select[1]));//品牌
		//20150528_ghm_品牌栏目优化
		if($bid){		
			$cateid=$this->model->cloud_brandinfo($bid);
			$cateid=explode(',',$cateid['cateid']);
			if(count($cateid)>1){
				$cateid=$cateid;		
			}else{
				$cateid=false;
			}
		}
		//END		
		$sort = abs(intval($select[2]));//排序
		
		$sorts = '';
		switch($sort){
			case  '2':
				$sorts = "and `g_style` = '1'";
			break;
			case  '5':
				$sorts = 'order by `g_money` DESC';
			break;
			case  '6':
				$sorts = 'order by `g_money` ASC';
			break;
			default:
				$sorts = 'order by `gid` ASC';		
		}


		/* 设置了查询分类ID 和品牌ID*/
		
		if(!$cid){	
				$brand=$this->model->cloud_brand();		
				$daohang_title = L('goods.classify');
				$one_cate_list=$this->model->cloud_parentid();
		}else{
		    $brandpcid=$this->model->cloud_brandpcid($cid);
			$brand=$this->model->cloud_brand($brandpcid);		
			$daohang=$this->model->cloud_cate1($cid);
			$daohang['info'] = unserialize($daohang['info']);			
			$daohang_title = empty($daohang['name']) ? L('goods.classify') : $daohang['name'];
		    $daohang_seo = empty($daohang['info']['meta_title']) ? $daohang['name'] : $daohang['info']['meta_title'];			$one_cate_list=$this->model->cloud_parentid($cid);
			if(!$one_cate_list && $daohang['parentid'])
		     $one_cate_list=$this->model->cloud_parentid($daohang['parentid']);				
		}	
		$model_title=$daohang_title . "_".L('cgoods.list')."_"._cfg("web_name");
		
		//分页
		$num=20;
		/* 设置了查询分类ID 和品牌ID*/
		if($cid && $bid){
			$sun_id_str = "'".$cid."'";
			$sun_cate=$this->model->cloud_cate2($daohang['cateid']);
			foreach($sun_cate as $v){
				$sun_id_str .= ","."'".$v['cateid']."'";
			}		
			$total=$this->modelg->cpgoodstotal($sun_id_str,$bid);
		}else{
			if($bid){	
			$total=$this->modelg->cpgoodstotal('',$bid);		
			}elseif($cid){			
				$sun_id_str = "'".$cid."'";
				$sun_cate=$this->model->cloud_parentid($daohang['cateid']);	
				foreach($sun_cate as $v){
					$sun_id_str .= ","."'".$v['cateid']."'";
				}
			$total=$this->modelg->cpgoodstotal($sun_id_str,'');								
			}else{		
			$total=$this->modelg->cpgoodstotal('','');
			}		
		}
		if($one_cate_list){
		$one_cate_listtag='N';
			foreach($one_cate_list as $v){
				if($cid == $v['cateid'])
			$one_cate_listtag='Y';	
			}		
		}
		$page->config($total,$num);
		$cpgoodslist=$this->modelg->cpgoodslist($sun_id_str,$bid,$sorts,$page->setlimit());
		
        $home_title=findconfig('seo','goods_title');
        $home_keywords=findconfig('seo','goods_keywords');
        $home_desc=findconfig('seo','goods_desc');
        if(!$home_title)$home_title.=_cfg("web_name")."_".L('cgoods.list');
        if(!$home_title)$home_keywords.=L('cgoods.list');
        if(!$home_title)$home_desc.=L('cgoods.list');
        seo("title",$home_title,'glist',$daohang_seo);
        seo("keywords",$home_keywords,'glist',$daohang_seo);
        seo("description",$home_desc,'glist',$daohang_seo);	 		
		
		$this -> view -> show("index.glist")
			  ->data('total',$total)->data('cpgoodslist',$cpgoodslist)->data('page',$page)
			  ->data('daohang_title',$daohang_title)->data('brand',$brand)->data('cid',$cid)
			  ->data('one_cate_list',$one_cate_list)->data('daohang',$daohang)->data('num',$num)
			  ->data('bid',$bid)->data('sort',$sort)->data('one_cate_listtag',$one_cate_listtag)->data('cateid',$cateid);		
	}
	
	/*
	普通商品详情
	*/
	public function gitem(){
        seo("title",_cfg("web_name")."_".L('goods.gitem'));
        seo("keywords",L('goods.gitem'));
        seo("description",L('goods.gitem'));
		$id=abs(intval(safe_replace($this->segment(4))));
		if(!$id)_message(L('html.err'),WEB_PATH,3);
		$where="a.`gid`={$id}";
		$item=$this->modelg->get_goods_one($where);
     	if(!$item)_message(L('goods.no'),WEB_PATH,3);
		$model_title = array();
		//获取该商品栏目
		$cateinfo=$this->model->cloud_cate1($item['g_cateid']);	
		$model_title['cate_name']=$cateinfo['name'];
		//获取该商品品牌
		$brandinfo=$this->model->cloud_brandinfo($item['g_brandid']);	
		$model_title['brand_name']=$brandinfo['name'];	
		$item['g_picarr'] = unserialize($item['g_picarr']);		
		$model_title['title']=L('cgoods.detail')."<br>";
		//获取Cookie配置信息
		$this -> view -> show("index.gitem")->data('id',$id)->data('model_title',$model_title)		
		->data('cgoods_url0',$cgoods_url0)->data('style0',$style0)
		->data('item',$item)->data('style0',$style0)->data('preuser_shop_time',$preuser_shop_time);			
	}	
	
	/*
	手机普通商品详情
	*/	
	public function goodsdesc(){
        seo("title",_cfg("web_name")."_".L('goods.gitem'));
        seo("keywords",L('goods.gitem'));
        seo("description",L('goods.gitem'));
		$id=abs(intval(safe_replace($this->segment(4))));
		if(!$id)_message(L('html.err'),WEB_PATH,3);
		$where="a.`gid`={$id}";
		$item=$this->modelg->get_goods_one($where);
     	if(!$item)_message(L('goods.no'),WEB_PATH,3);
		$model_title = array();
		//获取该商品栏目
		$cateinfo=$this->model->cloud_cate1($item['g_cateid']);	
		$model_title['cate_name']=$cateinfo['name'];
		//获取该商品品牌
		$brandinfo=$this->model->cloud_brandinfo($item['g_brandid']);	
		$model_title['brand_name']=$brandinfo['name'];	
		$item['g_picarr'] = unserialize($item['g_picarr']);		
		$model_title['title']=L('cgoods.detail')."<br>";
		//获取Cookie配置信息
		$this -> view -> show("index.goodsdesc")->data('id',$id)->data('model_title',$model_title)		
		->data('cgoods_url0',$cgoods_url0)->data('style0',$style0)
		->data('item',$item)->data('style0',$style0)->data('preuser_shop_time',$preuser_shop_time);			
	}	
	
	
 }