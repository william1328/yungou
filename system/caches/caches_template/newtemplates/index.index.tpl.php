<?php defined('G_IN_SYSTEM')or exit('No permission resources.'); ?><?php echo js("jquery.webox"); ?>
<?php echo js("jquery.cartlist"); ?>
<?php echo js("koala.min.1.5"); ?>
<?php include self::includes("index.header"); ?>
<!--banner 开始-->
<div class="banner w1200">
	<div class="banner_box b_gray" style="border-top:0;">
		<div id="fsD1" class="focus">
			<div id="D1pic1" class="fPic">
			<?php if(is_array(Getslide(5))) foreach(Getslide(5) AS $slide): ?>
				<div class="fcon" style="display: none;">
					<a  href="<?php echo $slide['link']; ?>" target="_blank"><img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $slide['img']; ?>"></a>
				</div>
			<?php endforeach; ?>
			</div>
			<div class="fbg">
			<div class="D1fBt" id="D1fBt">
			<?php if(is_array(Getslide(5))) foreach(Getslide(5) AS $slide): ?>
				<a href="javascript:void(0)" hidefocus="true" target="_self" class=""></a>
			<?php endforeach; ?>
			</div>
			</div>
			<span class="prev"></span>
			<span class="next"></span>
		</div>
		<div class="banner_boxR fr">
			<div class="m_login bb_gray">
				<a href="<?php echo WEB_PATH; ?>/article-1.html" target="_blank"><img src="<?php echo G_TEMPLATES_IMAGE; ?>/index-come.gif" width="208" height="108"></a>
			</div>
			<div class="m_app bb_gray">
				<ul>
					<li><a href=""><i class="i1"></i>诚信网站</a></li>
					<li><a href=""><i class="i2"></i>可信网站</a></li>
					<li><a href=""><i class="i3"></i>电商诚信</a></li>
					<li><a href=""><i class="i4"></i>安信宝</a></li>
					<li><a href=""><i class="i5"></i>监督管理局</a></li>
					<li><a href=""><i class="i6"></i>更多</a></li>
				</ul>
			</div>
			<div class="m_news">
				<div class="m_newsT bb_gray">
					<p class="notice c_red">官方公告</p>
				</div>
				<div class="m_newsM">
					<div class="m_newsML">
                        <?php if(is_array($notice)) foreach($notice AS $v): ?>
                        <a href="<?php echo WEB_PATH; ?>/article-<?php echo $v['id']; ?>.html" target="_blank"><?php echo $v['title']; ?></a>
                        <?php endforeach; ?>
					</div>
					<div class="m_newsMR">
                        <?php if(is_array($notice)) foreach($notice AS $v): ?>
                        <p><?php echo microt($v['posttime'],'s'); ?></p>
                        <?php endforeach; ?>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--banner 结束-->
<div class="clear"></div>
<!--prin_pp 开始-->
<div class="prin_pp w1222"  style="margin-top:10px;">
	<ul id="prin_pp">
		<?php $mod_common_cloud_goods = System::load_app_model('cloud_goods','common');$r_cgoodslisted = $mod_common_cloud_goods->cloud_goodsed(3); ?>
		<?php if(is_array($r_cgoodslisted)) foreach($r_cgoodslisted AS $cgoodslisted): ?>
		<li class="b_gray" >
			<div class="print">
				<p>用户：<a href="<?php echo WEB_PATH; ?>/uname/<?php echo idjia($cgoodslisted['q_uid']); ?>"  target="_blank" class="c_red"><?php echo Getusername($cgoodslisted['q_uid']); ?></a></p>
				<p>花费 <span class="c_red"><?php echo go_count_record($cgoodslisted['id'],$cgoodslisted['q_uid'],'m'); ?></span> <?php echo L('cgoods.currency'); ?>，获得了</p>
				<a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $cgoodslisted['id']; ?>" target="_blank"><p class="c_black"><?php echo $cgoodslisted['g_title']; ?></p></a>
				<p class="mt30">回报率：<span class="c_red t18"><?php echo go_count_record($cgoodslisted['id'],$cgoodslisted['q_uid'],'l'); ?></span> 倍</p>
			</div>
			<div class="w_goods_pic">
				<a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $cgoodslisted['id']; ?>" target="_blank"><img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $cgoodslisted['g_thumb']; ?>"></a>
			</div>
		</li>
		<?php endforeach; ?>
