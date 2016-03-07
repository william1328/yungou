(function(){
	
	//立即购买
	var  gcartlist = {};
		 gcartlist.DOMS = {};
		 		 
	var _x,_y,m,allscreen=false;		 
	gcartlist.heredoc = function(fn) {
		 //return fn.toString().split('\n').slice(1,-1).join('\n') + '\n'
		  return fn.toString().match(/\/\*!?(?:\@preserve)?[ \t]*(?:\r\n|\n)([\s\S]*?)(?:\r\n|\n)\s*\*\//)[1];
	}

	gcartlist.strToDom = function (arg) {
	　　 var objE = document.createElement("div");
	　　 objE.innerHTML = arg;
	　　 return objE.childNodes;
	};
	var cartlist_shopid="";
	gcartlist.gocartlist = function(shopid,path,cookie_pre){
		var syrs='';
		var shopinfo='';
		$.get(path+"/member/cart/Fastpay/",{'shopid':shopid},function(cgoodsdata){
			var cgoodsinfo = jQuery.parseJSON(cgoodsdata);
			syrs=cgoodsinfo.zongrenshu-cgoodsinfo.canyurenshu;
			shopinfo={'shopid':shopid,'money':cgoodsinfo.price,'shenyu':syrs};
            var carid='car_'+shopid;
			if(syrs!='0'){
                $('#'+carid).parent().parent().parent().find('.success .main').html(cgoodsinfo.tishi);
                $('#'+carid).parent().parent().parent().find('.success').show(1500,function(){
                    $('#'+carid).parent().parent().parent().find('.success').hide(1500);
                });
				Cartcookie(false);
			}else{
                $('#'+carid).parent().parent().parent().find('.fail .main').html(cgoodsinfo.tishi);
                $('#'+carid).parent().parent().parent().find('.fail').show(1500,function(){
                    $('#'+carid).parent().parent().parent().find('.fail').hide(1500);
                });
            }
		function Cartcookie(cook){
			var info = {};
			var Cartlist = $.cookie(cookie_pre+'Cartlist');
			if(!Cartlist){
				var info = {};
			}else{
				var info = $.evalJSON(Cartlist);
				if((typeof info) !== 'object'){
					var info = {};
				}
			}		
			if(!info[shopid]){
                //showCart("添加成功！");
				var CartTotal=$("#sCartTotal").text();
					$("#sCartTotal").text(parseInt(CartTotal)+1);
			}	
			// var number=parseInt($("#num_dig").val());	
			var number=1;	
			info[shopid]={};
			info[shopid]['num']=number;
			info[shopid]['shenyu']=shopinfo['shenyu'];
			info[shopid]['money']=shopinfo['money'];
			info['MoenyCount']='0.00';
			$.cookie(cookie_pre+'Cartlist',$.toJSON(info),{expires:7,path:'/'});	
			if(cook){
				window.location.href=path+"/member/cart/cartlist/"+new Date().getTime();//+new Date().getTime()
			}
		}
            function showCart(str){
                var html="<div id='add_cart_alert'>"+str+"</div>";
                $("body").append(html);
                $("#add_cart_alert").css("padding","20px");
                $("#add_cart_alert").css("position","absolute");
               // $("#add_cart_alert").css("lineHeight","40px");
                $("#add_cart_alert").css("border","2px solid #ccc");
                $("#add_cart_alert").css("backgroundColor","red");
                $("#add_cart_alert").css("Zindex","11000");
                //$("#add_cart_alert").css("textA","40px");
                var sp=document.body.scrollTop;
                var left=parseInt(($(window).width() - 150) / 2);
                var top=parseInt(($(document).height() - 40) / 2);
                $('#add_cart_alert').css({left:left,top:top});
                //alert(top+'--'+left+'--'+sp);
            }
		});			
	
	}
	
	window.gcartlist = gcartlist;
	
})();
