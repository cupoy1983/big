jQuery(function($){
	$('.t_l .pic div').live('click',function(){
		var parent = $(this).parent();
		var index = $('div',parent).index(this);
		var picb = parent.next();
		parent.hide();
		var picbobj = $('li',picb).eq(index);
		picbobj.show();
		
		var img = $('.lazyload',picbobj).get(0);
		var src = img.getAttribute('original');
		var scaleType = parseInt(img.getAttribute('scaleType'));
		var scaleWidth = parseInt(img.getAttribute('scaleWidth'));
		var scaleHeight = parseInt(img.getAttribute('scaleHeight'));
		$.ScaleImage(img,src,scaleType,scaleWidth,scaleHeight);
		return false;
	});
	
	$('.t_l .pic_b .pic_b_bd').live('click',function(){
		if($(this).hasClass('noSmall'))
			return;
		var parent = $(this).parent();
		parent.hide();
		parent.parent().prev().show();
	});
	
	$("#SHARE_DETAIL_LiST .pic_b_bd").live('mouseover',function(){
		$(".add_to_album_btn",this).show();
	}).live('mouseout',function(){
		$(".add_to_album_btn",this).hide();
	});
});