<!---正在倒计时--->					
	</ul>
<script>
 var APP = {
      WEB_PATH : '<?php echo WEB_PATH; ?>',
      G_WEB_PATH : '<?php echo G_WEB_PATH; ?>',
      G_PARAM_URL : '<?php echo G_PARAM_URL; ?>'
    };		
</script>		
<script type="text/javascript" src="<?php echo G_GLOBAL_STYLE; ?>/global/js/jquery.BusyTime.js"></script>	
<script type="text/javascript" src="<?php echo G_GLOBAL_STYLE; ?>/global/js/jquery.cmsapi.js"></script>	
<style>
.busytime i{
	color: #fff;
	font-size: 15px;
	line-height: 30px;
	background: #ca1b38;
	border-radius: 3px;
	padding: 5px;
}
</style>
<script>
$.YunCmsApi.Loop({
	"timer"	  : 15000,
	"callback": function(data){
		var path='<?php echo WEB_PATH; ?>';
		var html= '';	
		html+= '<li class="b_gray" id="timeloop'+data.id+'">';		
		html+= '<div class="print" >';		
		html+= '<p>用户：<a  href="'+path+'/cgdataserver/'+data.id+'" target="_blank" class="c_red">马上揭晓</a></p>';		
		html+= '<p>花费：<a  href="'+path+'/cgdataserver/'+data.id+'" target="_blank" class="c_red">马上揭晓</a></p>';	
		html+= '<a href="'+path+'/cgdataserver/'+data.id+'" target="_blank"><p class="c_black">'+data.title+'</p></a>';	
		html+= '<p class="mt30">离开奖还有</p>';	
		html+= '<span class="shi"><span class="busytime" pattern="<i>mm</i>:<i>ss</i>:<i>ms</i>" time="'+(new Date().getTime() + (data.times * 1000)) +'">99 : 99 : 99</span>';							
		html+= '</span></div>';	
		html+= '<div class="w_goods_pic">';	
		html+= '<a href="'+path+'/cgdataserver/'+data.id+'" target="_blank"><img src="'+data.upload+'/'+data.thumb+'"></a>';							
		html+= '</div>';			
		html+= '</li>';	
		var divl = $("#prin_pp").find('li');
		var len = divl.length;			
		if(len==3 && len  >0){
			var this_len = len - 1;
			divl.eq(this_len).remove();
		}			
		$("#prin_pp").prepend(html);
		$("#timeloop"+data.id+" .busytime").busytime({
			callback:function($dom){
				$dom.find(".shi").html('<span class="minute">正在计算,请稍后…</span>');	
				setTimeout(function(){
				$.post(path+'/index/getshop/lottery_shop_getjson/',{gid:data.id},function(info){
					var uhtml = '';		
					uhtml+= '<div class="print">';						
					uhtml+= '<p>用户：<a href="'+path+'/uname/'+(1000000000 + parseInt(info.uid))+'"  target="_blank" class="c_red">'+info.user+'</a></p>';		
					uhtml+= '<p>花费 <span class="c_red">'+info.huafei+'</span> '+info.currency+'，获得了</p>';	
					uhtml+= '<a href="'+path+'/cgdataserver/'+info.id+'" target="_blank"><p class="c_black">'+info.title+'</p></a>'	;	
					uhtml+= '<p class="mt30">回报率：<span class="c_red t18">'+info.huibaolv+'</span> 倍</p>';		
					uhtml+= '</div>';	
					uhtml+= '<div class="w_goods_pic">';	
					uhtml+= '<a href="'+path+'/cgdataserver/'+info.id+'" target="_blank"><img src="'+info.upload+'/'+info.thumb+'"></a>';
					uhtml+= '</div>';	
					$("#timeloop"+data.id).html(uhtml);					
				},'json');
				},5000);
			}
		}).start();		
		
	}
});
</script>		
<!---正在倒计时--->		
</div>
<!--prin_pp 结束-->
<div class="clear"></div>
<!--goods_hot 开始-->
<div class="goods_hot bt2_red w1200" style="margin-top:10px;">

	<div class="goods_hotL fl b_gray" style="border-top:0;margin-right:-1px;">
		<div class="title bb_gray br_gray" style="width:249px;"><p class="c_red">如何开始？</p></div>
		<ul class="step">
			<li>
				<h1 class="c_red">首先</h1>
				<p>注册账号，挑选喜欢的奖品</p>
			</li>
			<li>
				<h1 class="c_red">然后</h1>
				<p>支付<?php echo L('cgoods.currency'); ?>参与<?php echo L('html.key'); ?>，每一个<?php echo L('cgoods.currency'); ?>可参与一次<?php echo L('html.key'); ?></p>
			</li>
			<li>
				<h1 class="c_red">最后</h1>
				<p>等待开奖，系统根据规则计算出一个幸运号码，持有该号码的用户，直接获得奖品</p>
			</li>
		</ul>
		<a href="<?php echo WEB_PATH; ?>/article-1.html"class="more c_red" target="_blank">更多新手指南>></a>
		<div class="title bb_gray bt_red"><p class="c_red">哥只是传奇</p></div>
		<ul class="user">
		<?php $mod_common_cloud_goods = System::load_app_model('cloud_goods','common');$r_recordhuode = $mod_common_cloud_goods->cloud_user_recordhuode(5); ?>
		<?php if(is_array($r_recordhuode)) foreach($r_recordhuode AS $recordhuode): ?>
		<?php $mod_common_cloud_goods = System::load_app_model('cloud_goods','common');$user_cgoods = $mod_common_cloud_goods->cloud_goodsdetaila($recordhuode['ogid']); ?>
		<?php if($user_cgoods): ?>
			<li class="bb_gray">
				<p>用户： <a class="c_yellow" href="<?php echo WEB_PATH; ?>/uname/<?php echo idjia($recordhuode['ouid']); ?>" target="_blank"><?php echo get_user_key($recordhuode['ouid'],'username'); ?></a>于<?php echo microt($recordhuode['otime'],'h'); ?></p>
				<a class="fl" href="<?php echo WEB_PATH; ?>/uname/<?php echo idjia($recordhuode['ouid']); ?>" target="_blank">
                <?php if(get_user_key($recordhuode['ouid'],'img','8080')=='null'): ?>
                <img class="mt10 mb10" src="<?php echo G_UPLOAD_PATH; ?>/photo/member.jpg.8080.jpg"width="58"height="58"></a>
                <?php  else: ?>
                <img class="mt10 mb10" src="<?php echo G_UPLOAD_PATH; ?>/<?php echo Getuserimg($recordhuode['ouid']); ?>.8080.jpg" width="58"height="58"></a>
                <?php endif; ?>
				<div class="fl li_r">
					<p> <span class="c_red"><?php echo $recordhuode['onum']; ?></span> 人次夺得
					<a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $recordhuode['ogid']; ?>" target="_blank"><span class="c_red"><?php echo useri_title($recordhuode['og_title'],'g_title'); ?></span></a></p>
					<p style="margin-top:-2px;">总需：<?php echo $user_cgoods['zongrenshu']; ?> 人次</p>
				</div>
			</li>
		<?php endif; ?>
		<?php endforeach; ?>
		</ul>
		<a href="<?php echo WEB_PATH; ?>/cgoods_lottery" class="more" target="_blank">看看谁的狗屎运最好！</a>
	</div>

	<div class="goods_hotR fl">
		<div class="title bb_gray br_gray"><p>热门推荐</p></div>
		<ul class="hot_list">
		<?php $mod_common_cloud_goods = System::load_app_model('cloud_goods','common');$r_cgoods = $mod_common_cloud_goods->cloud_goodslist(8,2); ?>
		<?php if(is_array($r_cgoods)) foreach($r_cgoods AS $recommended): ?>
			<li class="list-block">
				<div class="pic"><a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $recommended['id']; ?>" target="_blank"><img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $recommended['g_thumb']; ?>"></a></div>
				<p class="name"><a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $recommended['id']; ?>" target="_blank"><?php echo $recommended['g_title']; ?></a></p>
				<p class="money"><?php echo L('cgoods.value'); ?>：<span class="rmb"><?php echo $recommended['g_money']; ?></span>  <?php echo L('cgoods.currency'); ?></p>
				<div class="Progress-bar" style="">
					<p title="已完成<?php echo percent($recommended['canyurenshu'],$recommended['zongrenshu']); ?>">
					<span style="width:<?php echo width($recommended['canyurenshu'],$recommended['zongrenshu'],212); ?>px;"></span></p>
					<ul class="Pro-bar-li">
						<li class="P-bar01"><em class="c_red"><?php echo $recommended['canyurenshu']; ?></em>已参与人次</li>
						<li class="P-bar02"><em><?php echo $recommended['zongrenshu']; ?></em>总需人次</li>
						<li class="P-bar03"><em><?php echo $recommended['zongrenshu']-$recommended['canyurenshu']; ?></em>剩余人次</li>
					</ul>
				</div>
				<div class="w-goods-ing">
					<div class="shop_buttom bg_red b_red1" style="margin:0px 10px;">
						<a href="javascript:;" target="_blank" class="Det_Shopnow" onclick="jwebox.goshopnow(<?php echo $recommended['id']; ?>,'<?php echo WEB_PATH; ?>')"><?php echo L('cgoods.go'); ?></a>
					</div>
					<div class="shop_buttom1 bg_pink b_pink c_red" style="margin:0px 10px;">
						<a class="c_red"   href="javascript:;" onclick="gcartlist.gocartlist(<?php echo $recommended['id']; ?>,'<?php echo WEB_PATH; ?>','<?php echo GetConfig('cookie_pre'); ?>')" ><?php echo L('cgoods.cartlist'); ?></a>
					</div>
				</div>

			</li>
		<?php endforeach; ?>
		</ul>
		<div class="catalog b_gray" style="border-left:0;float:left;width:950px;height:63px;margin-top:-1px;">
			<div class="fr">
			<a href="<?php echo WEB_PATH; ?>/cgoods_list/">全部</a>
			<?php if(is_array(array_reverse(GetCate('0',7,5)))) foreach(array_reverse(GetCate('0',7,5)) AS $v): ?>
				<a href="<?php echo WEB_PATH; ?>/cgoods_list/<?php echo $v['cateid']; ?>_0_0"><?php echo $v['name']; ?></a>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
