<?php 
defined('G_IN_SYSTEM')or exit('no');
System::load_app_class('admin',G_ADMIN_DIR,'no');
class goods extends admin {
	private $db,$cate,$model,$member;
	public function __construct(){		
		parent::__construct();		
		$this->db=System::load_sys_class('model');
        $this->model=System::load_app_model("goods","common");
        $this->cate=System::load_app_model("cate","common");
        $this->member=System::load_app_model("member","common");
        $this->order=System::load_app_model("order","common");
		$this->models=$this->cate->get_model();
        $this->goods_ment=array(
            array("lists","普通商品管理",ROUTE_M.'/'.ROUTE_C."/goods_list"),
            array("insert","添加商品",ROUTE_M.'/'.ROUTE_C."/goods_add")
        );
        $this->cgoods_ment=array(
            array("lists","云购商品管理",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists"),
            array("insert","添加商品",ROUTE_M.'/'.ROUTE_C."/cloud_goods_add")
        );
        $this->brand_ment=array(
            array("lists","品牌管理",ROUTE_M.'/'.ROUTE_C."/brand_lists"),
            array("insert","添加品牌",ROUTE_M.'/'.ROUTE_C."/brand_add")
        );
	}
    //商品列表
    public function goods_list(){
        $cateid=$this->segment(4);
        $where = '';
        $order='g_sort desc';
        $where="`g_type`=1";
        if(isset($_POST['sososubmit'])){
            $data=_post();
            $posttime1 = !empty($data['posttime1']) ? strtotime($data['posttime1']) : NULL;
            $posttime2 = !empty($data['posttime2']) ? strtotime($data['posttime2']) : NULL;
            if($posttime2 < $posttime1)_message("结束时间不能小于开始时间");
            if(!empty($posttime1)){
                $where .= " AND `g_add_time` >= '$posttime1'  ";
            }
            if(!empty($posttime2)){
                $where .= " AND `g_add_time` <= '$posttime2'  ";
            }
            if(!empty($data['sosotext'])){
                if($data['sotype'] == 'cateid'){
                        $where .= " AND  `g_cateid` = '".$data['sosotext']."'";
                }
                if($data['sotype'] == 'title'){
                    $where .= " AND `g_title` LIKE  '%".$data['sosotext']."%'";
                }
                if($data['sotype'] == 'id'){
                    $where .= " AND `gid` = '".$data['sosotext']."'";
                }
            }
        }
        $num=20;
        $total=$this->model->get_goods_num($where);
        $page=System::load_sys_class('page');
        $page->config($total,$num);
        $goodslist=$this->model->get_goods($where,"*",$order,$page->setlimit(1));
        foreach($goodslist as &$row){
            $row['cate_name']=$this->cate->get_cate_name("cateid=".$row['g_cateid']);
        }
        $this->view->data("cateid",$cateid);
        $this->view->data("total",$total);
        $this->view->data("page",$page->show('one',true));
        $this->view->tpl('goods.lists')->data("shoplist",$goodslist)->data("ments",$this->goods_ment);
    }
    //编辑商品
    public function goods_edit(){
        $shopid=intval($this->segment(4));
        $shopinfo=$this->model->get_goods_one("a.g_status=1 and a.gid='".$shopid."'");
        if(!$shopinfo)_message("参数不正确");

        if(isset($_POST['dosubmit'])){
            $data=_post();
            $g_style=_post("g_style");
            $data['g_style']=array_sum($g_style);
            $data['g_title_style']=serialize(array("color"=>$data['title_style_color'],"bold"=>$data['title_style_bold']));
            unset($data['title_style_color']);
            unset($data['title_style_bold']);
            unset($data['dosubmit']);
            unset($data['sub_text_des']);
            unset($data['sub_text_len']);			
            if(!$data['g_cateid'])_message("请选择栏目");
            if(!$data['g_brandid'])_message("请选择品牌");
            if(!$data['g_title'])_message("标题不能为空");
            if(!$data['g_thumb'])_message("缩略图不能为空");
            $g_picarr=array();
            $g_picarr=_post("uppicarr");
            $g_picarr = serialize($g_picarr);
            $data['g_picarr']=$g_picarr;
            unset($data['uppicarr']);
            $data['g_content']=$_POST['g_content'];
            $res=$this->model->goods_save($data,"gid='".$shopid."'");
            if($res){
                _message("修改成功!");
            }else{
                _message("修改失败!");
            }
        }
        $cateinfo=$this->cate->get_cate_one("`cateid` = '".$shopinfo['g_cateid']."'");
        $Brand_arr=$this->model->get_brand("concat(',',`cateid`,',') LIKE '%,".$shopinfo['g_cateid'].",%'");
        foreach($Brand_arr as $row){
            $BrandList[$row['id']]=$row;
        }
        $cate=$this->cate->get_cate_list("`model` = '1'","*","`parentid` ASC,`cateid` ASC");
        foreach($cate as $row){
            $categorys[$row['cateid']]=$row;
        }
        $tree=System::load_sys_class('tree');
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;';
        $categoryshtml="<option value='\$cateid'>\$spacer\$name</option>";
        $tree->init($categorys);
        $categoryshtml=$tree->get_tree(0,$categoryshtml);
        $categoryshtml.='<option value="'.$cateinfo['cateid'].'" selected="true">'.$cateinfo['name'].'</option>';

        if($shopinfo['g_title_style']){
            $shopinfo['g_title_style']=unserialize($shopinfo['g_title_style']);
        }
        $shopinfo['picarr'] = unserialize($shopinfo['g_picarr']);

        $this->view->data("BrandList",$BrandList);
        $this->view->data("categoryshtml",$categoryshtml);
        $this->view->tpl('goods.goods_edit')->data("shopinfo",$shopinfo)->data("ments",$this->goods_ment);
    }
    public function goods_set(){
        $set_type=$this->segment(4);
        $gid=$this->segment(5);
        $info=$this->model->get_goods_one("a.`gid`='".$gid."'   and   a.`g_type`='1' ");
        if($set_type=="renqi")$g_style=$info['g_style']+1;
        if($set_type=="comm")$g_style=$info['g_style']+2;
		$info['g_style']=$g_style;
        $res=$this->model->goods_save($info,"`gid`='".$gid."'");
        if($res){
            _message("设置成功！",WEB_PATH."/".ROUTE_M.'/'.ROUTE_C."/goods_list");
        }else{
            _message("设置失败！");
        }
    }
    public function goods_unset(){
        $set_type=$this->segment(4);
        $gid=$this->segment(5);
        $info=$this->model->get_goods_one("a.`gid`='".$gid."'   and   a.`g_type`='1' ");
        if($set_type=="renqi")$g_style=$info['g_style']-1;
        if($set_type=="comm")$g_style=$info['g_style']-2;
		$info['g_style']=$g_style;
        $res=$this->model->goods_save($info,"`gid`='".$gid."'");
        if($res){
            _message("设置成功！",WEB_PATH."/".ROUTE_M.'/'.ROUTE_C."/goods_list");
        }else{
            _message("设置失败！");
        }
    }

    //添加商品
    public function goods_add(){

        if(isset($_POST['dosubmit'])){

            $data=_post();
            $g_style=_post("g_style");
            $data['g_style']=array_sum($g_style);
            $data['g_title_style']=serialize(array("color"=>$data['title_style_color'],"bold"=>$data['title_style_bold']));
            unset($data['title_style_color']);
            unset($data['title_style_bold']);
            unset($data['dosubmit']);

            if(!$data['g_cateid'])_message("请选择栏目");
            if(!$data['g_brandid'])_message("请选择品牌");
            if(!$data['g_title'])_message("标题不能为空");
            if(!$data['g_thumb'])_message("缩略图不能为空");
            $g_picarr=array();
            $g_picarr=_post("uppicarr");
            $g_picarr = serialize($g_picarr);
            $data['g_picarr']=$g_picarr;
            unset($data['uppicarr']);
            $info=$this->AdminInfo;
            $data['g_add_uid']=$info['mid'];
            $data['g_add_time']=time();
            $data['g_type']=1;
            $data['g_status']=1;
            $data['g_content']=$_POST['g_content'];
            $res=$this->model->goods_add($data);
            if($res){
                _message("商品添加成功!");
            }else{
                _message("商品添加失败!");
            }

            header("Cache-control: private");
        }
        $cateid=intval($this->segment(4));
        $cate=$this->cate->get_cate_list("`model` = '1'","","`parentid` ASC,`cateid` ASC");
        foreach($cate as $row){
            $categorys[$row['cateid']]=$row;
        }
        $tree=System::load_sys_class('tree');
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;';
        $categoryshtml="<option value='\$cateid'>\$spacer\$name</option>";
        $tree->init($categorys);
        $categoryshtml=$tree->get_tree(0,$categoryshtml);
        $categoryshtml='<option value="0">≡ 请选择栏目 ≡</option>'.$categoryshtml;
        if($cateid){
            $cateinfo=$this->cate->get_cate_one("`cateid` = '".$cateid."'");
            if(!$cateinfo)_message("参数不正确,没有这个栏目",G_ADMIN_PATH.'/'.ROUTE_C.'/addarticle');
            $categoryshtml.='<option value="'.$cateinfo['cateid'].'" selected="true">'.$cateinfo['name'].'</option>';
            $BrandList=$this->model->get_brand("`cateid`='$cateid'");
        }else{
            $BrandList=$this->model->get_brand();
        }
        $this->view->data("categoryshtml",$categoryshtml);
        $this->view->tpl('goods.goods_add')->data("BrandList",$BrandList)->data("ments",$this->goods_ment);

    }
    public function json_brand(){
        $id=_get("cid");
        $res=$this->model->get_brand("concat(',',`cateid`,',') LIKE '%,".$id.",%'");
        echo json_encode($res);exit;
    }
	    //云购START
	//商品列表	
	public function cloud_goods_lists(){
        $this->model->get_codes_table(5);
        $ments=array(
            array("lists","云购商品管理",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists"),
            array("add","添加商品",ROUTE_M.'/'.ROUTE_C."/cloud_goods_add"),
            array("renqi","人气商品",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/renqi"),
            array("xsjx","限时揭晓商品",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/xianshi"),
            array("qishu","期数倒序",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/qishu"),
            array("danjia","单价倒序",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/danjia"),
            array("money","商品价格倒序",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/money"),
            array("jiexiaook","已揭晓",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/jiexiaook"),
            array("maxqishu","<font color='#f00'>期数已满商品</font>",ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/maxqishu"),
        );

        $cateid=$this->segment(4);
		$where = 'a.g_type=3';
        $order='';
		if($cateid){
			if($cateid=='jiexiaook'){
				$where .= " and b.`q_uid` is not null";
			}
			if($cateid=='maxqishu'){
				$where .= " and b.`qishu` = b.`maxqishu` and b.`q_end_time` is not null";
			}			
			if($cateid=='renqi'){
				$where .= " and a.`g_style` in (1,3)";
			}
			if($cateid=='xianshi'){
				$where .= " and b.`xsjx_time` != '0'";
			}
			if($cateid=='qishu'){
                $order = "b.`qishu` DESC";
				$ments[4][1]="期数正序";
				$ments[4][2]=ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/qishuasc";
			}
			if($cateid=='qishuasc'){
                $order = "b.`qishu` ASC";
                $ments[4][1]="期数倒序";
                $ments[4][2]=ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/qishu";
			}
			if($cateid=='danjia'){
                $order = "b.`price` DESC";
                $ments[5][1]="单价正序";
                $ments[5][2]=ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/danjiaasc";
			}
			if($cateid=='danjiaasc'){
                $order = "b.`price` ASC";
                $ments[5][1]="单价倒序";
                $ments[5][2]=ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/danjia";
			}
			if($cateid=='money'){
                $order = "a.`g_money` DESC";
                $ments[6][1]="商品价格正序";
                $ments[6][2]=ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/moneyasc";
			}
			if($cateid=='moneyasc'){
                $order = "a.`g_money` ASC";
                $ments[6][1]="商品价格倒序";
                $ments[6][2]=ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists/money";
			}
			if($cateid==''){
                $order="`a.gid` DESC";
			}
			if(intval($cateid)){
				$where .= " and a.`g_cateid` = '$cateid'";
			}			
		}else{
            $order="a.`gid` DESC";
		}
		if(isset($_POST['sososubmit'])){
            $data=_post();
            $list_where='';
            if(!empty($data['posttime1'])){
                $list_where.="b.`ur_time` >= ".strtotime($data['posttime1']);
            }
            if(!empty($data['posttime2'])){
                $list_where.="b.`ur_time` <= ".strtotime($data['posttime2']);
            }
			$sotype = $data['sotype'];
			$sosotext = $data['sosotext'];
			if($data['posttime1'] && $data['posttime2']){
				if(strtotime($data['posttime2']) < strtotime($data['posttime1']))_message("结束时间不能小于开始时间");
			}

			if(!empty($sosotext)){			
				if($sotype == 'cateid'){
					$sosotext = intval($sosotext);
					$list_where .= " AND a.`g_cateid` = '$sosotext'";
				}
				if($sotype == 'brandid'){
					$sosotext = intval($sosotext);
					$list_where .= " AND a.`g_brandid` = '$sosotext'";
				}
				if($sotype == 'brandname'){
					$sosotext = htmlspecialchars($sosotext);
					$info = $this->model->get_brand_one("`name` LIKE '%".$sosotext."%' ");
					if($info)
						$list_where .= " AND a.`g_brandid` = '$info[id]'";

				}
				if($sotype == 'catename'){
					$sosotext = htmlspecialchars($sosotext);
					$info = $this->cate->get_cate_one("`model`=1 AND `name` LIKE '%".$sosotext."%'");
					if($info)
						$list_where .= " AND a.`g_cateid` = '$info[cateid]'";

				}
				if($sotype == 'title'){
					$sosotext = htmlspecialchars($sosotext);
					$list_where .= " AND a.`g_title` LIKE '%".$sosotext."%'";
				}
				if($sotype == 'id'){
					$sosotext = intval($sosotext);
					$list_where = " AND a.`gid` = '$sosotext'";
				}
			}
            $list_where=trim($list_where," AND");
            if(!empty($list_where) && !empty($where)){
                $where .=" AND ".$list_where;
            }else{
                $where .=$list_where;
            }


		}
		$num=10;
		$total=$this->model->cloud_goods_num1($where);
		$page=System::load_sys_class('page');
		if(isset($_GET['p'])){$pagenum=$_GET['p'];}else{$pagenum=1;}	
		$page->config($total,$num);
        $goodslist=$this->model->cloud_goods($where,$order,$page->setlimit(1));
        foreach($goodslist as &$row){
            $row['cate_name']=$this->cate->get_cate_name("cateid=".$row['g_cateid']);
        }
        $this->view->data("ments",$ments);
        $this->view->data("total",$total);
		$this->view->data("cateid",$cateid);
        $this->view->data("page",$page->show('one',true));
		$this->view->tpl('goods.cloud_lists')->data("shoplist",$goodslist)->data("ments",$this->cgoods_ment);
	}
    public function cloud_goods_set(){
        $set_type=$this->segment(4);
        $gid=$this->segment(5);
        $info=$this->model->get_goods_one("a.`gid`='".$gid."' and  a.`g_type`='3' ");
        if($set_type=="renqi")$g_style=$info['g_style']+1;
        if($set_type=="comm")$g_style=$info['g_style']+2;
		$info['g_style']=$g_style;
        $res=$this->model->goods_save($info,"`gid`='".$gid."'");
        if($res){
            _message("设置成功！",WEB_PATH."/".ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists");
        }else{
            _message("设置失败！");
        }
    }
    public function cloud_goods_unset(){
        $set_type=$this->segment(4);
        $gid=$this->segment(5);
        $info=$this->model->get_goods_one("a.`gid`='".$gid."'   and   a.`g_type`='3' ");
        if($set_type=="renqi")$g_style=$info['g_style']-1;
        if($set_type=="comm")$g_style=$info['g_style']-2;
		$info['g_style']=$g_style;
        $res=$this->model->goods_save($info,"`gid`='".$gid."'");
        if($res){
            _message("设置成功！",WEB_PATH."/".ROUTE_M.'/'.ROUTE_C."/cloud_goods_lists");
        }else{
            _message("设置失败！");
        }
    }
    //编辑商品
    public function cloud_goods_edit(){
        $shopid=intval($this->segment(4));
        $shopinfo=$this->model->cloud_goods_one("a.`gid` = '".$shopid."'");
        if($shopinfo['q_end_time'])_message("该商品已经揭晓,不能修改!",WEB_PATH.'/'.ROUTE_M.'/goods/cloud_goods_lists');
        if(!$shopinfo)_message("参数不正确");

        if(isset($_POST['dosubmit'])){

            $data=_post();
            $g_style=_post("g_style");
            $data['g_type']=3;
            $data['yunjiage']=$shopinfo['price'];
            $data['g_style']=array_sum($g_style);
            $data['g_title_style']=serialize(array("color"=>$data['title_style_color'],"bold"=>$data['title_style_bold']));
            if(!$data['g_cateid'])_message("请选择栏目");
            if(!$data['g_brandid'])_message("请选择品牌");
            if(!$data['g_title'])_message("标题不能为空");
            if(!$data['g_thumb'])_message("缩略图不能为空");
            $g_picarr=array();
            $g_picarr=_post("uppicarr");
            $g_picarr = serialize($g_picarr);
            $data['g_picarr']=$g_picarr;
			unset($data['uppicarr']);
            if($data['xsjx_time'] != ''){
                $xsjx_time = strtotime($data['xsjx_time']) ? strtotime($data['xsjx_time']) : time();
                $xsjx_time_h = intval($data['xsjx_time_h']) ? $data['xsjx_time_h'] : 36000;
                $xsjx_time += $xsjx_time_h;
            }else{
                $xsjx_time = '0';
            }
            unset($data['title_style_color']);
            unset($data['title_style_bold']);
            unset($data['title2']);
            unset($data['sub_text_des']);
            unset($data['sub_text_len']);

            unset($data['dosubmit']);
            unset($data['xsjx_time_h']);
            $data['g_content']=$_POST['g_content'];
            $data['xsjx_time']=$xsjx_time;
            if($data['maxqishu'] > 65535){
                _message("最大期数不能超过65535期");
            }
            if($data['maxqishu'] < $shopinfo['qishu']){
                _message("最大期数不能小于当前期数！");
            }
            $res=$this->model->cloud_goods_save($data,"gid='".$shopid."'");
            if($res){
                _message("修改成功!");
            }else{
                _message("修改失败!");
            }
        }
        $cateinfo=$this->cate->get_cate_one("`cateid` = '".$shopinfo['g_cateid']."'");
        $Brand=$this->model->get_brand("concat(',',`cateid`,',') LIKE '%,".$shopinfo['g_cateid'].",%'");
        foreach($Brand as $row){
            $BrandList[$row['id']]=$row;
        }
        $cate=$this->cate->get_cate_list("`model`=1","*","`parentid` ASC,`cateid` ASC");
        foreach($cate as $row){
            $categorys[$row['cateid']]=$row;
        }
        $tree=System::load_sys_class('tree');
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;';
        $categoryshtml="<option value='\$cateid'>\$spacer\$name</option>";
        $tree->init($categorys);
        $categoryshtml=$tree->get_tree(0,$categoryshtml);
        $categoryshtml.='<option value="'.$cateinfo['cateid'].'" selected="true">'.$cateinfo['name'].'</option>';

        if($shopinfo['g_title_style']){
            $shopinfo['g_title_style']=unserialize($shopinfo['g_title_style']);
        }else{
            $title_color='';
            $title_bold = '';
        }
        $shopinfo['picarr'] = unserialize($shopinfo['g_picarr']);
        if($shopinfo['xsjx_time']){
            $shopinfo['xsjx_time_1'] = date("Y-m-d",$shopinfo['xsjx_time']);
            $shopinfo['xsjx_time_h'] = $shopinfo['xsjx_time'] - strtotime($shopinfo['xsjx_time_1']);
            $shopinfo['xsjx_time'] = $shopinfo['xsjx_time_1'];
        }else{
            $shopinfo['xsjx_time']='';
            $shopinfo['xsjx_time_h']=79200;
        }
        $this->view->data("shopid",$shopid);
        $this->view->data("categoryshtml",$categoryshtml);
        $this->view->data("BrandList",$BrandList);
        $this->view->tpl('goods.cloud_goods_edit')->data("shopinfo",$shopinfo)->data("ments",$this->cgoods_ment);
    }


    //添加商品
    public function cloud_goods_add(){

        if(isset($_POST['dosubmit'])){

            $data = _post();
           
            $g_style=_post("g_style");
            $data['g_type']=3;
            $data['g_style']=@array_sum($g_style);
            $data['g_title_style']=serialize(array("color"=>$data['title_style_color'],"bold"=>$data['title_style_bold']));
            if(!$data['g_cateid'])_message("请选择栏目");
            if(!$data['g_brandid'])_message("请选择品牌");
            if(!$data['g_title'])_message("标题不能为空");
            if(!$data['g_thumb'])_message("缩略图不能为空");
            $g_picarr=array();
            $g_picarr=_post("uppicarr");
            $g_picarr = serialize($g_picarr);
            $data['g_picarr']=$g_picarr;
            unset($data['uppicarr']);
            if($data['xsjx_time'] != ''){
                $xsjx_time = strtotime($data['xsjx_time']) ? strtotime($data['xsjx_time']) : time();
                $xsjx_time_h = intval($data['xsjx_time_h']) ? $data['xsjx_time_h'] : 36000;
                $xsjx_time += $xsjx_time_h;
            }else{
                $xsjx_time = '0';
            }
            $info=$this->AdminInfo;
            $data['g_add_uid']=$info['mid'];
            $data['g_add_time']=time();
            $data['g_status']=1;
            unset($data['title_style_color']);
            unset($data['title_style_bold']);
            unset($data['title2']);
            unset($data['sub_text_des']);
            unset($data['sub_text_len']);

            unset($data['dosubmit']);
            unset($data['xsjx_time_h']);
            $data['xsjx_time']=$xsjx_time;
            if($data['maxqishu'] > 65535){
                _message("最大期数不能超过65535期");
            }

            if($data['g_money'] < $data['yunjiage']) _message("商品价格不能小于购买价格");
            $zongrenshu = ceil($data['g_money']/$data['yunjiage']);
            $data['zongrenshu']=$zongrenshu;
            $data['shenyurenshu']=$zongrenshu;
            $codes_len = ceil($zongrenshu/3000);
            if($zongrenshu<=0){
                _message("云购价格不正确");
            }
            $data['g_content']=$_POST['g_content'];

            
            $res=$this->model->cloud_goods_add($data);
            if($res){
                $t=$this->order->goods_table($res,$data['zongrenshu']);
                _message("商品添加成功!");
            }else{
                _message("商品添加失败!");
            }

            header("Cache-control: private");
        }
        $cateid=intval($this->segment(4));
        //$categorys=$this->db->GetList("SELECT * FROM `@#_cate` WHERE `model` = '1' order by `parentid` ASC,`cateid` ASC",array('key'=>'cateid'));
        $cate=$this->cate->get_cate_list("`model` = '1'","","`parentid` ASC,`cateid` ASC");
        foreach($cate as $row){
            $categorys[$row['cateid']]=$row;
        }
        $tree=System::load_sys_class('tree');
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;';
        $categoryshtml="<option value='\$cateid'>\$spacer\$name</option>";
        $tree->init($categorys);
        $categoryshtml=$tree->get_tree(0,$categoryshtml);
        $categoryshtml='<option value="0">≡ 请选择栏目 ≡</option>'.$categoryshtml;
        if($cateid){
            //$cateinfo=$this->db->GetOne("SELECT * FROM `@#_cate` WHERE `cateid` = '$cateid' LIMIT 1");
            $cateinfo=$this->cate->get_cate_one("`cateid` = '".$cateid."'");
            if(!$cateinfo)_message("参数不正确,没有这个栏目",G_ADMIN_PATH.'/'.ROUTE_C.'/addarticle');
            $categoryshtml.='<option value="'.$cateinfo['cateid'].'" selected="true">'.$cateinfo['name'].'</option>';
            $Brand=$this->model->get_brand("`cateid`='".$cateid."'");
        }else{
            $Brand=$this->model->get_brand();
        }
        foreach($Brand as $row){
            $BrandList[$row['id']]=$row;
        }
        $this->view->data("categoryshtml",$categoryshtml);
        $this->view->data("categoryshtml",$categoryshtml);
        $this->view->tpl('goods.cloud_goods_add')->data("BrandList",$BrandList)->data("ments",$this->cgoods_ment);

    }

    //  云购END
	
	/* 单个商品的购买详细 */
	public function goods_go_one(){
		$gid = intval($this->segment(4));
		$ginfo=$this->model->cloud_qishu_one("`id`='".$gid."'");
		if(!$ginfo)_message("没有找到这个商品");
        $info=$this->model->cloud_goods_one("a.`gid`='".$ginfo['gid']."'");
		$selectwords="`ogid` = '$gid'  order by  `otime` DESC";		
		$total  = $this->order->ready_order_num($selectwords,1);
		$page=System::load_sys_class('page');
		$page->config($total,20);     	
        $go_list = $this->order->ready_order($selectwords,1,'','',$page->setlimit(1));
		
        $this->view->data("info",$info);
        $this->view->data("ginfo",$ginfo);
        $this->view->data("go_list",$go_list);
        $this->view->data("total",$total);
        $this->view->data("page",$page->show("one",true));
		$this->view->tpl('goods.go_list');
	}
	/* 手动揭晓	*/
	public function goods_one_ok(){
	
		$gid = intval($this->segment(4));
    $ginfo=$this->model->cloud_qishu_one("`id`='".$gid."'");
  
		if(!$ginfo)_message("没有找到这个商品");
		$jinri_time = time();		
		if($ginfo['xsjx_time']!='0')_message("限时揭晓商品不能手动揭晓");
		if($ginfo['shenyurenshu']!='0')_message("该商品还有剩余人数,不能手动揭晓！");
		
		if($ginfo['shenyurenshu']=='0' && (empty($ginfo['q_uid']) || $ginfo['q_uid']=='')){	
			//判断是否有外部开奖数据插件
			$loconfig = System::load_sys_config("lotteryway");
			$json_shop =  json_encode($ginfo);
			$json_shop =  base64_encode($json_shop);                
			$post_arr= array("shop"=>$json_shop,"lotteryway"=>$loconfig['lotteryway']['opennow']);       
			_g_triggerRequest(WEB_PATH.'/plugin-CloudWay-optway',false,$post_arr);
       sleep(1);			
			
			$ginfo=$this->model->cloud_qishu_one("`id`='".$gid."'");	
			if(!$ginfo['q_uid']){				
				_message("揭晓失败!");	
			}else{	
				_message("揭晓成功!");
			}
		}
			_message("揭晓成功!");
	}
    //ajax 删除商品
    public function goods_del(){
        $shopid=intval($this->segment(4));
        $res=$this->model->goods_del($shopid);
        if($res){
            _message("删除成功!",WEB_PATH.'/'.ROUTE_M.'/goods/goods_list/');
            //echo WEB_PATH.'/'.ROUTE_M.'/goods/goods_list/';
        }else{
            _message("删除失败!");
            //echo "no";
        }
        exit;
    }
	//商品回收站
	public function goods_del_list(){
		$this->ment=array(
						array("lists","返回商品列表",ROUTE_M.'/'.ROUTE_C."/goods_list"),
						array("add","添加商品",ROUTE_M.'/'.ROUTE_C."/goods_add"),
		);
		$num=20;
		$total=$this->db->GetCount("SELECT COUNT(*) FROM `@#_shoplist_del` WHERE 1"); 
		$page=System::load_sys_class('page');
		if(isset($_GET['p'])){$pagenum=$_GET['p'];}else{$pagenum=1;}
		$page->config($total,$num,$pagenum,"0");
		$shoplist=$this->db->GetPage("SELECT * FROM `@#_shoplist_del` WHERE 1",array("num"=>$num,"page"=>$pagenum,"type"=>1,"cache"=>0));
		
		include $this->tpl(ROUTE_M,'shop.del');
	}
	//ajax 删除商品
	public function cloud_goods_del(){
		$shopid=intval($this->segment(4));
        $gid=intval($this->segment(5));
        $res=$this->model->del_cloud_qishu($shopid);
		if($res){
			echo WEB_PATH.'/'.ROUTE_M.'/goods/qishu_list/'.$gid;
		}else{
			echo "no";
		}
		exit;
	}
	
	//ajax 删除一个商品商品所有期数
	public function cloud_goods_dels(){
		$shopid=intval($this->segment(4));
        $res=$this->model->del_cloud_list($shopid);
        if($res){
            _message("删除产品成功!",WEB_PATH.'/'.ROUTE_M.'/goods/cloud_goods_lists/');
        }else{
            _message("删除产品失败!");
        }
	}
    public function qishu_list(){
        $shopid=intval($this->segment(4));
        $info = $this->model->cloud_goods_one("a.`gid` = '".$shopid."' ");
        $num=20;
        $total=$this->model->cloud_goods_num("b.`gid` = '".$shopid."'");
        $page=System::load_sys_class('page');
        $page->config($total,$num);
        $qishu=$this->model->cloud_goods_list("b.`gid` = '".$shopid."'","",$page->setlimit(1));
        foreach($qishu as &$row){
            $row['cate_name']=$this->cate->get_cate_name("cateid=".$row['g_cateid']);
        }
        $this->view->data("info",$info);
        $this->view->data("qishu",$qishu);
        $this->view->data("total",$total);
        $this->view->data("page",$page->show("one",true));
        $this->view->tpl('goods.qishu');
    }

	/*
	*	商品排序
	*/	
	public function goods_listorder(){
        $data=_post('listorders');
        $res=$this->model->goods_sort($data);
		if($res){
			_message("排序更新成功",WEB_PATH.'/'.ROUTE_M."/goods/goods_list");
		}else{
			_message("请排序");
		}		
	}//
	/*
	*	商品最大期数修改
	*	ajax
	*/	
	public function goods_max_qishu(){
		if($this->segment(4)!='dosubmit'){
			echo json_encode(array("meg"=>"not key","err"=>"-1"));			
			exit;
		}
		if(!isset($_POST['gid']) || !isset($_POST['qishu']) || !isset($_POST['money']) || !isset($_POST['onemoney'])){
			echo json_encode(array("meg"=>"参数不正确!","err"=>"-1"));
			exit;
		}
				
		$gid = abs(intval($_POST['gid']));
		$qishu = abs(intval($_POST['qishu']));
		$money = abs(intval($_POST['money']));
		$onemoney = abs(intval($_POST['onemoney']));
		
		
		if(!$gid || !$qishu || !$money || !$onemoney){
			echo json_encode(array("meg"=>"参数不正确!","err"=>"-1"));
			exit;
		}
		if($money < $onemoney){
			echo json_encode(array("meg"=>" 总价不能小于云购价!","err"=>"-1"));
			exit;
		}
		
		$info = $this->db->GetOne("SELECT * FROM `@#_shoplist` where `id` = '$gid' and `q_end_time` is not null");
		if(!$info || ($info['qishu']!=$info['maxqishu'])){
			echo json_encode(array("meg"=>"没有该商品或还有剩余期数!","err"=>"-1"));
			exit;
		}
		if($qishu <= $info['qishu']){
			echo json_encode(array("meg"=>"期数不正确!","err"=>"-1"));
			exit;
		}		
		$ret = $this->db->Query("UPDATE `@#_shoplist` SET `maxqishu` = '$qishu' where `sid` = '$info[sid]'");
		if(!$ret){
			echo json_encode(array("meg"=>"期数更新失败!","err"=>"-1"));
			exit;
		}
		$info['maxqishu'] = $qishu;
		$info['money'] = $money;
		$info['yunjiage'] = $onemoney;
		$info['zongrenshu'] = ceil($money/$onemoney);		
		System::load_app_fun("content");
		$ret = content_add_shop_install($info);
		if($ret){
			echo json_encode(array("meg"=>"新建商品成功!","err"=>"1"));
			exit;
		}else{
			echo json_encode(array("meg"=>"更新失败失败!","err"=>"-1"));
			exit;		
		}		
	
		
	}//
	
	/**
	*	重置商品价格
	**/
	public function goods_set_money(){		

		$ments=array(
						array("lists","商品管理",ROUTE_M.'/'.ROUTE_C."/goods_list"),
						array("add","添加商品",ROUTE_M.'/'.ROUTE_C."/goods_add"),
						array("renqi","人气商品",ROUTE_M.'/'.ROUTE_C."/goods_list/renqi"),
						array("xsjx","限时揭晓商品",ROUTE_M.'/'.ROUTE_C."/goods_list/xianshi"),
						array("qishu","期数倒序",ROUTE_M.'/'.ROUTE_C."/goods_list/qishu"),
						array("danjia","单价倒序",ROUTE_M.'/'.ROUTE_C."/goods_list/danjia"),
						array("money","商品价格倒序",ROUTE_M.'/'.ROUTE_C."/goods_list/money"),
						array("money","已揭晓",ROUTE_M.'/'.ROUTE_C."/goods_list/jiexiaook"),
						array("money","<font color='#f00'>期数已满商品</font>",ROUTE_M.'/'.ROUTE_C."/goods_list/maxqishu"),
		);
		$gid = abs(intval($this->segment(4)));
		$shopinfo = $this->model->cloud_goods_one("a.`gid` = '$gid'");
		if(!$shopinfo){
			_message("参数不正确!");exit;
		}
		if($shopinfo['canyurenshu']>0){
			_message("商品正在进行中不能重置价格!");exit;		
		}
        if(!empty($shopinfo['q_uid'])){
            _message("该商品已经开奖!");exit;
        }
		
		if(isset($_POST['g_money']) || isset($_POST['price'])){
            $data=_post();
            $data['g_money'] = abs(intval($data['g_money']));
            $data['price'] = abs(intval($data['price']));
			
	
			if($data['price'] > $data['g_money']){
				_message("单人次购买价格不能大于商品总价格!");
			}
			if(!$data['price'] || !$data['g_money']){
				_message("价格填写错误!");
			}
			if(($data['price'] == $shopinfo['price']) && ($data['g_money'] == $shopinfo['g_money'])){
				_message("价格没有改变!");
			}
			$zongrenshu = ceil($data['g_money']/$data['price']);
            $data['zongrenshu']=$zongrenshu;
            $data['shenyurenshu']=$zongrenshu;
			$res=$this->model->reset_money($data,$gid);

			if($res){
				_message("更新成功!");
			}else{
				_message("更新失败!");				
			}
		}
        $this->view->data("shopinfo",$shopinfo);
	    $this->view->data("ments",$ments);
		$this->view->tpl('goods.cloud_set_money');
	}

    public function brand_lists(){
        $page=System::load_sys_class("page");
        $num=20;
        $total=$this->model->get_brand_num();
        if(isset($_GET['p'])){
            $pagenum=intval($_GET['p']);
        }else{
            $pagenum=1;
        }
        $page->config($total,$num);
        $brands=$this->model->get_brand("","","`sort` DESC",$page->setlimit(1));
        $cate=$this->cate->get_cate_list("","","`parentid` ASC,`cateid` ASC");
        foreach($cate as $row){
            $categorys[$row['cateid']]=$row;
        }
        $this->view->data("brands",$brands);
        $this->view->data("total",$total);
        $this->view->data("categorys",$categorys);
        $this->view->data("page",$page->show('one',true));
        $this->view->tpl('goods.brand_list')->data("ments",$this->brand_ment);
    }

    //品牌管理入库
    public function brand_add(){
        $cate=$this->cate->get_cate_list("`model` = '1'","*","`parentid` ASC,`sort` ASC");
        foreach($cate as $v){
            $categorys[$v['cateid']]=$v;
        }
        $tree=System::load_sys_class('tree');
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;';
        $categoryshtml="<option value='\$cateid'>\$spacer\$name</option>";
        $tree->init($categorys);
        $categoryshtml=$tree->get_tree(0,$categoryshtml);
        if(isset($_POST['dosubmit'])){

            $data=_post();
            $cateid=_post("cateid");
            if(!isset($cateid)){
                _message("请选择所属栏目");
            }
            if(!isset($data['name'])){
                _message("请填写品牌名称");
            }
            $cateidsty = '';
            foreach($cateid as $v){
                $cateidsty .= intval($v).",";
            }
            $cateidsty = trim($cateidsty,",");
            $data['sort']=intval($data['sort']) ? intval($data['sort']) : 1;
            $data['cateid']=$cateidsty;
            unset($data['dosubmit']);
            $res=$this->model->brand_add($data);
            if($res){
                _message("操作成功!",WEB_PATH.'/'.ROUTE_M.'/goods/brand_lists');
            }else{
                _message("操作失败!");
            }
        }
        $this->view->tpl('goods.brand_edit')->data("categoryshtml",$categoryshtml)->data("ments",$this->brand_ment);
    }
    //修改品牌
    public function brand_edit(){

        $brandid=intval($this->segment(4));

        $brands=$this->db->Getone("select * from `@#_brand` where id='$brandid'");
        if(!$brands)_message("参数错误!");
        $categorys=$this->db->GetList("SELECT * FROM `@#_cate` WHERE `model` = '1' order by `parentid` ASC,`cateid` ASC",array('key'=>'cateid'));
        $tree=System::load_sys_class('tree');
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;';
        $categoryshtml="<option value='\$cateid'>\$spacer\$name</option>";
        $tree->init($categorys);
        $categoryshtml=$tree->get_tree(0,$categoryshtml);
        if(isset($_POST['dosubmit'])){
            $data=_post();
            $cateid=_post("cateid");

            if(!isset($cateid)){
                _message("请选择所属栏目");
            }
            if(!isset($data['name'])){
                _message("请填写品牌名称");
            }
            $cateidsty = '';
            foreach($cateid as $v){
                $cateidsty .= intval($v).",";
            }
            $cateidsty = trim($cateidsty,",");
            $data['sort']=intval($data['sort']) ? intval($data['sort']) : 1;
            $data['cateid']=$cateidsty;
            unset($data['dosubmit']);

            $res=$this->model->brand_save("`id`='".$brandid."'",$data);

            if($res){
                _message("操作成功!",WEB_PATH.'/'.ROUTE_M.'/goods/brand_lists');
            }else{
                _message("操作失败!");
            }
        }
        $cateid_arr =  explode(",",$brands['cateid']);
        foreach($cateid_arr as $v){
            $tmp['cateid']=$v;
            $tmp['cate_name']=$this->cate->get_cate_name("`cateid`='".$v."'");
            $cate[]=$tmp;
        }
        $this->view->data("brands",$brands);
        $this->view->data("cateid_arr",$cate);
        $this->view->tpl('goods.brand_edit')->data("categoryshtml",$categoryshtml)->data("ments",$this->brand_ment);
    }

    //删除品牌管理
    public function brand_del(){
        $brandid=intval($this->segment(4));
        if(!$brandid)_message("参数错误!");
        $res=$this->model->brand_del("id='".$brandid."'");
        if($res){
            _message("操作成功!",WEB_PATH.'/'.ROUTE_M.'/goods/brand_lists');
        }else{
            _message("操作失败!");
        }
    }
    //品牌排序
    public function brand_listorder(){
        $data=_post("listorders");
        $res=$this->model->brand_sort($data);
        if($res){
            _message("排序更新成功",G_ADMIN_PATH.'/'.ROUTE_C."/brand_lists");
        }else{
            _message("操作失败！");
        }
    }
	
}//
?>