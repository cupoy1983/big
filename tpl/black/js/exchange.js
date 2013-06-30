var goodsID = 0;
var consigneeWebBox = null;
var trialWebBox = null;
jQuery(function($){
	$(".exchangeBtn").click(function(){
		if(!$.Check_Login())
			return false;
		
		goodsID = this.getAttribute("goodsID");
		goodsType = this.getAttribute("goodsType");
		if(goodsType == 0)
		{
			var readyFun = function(weebox){
				weebox.dc.append($("#consignee"));
				$("#consignee").show();
				weebox.dc.height(weebox.dc.get(0).scrollHeight);
				weebox.dc.width(weebox.dc.get(0).scrollWidth);
			};
			
			if(consigneeWebBox == null)
				consigneeWebBox = $.weeboxs.open('',{boxid:'exchange_box',contentType:'text',draggable:false,showButton:false,title:"商品兑换",width:561,onready:readyFun,isCloseToHide:true});
			else
				consigneeWebBox.show();
			$("#submitConsignee").unbind('click');
			$("#submitConsignee").bind('click',function(){
				consigneeWebBox.close();
				exchangeHandler(true,false);
			});
		}
		else
			exchangeHandler(false,false);
	});
	
	$(".trialBtn").click(function(){
		if(!$.Check_Login())
			return false;
		
		goodsID = this.getAttribute("goodsID");
		goodsType = this.getAttribute("goodsType");
		var readyFun = function(weebox){
			weebox.dc.append($("#applySubmitBox"));
			$("#applySubmitBox").show();
			weebox.dc.height(weebox.dc.get(0).scrollHeight);
			weebox.dc.width(weebox.dc.get(0).scrollWidth);
		};
		
		if(trialWebBox == null)
			trialWebBox = $.weeboxs.open('',{boxid:'exchange_box',contentType:'text',draggable:false,showButton:false,title:"申请试用",width:561,onready:readyFun,isCloseToHide:true});
		else
			trialWebBox.show();
		$("#submitTrialAppay").unbind('click');
		$("#submitTrialAppay").bind('click',function(){
			trialWebBox.close();
			exchangeHandler(true,true);
		});
	});
});

function exchangeHandler(isConsignee,istrial)
{
	if(goodsID == 0)
		return false;
	
	var query = new Object();
	query.id = goodsID;
	
	if(isConsignee)
	{
		query.address = $("#c-address").val();
		query.email = $("#c-email").val();
		query.zip = $("#c-zip").val();
		query.mobile = $("#c-mobile-phone").val();
		query.fax = $("#c-fax-phone").val();
		query.fix = $("#c-fix-phone").val();
		query.qq = $("#c-qq").val();
		query.memo = $("#c-memo").val();
		if(istrial)
			query.reason = $("#c-reason").val();
	}
	
	var action = 'submit';
	if(istrial)
		action = 'apply';
	
	$.ajax({
		type:"POST",
		url: SITE_PATH+"services/service.php?m=exchange&a="+action,
		data:query,
		cache:false,
		dataType:"json",
		success: function(result){	
			alert(result.msg);
		}
	});
}