<!--goods_hot 结束-->
<div class="clear"></div>
<!--即将揭晓 get_ready 开始-->
<div class="get_ready w1200" style="margin-top:10px;">
	<div class="title br_gray bl_gray bb_gray bt2_red"><p class="c_red t16">即将揭晓</p></div>
	<ul>
		<?php $mod_common_cloud_goods = System::load_app_model('cloud_goods','common');$r_cgoods = $mod_common_cloud_goods->cloud_goodslist(8,5); ?>
		<?php if(is_array($r_cgoods)) foreach($r_cgoods AS $ready): ?>
		<li class="list-block">
			<div class="pic"><a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $ready['id']; ?>"   target="_blank"><img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $ready['g_thumb']; ?>"></a></div>
			<p class="name"><a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $ready['id']; ?>"   target="_blank"><?php echo $ready['g_title']; ?></a></p>
			<p class="money"><?php echo L('cgoods.value'); ?>：<span class="rmb"><?php echo $ready['g_money']; ?></span></p>
			<div class="Progress-bar" style="">
				<p title="已完成<?php echo percent($ready['canyurenshu'],$ready['zongrenshu']); ?>">
				<span style="width:<?php echo width($ready['canyurenshu'],$ready['zongrenshu'],275); ?>px;"></span></p>
				<ul class="Pro-bar-li">
					<li class="P-bar01"><em class="c_red"><?php echo $ready['canyurenshu']; ?></em>已参与人次</li>
					<li class="P-bar02"><em><?php echo $ready['zongrenshu']; ?></em>总需人次</li>
					<li class="P-bar03"><em><?php echo $ready['zongrenshu']-$ready['canyurenshu']; ?></em>剩余人次</li>
				</ul>
			</div>
			<div class="w-goods-ing">
				<div class="shop_buttom bg_red b_red1" style="margin:0 10px;width:115px;height:30px;">
					<a href="javascript:;" style="line-height:30px;font-size:14px;"  class="Det_Shopnow" onclick="jwebox.goshopnow(<?php echo $ready['id']; ?>,'<?php echo WEB_PATH; ?>')"><?php echo L('cgoods.go'); ?></a>
				</div>
				<div class="shop_buttom1 bg_pink b_pink c_red" style="margin:0 10px;width:115px;height:30px;">
					<a class="c_red"   href="javascript:;" onclick="gcartlist.gocartlist(<?php echo $ready['id']; ?>,'<?php echo WEB_PATH; ?>','<?php echo GetConfig('cookie_pre'); ?>')" style="line-height:30px;font-size:14px;"><?php echo L('cgoods.cartlist'); ?></a>
				</div>

			</div>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<!--即将揭晓 get_ready 结束-->
