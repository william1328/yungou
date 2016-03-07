<?php 
	self::$skin ? true : exit(0);
?>
<style>
	#skinchange{
		width:100%;
		height:60px;
		background: #f60;
		padding: 0px 5px;	
		color:#fff;	
		margin-bottom:0px;
		clear: both;
	}
	#skinchange_title{
		font-size:25px;line-height: 60px; text-align: center;
	}
	#skinchange a{
		color:#008800;font-size:25px;
	}
	
</style>
<script>
function skinchange_close(){
	location.href="<?php echo WEB_PATH."/skin=-this"; ?>";
}
</script>
<div id="skinchange">
	<h1 id="skinchange_title">
		您现在正在[模板]预览模式下!
		<a href="javascript:skinchange_close();">点击关闭预览</a>
	</h1>
</div> 
