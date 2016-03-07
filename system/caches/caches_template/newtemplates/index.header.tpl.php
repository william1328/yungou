<?php defined('G_IN_SYSTEM')or exit('No permission resources.'); ?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="Shortcut Icon" href="<?php echo G_WEB_PATH; ?>/favicon.ico">
<?php echo seo(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo G_TEMPLATES_STYLE; ?>/css/index.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo G_TEMPLATES_STYLE; ?>/css/Comm.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo G_TEMPLATES_STYLE; ?>/css/color.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo G_TEMPLATES_STYLE; ?>/css/css.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo G_TEMPLATES_STYLE; ?>/css/myCart.css"/>
<?php echo css(); ?>
<script type="text/javascript" src="<?php echo G_GLOBAL_STYLE; ?>/global/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="<?php echo G_TEMPLATES_JS; ?>/cloud-zoom.min.js"></script>
<script type="text/javascript" src="<?php echo G_TEMPLATES_JS; ?>/jquery.cookie.js"></script>
<?php echo js(); ?>
<?php echo GetConfiga('verify'); ?>
</head>
<body>
<div id="loadingtime"></div>
<div class="header">
	<div class="site_top">
		<div class="head_top w1200">
			<ul class="collect fr">            
                <?php echo include 'D:\phpStudy2014\WWW\yg\system\caches\caches_codes/tag.onlinecustomer.php'; ?>             				
				<li class="fr"><a href="<?php echo WEB_PATH; ?>/article-1.html">帮助</a><span>|</span></li>           
				<li class="fr"><a href="<?php echo WEB_PATH; ?>/member/account/userrecharge">快速充值</a><span>|</span></li>
				<li class="fr yu_ff">
					<a href="">我的<?php echo _cfg("web_name_two"); ?></a><span>|</span>
					<div class="h_1yyg_eject">
						<dl>							
								<?php if(is_array(login('list'))) foreach(login('list') AS $title=>$url): ?>	
									<dd><a href="<?php echo $url; ?>"><?php echo $title; ?></a></dd>
								<?php endforeach; ?>
						</dl>
					</div>					
				</li>						
				<?php if($user=login('bool')): ?>				
				<li class="fr">欢迎您:<a href="<?php echo $user['url']; ?>"><?php echo $user['name']; ?></a><span>|</span><a href="<?php echo WEB_PATH; ?>/member/user/logout">[退出]</a><span>|</span></li>													
				<?php  else: ?>
					<?php if(is_array(login('view'))) foreach(login('view') AS $title=>$url): ?>			
						<li class="fr"><a href="<?php echo $url; ?>"><?php echo $title; ?></a><span>|</span></li>
					<?php endforeach; ?>
				<?php endif; ?>					
			</ul>
			<ul class="collect fl">
				<li class="fl"><a href="javascript:;" onclick=AddFavorite(window.location,document.title)>收藏</a><span>|</span></li>
				<li class="fl mobile" style="position:relative;"><a href="">手机<?php echo _cfg("web_name_two"); ?></a><s></s><span></span>
					<div class="h_mobile">
						<dl>
							<li class="fl"><a href="">手机<?php echo _cfg("web_name_two"); ?></a><s></s><span></span></li>
						</dl>
                        <?php if(readapp(1)): ?>
                        <?php if(is_array(readapp(1))) foreach(readapp(1) AS $weixin): ?> 
                        	<img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $weixin['img']; ?>" width="75" height="75">
						    <span>下载客户端</span>
                        <?php endforeach; ?>    
                        <?php endif; ?>    
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div class="head_mid">
		<div class="head_mid_bg w1200">
			<h1 class="logo_yungou fl">
				<a class="logo_1yyg_img" href="<?php echo WEB_PATH; ?>" title="云购CMS — 惊喜无线">
					<img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo Getlogo(); ?>" />
				</a>
			</h1>
			<div class="head_search b_red fl">
				<input type="text" id="txtSearch" class="init" placeholder='输入"小米手机"试一试'/>
				<a class="search_submit" id="butSearch" href="javascript:;" >
					<i class="ico_search"></i>
				</a>
			</div>
					
			<div class="m-joinNum r">
				<a href="<?php echo WEB_PATH; ?>/index/index/cloud_gorecord" target="_blank">
					<span class="qian text">已有</span>
                    <?php if(is_array(go_count_renci())) foreach(go_count_renci() AS $v): ?>
					<span class="tnum" id="spBuyCount"><?php echo $v; ?></span>
                    <?php endforeach; ?>
					<span class="hou text">人次参加></span>
				</a>
			</div>
		</div>
	</div>
</div>
<!--导航 header_nav 开始-->
<div class="head_nav">
	<div class="nav_center bg_red b_red w1200">
		<div class="m_menu br_red">
			<div class="m_menu_all">
                 <h3><a class="c_red" href="<?php echo WEB_PATH; ?>/cgoods_list/">全部商品分类</a></h3>
             </div>
			 <div class="m_all_sort b_gray" id="m_all_sort">
				<ul>
                    <?php if(is_array(GetCate('0',8,5))) foreach(GetCate('0',8,5) AS $two_cate): ?>
                    <li>
                     <a href="<?php echo WEB_PATH; ?>/cgoods_list/<?php echo $two_cate['cateid']; ?>_0_0" target="_blank">
                    <?php if(_unser($two_cate['info'],'thumb')): ?>
                        <img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo _unser($two_cate['info'],'thumb'); ?>" width="44"height="49">
                    <?php  else: ?>    
                        <img class="mt10 mb10" src="<?php echo G_UPLOAD_PATH; ?>/photo/pplm.png" width="44"height="51"></a>
                    <?php endif; ?> 
                     </a>  
                        <a href="<?php echo WEB_PATH; ?>/cgoods_list/<?php echo $two_cate['cateid']; ?>_0_0" target="_blank"><?php echo $two_cate['name']; ?></a></li>
                    <?php endforeach; ?>

				</ul>
				<a class="more" href="<?php echo WEB_PATH; ?>/cgoods_list/">查看更多</a>
			 </div>
		</div>
		<ul class="nav_list">
		    <li class="sort-all-shouye" ><a href="<?php echo G_WEB_PATH; ?>">首页</a></li>
			<?php if(is_array(Getheader('index','classstyle'))) foreach(Getheader('index','classstyle') AS $indexnav): ?>
			<li class="sort-all <?php echo $indexnav['focus_urlstyle']; ?>" ><a href="<?php echo $indexnav['url']; ?>" target="<?php echo $indexnav['url_target']; ?>"><?php echo $indexnav['name']; ?></a></li>
			<?php endforeach; ?>		
		</ul>	
		<div class="mini_mycart" id="sCart">
			<a href="<?php echo WEB_PATH; ?>/member/cart/cartlist" class="cart c_red"  target="_blank" id="sCartNavi">
				<s></s>购物车(<span id="sCartTotal" class="c_red">0</span>)
			</a>
		</div>
	</div>
</div>
<div class="clear"></div>
<style>
.fixedNav{
	position:fixed;
	top:0px;
	left:0px;
	width:100%;
	z-index:100000;
	_position:absolute;
	_top:expression(eval(document.documentElement.scrollTop));
}
</style>
<!--导航 header_nav 结束-->
<script>

$(function(){
  $(window).scroll(function() {	
 	<?php if(!isset($isindex) || $isindex!='Y'): ?>	
		if($(window).scrollTop()>=130&&$(window).scrollTop()<=560){
			$(".head_nav").addClass("fixedNav");	
			$("#m_all_sort").fadeOut();
		}else if($(window).scrollTop()>560){
			$(".head_nav").addClass("fixedNav");
			$("#m_all_sort").fadeOut();
	} else if($(window).scrollTop()<130){
			$(".head_nav").removeClass("fixedNav");
	}
    <?php  else: ?> 
		if($(window).scrollTop()>=520){
			$(".head_nav").addClass("fixedNav");
			$("#m_all_sort").hide();
	     $(".m_menu_all").mouseenter(function(){
			    $(".m_all_sort").show();
	     })
		 $(".m_menu_all").mouseleave(function(){
			    $(".m_all_sort").hide();
	     })
		 $(".m_all_sort").mouseenter(function(){
			    $(this).show();
	     })
		 $(".m_all_sort").mouseleave(function(){
			    $(this).hide();
	     })
		}	else if($(window).scrollTop()<520){
			$(".head_nav").removeClass("fixedNav");
			$("#m_all_sort").show();
			  $(".m_menu_all").mouseenter(function(){
				$("#m_all_sort").show();
			  })
			$(".m_menu_all").mouseleave(function(){
				$("#m_all_sort").show();
			  })
			$(".m_all_sort").mouseenter(function(){
			    $(this).show();
	     })
			$(".m_all_sort").mouseleave(function(){
			    $(this).show();
	     })
		}	
    <?php endif; ?>
  });
});

	$(document).ready(function(){
		$.get("<?php echo WEB_PATH; ?>/member/cart/getnumber/"+new Date().getTime(),function(data){
			$("#sCartTotal").text(data);
		});
	});

document.onkeydown=function(event)
{ 
	e = event ? event :(window.event ? window.event : null);
	ss= document.getElementById('txtSearch').value;
	if(e.keyCode==13 && ss!=""){
	 window.location.href="<?php echo WEB_PATH; ?>/soso="+$("#txtSearch").val();
	}
}

$(function(){
	$("#txtSearch").focus(function(){
		$("#txtSearch").css({background:"#fff"});
		$(this).attr("placeholder","");
	});
	$("#txtSearch").blur(function(){
		$("#txtSearch").css({background:"#FFF"});
		$(this).attr("placeholder","输入'小米手机'试一试");	
	});
	$("#butSearch").click(function(){
		var val1="小米手机"
	    if($("#txtSearch").val()==""){
			window.location.href="<?php echo WEB_PATH; ?>/soso="+val1;
		}else
		if($("#txtSearch").val()==$("#txtSearch").val()){
			window.location.href="<?php echo WEB_PATH; ?>/soso="+$("#txtSearch").val();
		}
	});
});

	$(".yu_ff").mouseover(function(){
	  $(".h_1yyg_eject").show();
	})
	$(".yu_ff").mouseout(function(){
	  $(".h_1yyg_eject").hide();
	})

	<?php if(!isset($isindex) || $isindex!='Y'): ?>
	     $("#m_all_sort").hide();
	     $(".m_menu_all").mouseenter(function(){
			    $(".m_all_sort").show();
	     })
		 $(".m_menu_all").mouseleave(function(){
			    $(".m_all_sort").hide();
	     })
		 $(".m_all_sort").mouseenter(function(){
			    $(this).show();
	     })
		 $(".m_all_sort").mouseleave(function(){
			    $(this).hide();
	     })
	<?php endif; ?>
	
	//收藏
	function AddFavorite(sURL, sTitle){
		try
		{
			window.external.addFavorite(sURL, sTitle);
		}
		catch (e)
		{
			try
			{
				window.sidebar.addPanel(sTitle, sURL, "");
			}
			catch (e)
			{
				alert("您可以通过快捷键Ctrl+D进行添加");
			}
		}
	}
	
	$(".mobile").mouseover(function(){
		$(".h_mobile").show();
		$(".h_mobile  s").css("background","../images/headbg11.png").css("background-position","5px -64px");
	})
	$(".h_mobile").mouseout(function(){
		$(".h_mobile").hide();
	})
</script>