<div class="clear"></div>
<!--新品上架 new_goods 开始-->
<div class="new_goods w1200" style="margin-top:10px;">
	<div class="title br_gray bl_gray bb_gray bt2_green"><p class="c_green t16">新品上架</p></div>
	<ul>
		<?php $mod_common_cloud_goods = System::load_app_model('cloud_goods','common');$n_cgoods = $mod_common_cloud_goods->cloud_goodslist(8,4); ?>
		<?php if(is_array($n_cgoods)) foreach($n_cgoods AS $new): ?>
		<li class="list-block">
			<div class="pic"><a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $new['id']; ?>" target="_blank"><img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $new['g_thumb']; ?>"></a></div>
			<p class="name"><a href="<?php echo WEB_PATH; ?>/cgoods/<?php echo $new['id']; ?>" target="_blank"><?php echo $new['g_title']; ?></a></p>
			<p class="money"><?php echo L('cgoods.value'); ?>：<span class="rmb"><?php echo $new['g_money']; ?></span></p>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="check_out b_gray"><a href="<?php echo WEB_PATH; ?>/cgoods_list/0_0_4" target="_blank">查看更多</a></div>
</div>
<!--新品上架 new_goods 结束-->
<div class="clear"></div>
<!--lottery_show 晒单分享-->
<div class="lottery_show w1200" style="margin-top:10px">
	<div class="title br_gray bl_gray bt2_orange"><p class="c_orange t16">晒单管理</p></div>
    <div class="share_show">
		<div class="singleL">
		<?php $mod_common_share = System::load_app_model('share','common');$shareL = $mod_common_share->sharelist(0,1); ?>
	    <?php if($shareL): ?>
			<dl>
			<?php if(is_array($shareL)) foreach($shareL AS $shareLeft): ?>
				<dt><a href="<?php echo WEB_PATH; ?>/index/share/detail/<?php echo $shareLeft['sd_id']; ?>" target="_blank"><img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $shareLeft['sd_thumbs']; ?>"></a></dt>
				<dd class="u_user">
					<p class="u_head"><a href="">
                    <?php if(get_user_key($shareLeft['sd_userid'],'img','8080')=='null'): ?>
                    <img class="mt10 mb10" src="<?php echo G_UPLOAD_PATH; ?>/photo/member.jpg.8080.jpg"width="58"height="58"></a>
                    <?php  else: ?>
                    <img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo Getuserimg($shareLeft['sd_userid']); ?>.8080.jpg"></a></p>
                    <?php endif; ?>
					<p class="u_info">
						<span><a href="<?php echo WEB_PATH; ?>/uname/<?php echo idjia($shareLeft['sd_userid']); ?>" target="_blank"><?php echo Getusername($shareLeft['sd_userid']); ?></a><em><?php echo _put_time($shareLeft['sd_time']); ?></em></span>
						<cite><a href="<?php echo WEB_PATH; ?>/index/share/detail/<?php echo $shareLeft['sd_id']; ?>" target="_blank"><?php echo $shareLeft['sd_title']; ?></a></cite>
					</p>
				</dd>
				<dd class="m_summary">
					<cite><a href="<?php echo WEB_PATH; ?>/index/share/detail/<?php echo $shareLeft['sd_id']; ?>" target="_blank"><?php echo $shareLeft['sd_content']; ?></a></cite>
					<b><s></s></b>
				</dd>
			<?php endforeach; ?>
			</dl>
		<?php endif; ?>
		</div>
		<div class="singleR">
		<?php $mod_common_share = System::load_app_model('share','common');$shareR = $mod_common_share->sharelist(1,6); ?>
	    <?php if($shareR): ?>
			<ul id="ul_PostList">
	    <?php if(is_array($shareR)) foreach($shareR AS $shareRight): ?>
				<li><a href="<?php echo WEB_PATH; ?>/index/share/detail/<?php echo $shareRight['sd_id']; ?>" target="_blank"><img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $shareRight['sd_thumbs']; ?>"><p><?php echo $shareRight['sd_title']; ?></p></a></li>
		<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		</div>
    </div>
	<div class="check_out b_gray"><a href="<?php echo WEB_PATH; ?>/index/share/init/" target="_blank">查看更多</a></div>
</div>
<!--lottery_show 晒单分享end-->
<div class="clear"></div>
<!--topic 开始-->
<div class="topic w1200"style="margin-top:10px;">
	<div class="line bg_red"></div>
	<div class="topicAll">
      <script language=JavaScript>

<!--
var bannerAD=new Array();
var bannerADlink=new Array();
var adNum=0;
<?php if(is_array(readad('img'))) foreach(readad('img') AS $ad): ?>
bannerAD[<?php echo $ad['key']; ?>]="<?php echo G_UPLOAD_PATH; ?>/<?php echo $ad['content']; ?>";
bannerADlink[<?php echo $ad['key']; ?>]="";
<?php endforeach; ?>

var preloadedimages=new Array();
for (i=1;i<bannerAD.length;i++){
preloadedimages[i]=new Image();
preloadedimages[i].src=bannerAD[i];
}

function setTransition(){
if (document.all){
bannerADrotator.filters.revealTrans.Transition=Math.floor(Math.random()*23);
bannerADrotator.filters.revealTrans.apply();
}
}

function playTransition(){
if (document.all)
bannerADrotator.filters.revealTrans.play()
}

function nextAd(){
if(adNum<bannerAD.length-1)adNum++ ;
else adNum=0;
setTransition();
document.images.bannerADrotator.src=bannerAD[adNum];
playTransition();
theTimer=setTimeout("nextAd()", 4000);
}

function jump2url(){
jumpUrl=bannerADlink[adNum];
jumpTarget='_blank';
if (jumpUrl != ''){
if (jumpTarget != '')window.open(jumpUrl,jumpTarget);
else location.href=jumpUrl;
}
}
function displayStatusMsg() {
status=bannerADlink[adNum];
document.returnValue = true;
}
-->
</script>
		<div class="topicAllL fl">
			<ul class="column fl b_gray">
            <?php if(readad('img')): ?>
            <li onMouseOver="displayStatusMsg();return document.returnValue" href="javascript:jump2url()"><img src="search_banner.gif"
            name=bannerADrotator width="280" height="165" border='0' align="middle" > </li>
            <script language=JavaScript>nextAd()</script></td>
            <?php  else: ?>
			<li><img src="<?php echo G_TEMPLATES_IMAGE; ?>/u826.png" width="280" height="165"></li>
            <?php endif; ?>
				<li>
					<h1>推荐话题</h1>
					<?php $mod_common_club_db = System::load_app_model('club_db','common');$hotpost = $mod_common_club_db->GetHotClubPost(4); ?>
					<?php if(is_array($hotpost)) foreach($hotpost AS $hp): ?>
					<a href="<?php echo WEB_PATH; ?>/index/club/nei/<?php echo $hp['id']; ?>">•  <?php echo $hp['title']; ?></a>
					<?php endforeach; ?>
				</li>
				<li style="margin-left:10px;">
					<h1>最新话题</h1>
					<?php $mod_common_club_db = System::load_app_model('club_db','common');$newpost = $mod_common_club_db->GetNewClubPost(4); ?>
					<?php if(is_array($newpost)) foreach($newpost AS $np): ?>
					<a href="<?php echo WEB_PATH; ?>/index/club/nei/<?php echo $np['id']; ?>">•  <?php echo $np['title']; ?></a>
					<?php endforeach; ?>
				</li>
			</ul>
			<ul class="columns fl b_gray" style="border-top:0">
				<?php $mod_common_club_db = System::load_app_model('club_db','common');$data = $mod_common_club_db->GetClubList(4); ?>
				<?php if(is_array($data)) foreach($data AS $v): ?>

				<li>
					<?php if($v['img']==null): ?>
					<img src="<?php echo G_UPLOAD_PATH; ?>/photo/member.jpg_8080.jpg" width="80" height="80">
					<?php  else: ?>
					<img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo $v['img']; ?>" width="80" height="80">
					<?php endif; ?>
					<div class="idea fl mt10">
						<p><?php echo $v['title']; ?></p>
						<p>
							<span class="people fl"></span>
							<span class="number fl"><?php echo $v['chengyuan']; ?></span>
							<span class="message fl"></span>
							<span class="number fl"><?php echo $v['tiezi']; ?></span>
						</p>
						<a href="<?php echo WEB_PATH; ?>/index/club/show/<?php echo $v['cid']; ?>">申请加入</a>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="topicAllR fl bb_gray br_gray">
			<h1>圈子活跃用户</h1>
			<ul>
				<?php $mod_common_club_db = System::load_app_model('club_db','common');$HotUser = $mod_common_club_db->GetHotClubUser(); ?>
				<?php if(is_array($HotUser)) foreach($HotUser AS $hu): ?>
				<?php if(userid($hu['huiyuan'],'img')==null): ?>
				<li>
					<p><a href="<?php echo WEB_PATH; ?>/uname/<?php echo idjia($hu['huiyuan']); ?>"><img src="<?php echo G_UPLOAD_PATH; ?>/photo/member.jpg.8080.jpg"></a></p>
				</li>
				<?php  else: ?>
				<li>
					<p><a href="<?php echo WEB_PATH; ?>/uname/<?php echo idjia($hu['huiyuan']); ?>"><img src="<?php echo G_UPLOAD_PATH; ?>/<?php echo Getuserimg($hu['huiyuan']); ?>.8080.jpg"></a></p>
				</li>
				<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
<!--topic 结束-->

<div class="clear"></div>
<script type="text/javascript">
	Qfast.add('widgets', { path: "<?php echo G_TEMPLATES_JS; ?>/terminator2.2.min.js", type: "js", requires: ['fx'] });
	Qfast(false, 'widgets', function () {
		K.tabs({
			id: 'fsD1',   //焦点图包裹id
			conId: "D1pic1",  //** 大图域包裹id
			tabId:"D1fBt",
			tabTn:"a",
			conCn: '.fcon', //** 大图域配置class
			auto: 1,   //自动播放 1或0
			effect: 'fade',   //效果配置
			eType: 'mouseover', //** 鼠标事件
			pageBt:true,//是否有按钮切换页码
			bns: ['.prev', '.next'],//** 前后按钮配置class
			interval: 3000  //** 停顿时间
		})
	})
</script>
<script type="text/javascript">


$(".b_gray").mouseenter(function(){
   $(this).find(".w_goods_pic").find("img").stop().animate({left:"-10px"});
})
$(".b_gray").mouseleave(function(){
   $(this).find(".w_goods_pic").find("img").stop().animate({left:"0px"});
})
</script>
<?php include self::includes("index.footer"); ?>