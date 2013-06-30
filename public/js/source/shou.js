var FanweShou;
if(!FanweShou)
    FanweShou = {};

(function(){
	FanweShou.browserType = !!window.ActiveXObject;
	FanweShou.url = "#SITE_URL#";
	FanweShou.domain = "#SITE_DOMAIN#";
	FanweShou.siteName = "#SITE_NAME#";
	FanweShou.imgWidth = 200;
	FanweShou.imgHeight = 120;
	
	if (typeof FanweShou.init !== 'function'){
		FanweShou.init = function(){
			if(FanweShou.getDomain() == FanweShou.domain)
			{
				alert("亲，你就在 "+FanweShou.siteName+" 呢，不能分享本站的图片哦！");
				return false;
			}
			
			FanweShou.isCancel = false;
			if(!FanweShou.ShouBox)
			{
				FanweShou.setCss();
				FanweShou.ShouBg = document.createElement("div");
				FanweShou.ShouBg.id = "FANWE_SHOU_BG";
				document.body.appendChild(FanweShou.ShouBg);
				
				FanweShou.ShouBox = document.createElement("div");
				FanweShou.ShouBox.id = "FANWE_SHOU_BOX";
				document.body.appendChild(FanweShou.ShouBox);
				
				FanweShou.CheckPageBox = document.createElement("div");
				FanweShou.CheckPageBox.className = "FSB_CHECK_PAGE";
				FanweShou.CheckPageBox.innerHTML = '亲，正在检测页面中，请稍候...<a class="FSBCP_CLOSE" href="javascript:;" onclick="FanweShou.cancel()"></a>';
				FanweShou.ShouBox.appendChild(FanweShou.CheckPageBox);
				
				FanweShou.Toolbar = document.createElement("div");
				FanweShou.Toolbar.id = "FANWE_SHOU_TOOLBAR";
				FanweShou.Toolbar.innerHTML = '<img class="FST_LOGO" id="FST_LOGO" src="'+FanweShou.url+'shou/logo.png" /><a class="FST_CANCEL" href="javascript:;" onclick="FanweShou.cancel()"></a>';
				document.body.appendChild(FanweShou.Toolbar);
				
				FanweShou.ImageBox = document.createElement("div");
				FanweShou.ImageBox.className = "FSB_IMG_BOX";
				FanweShou.ShouBox.appendChild(FanweShou.ImageBox);
				
				FanweShou.ShouIframe = document.createElement("iframe");
				FanweShou.ShouIframe.width = "100%"; 
				FanweShou.ShouIframe.height = "100%";
				FanweShou.ShouIframe.allowTransparency = true;
				FanweShou.ShouIframe.frameBorder = 0;
				FanweShou.ShouIframe.scrolling = "no";
				FanweShou.ShouBox.appendChild(FanweShou.ShouIframe);
				FanweShou.addIframeEvent();
			}
			
			FanweShou.ImageBox.innerHTML = "";
			FanweShou.ImageBox.style.display = "none";
			FanweShou.ShouBg.style.display = "block";
			FanweShou.ShouBox.style.display = "block";
			FanweShou.Toolbar.style.display = "none";
			FanweShou.ShouIframe.style.display = "none";
			FanweShou.CheckPageBox.style.display = "block";
			FanweShou.CheckPageBox.style.left = ((FanweShou.ShouBox.offsetWidth - 306) / 2) + "px";
			FanweShou.checkImgs();
		}
		
		FanweShou.setCss = function(){
			var head = document.head || document.getElementsByTagName("head")[0];
			var css = "#FANWE_SHOU_BG{background-color:#f2f2f2; height:100%; width:100%; left:0px; top:0px; zoom:1; position:fixed; z-index:2147483580; opacity:0.8; FILTER:alpha(opacity=80);}#FANWE_SHOU_BOX{height:100%; width:100%; left:0px; top:0px; zoom:1; position:absolute; z-index:2147483581;}#FANWE_SHOU_BOX *{font-size:12px;}#FANWE_SHOU_BOX iframe{position:fixed; left:0; top:0; background:transparent; display:none;}#FANWE_SHOU_BOX .FSB_CHECK_PAGE{-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;border:dashed 3px #ff277d; width:220px; height:80px; background:url("+FanweShou.url+"shou/loading.gif) no-repeat 16px center #ffe8f0; padding:0 0 0 80px;font-size:14px;line-height:80px; position:fixed; top:100px; display:none;}#FANWE_SHOU_BOX .FSBCP_CLOSE{display:block; width:13px; height:13px;background:url("+FanweShou.url+"shou/closem.png) no-repeat; position:absolute; top:6px; right:6px;}#FANWE_SHOU_TOOLBAR{height:75px; width:100%; left:0px; top:0px; zoom:1; position:fixed; z-index:2147483582; background:url("+FanweShou.url+"shou/toolbar.png) repeat-x; _filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+FanweShou.url+"shou/toolbar.png', sizingMethod='scale'); _background:none; display:none;}#FANWE_SHOU_TOOLBAR .FST_LOGO{height:50px; float:left; margin:8px 0 0 8px;}#FANWE_SHOU_TOOLBAR .FST_CANCEL{display:block; width:69px; height:31px;background:url("+FanweShou.url+"shou/close.png) no-repeat; float:right; margin:16px 20px 0 0;}#FANWE_SHOU_BOX .FSB_IMG_BOX{float:left; border-top:1px solid #E7E7E7;border-left: 1px solid #E7E7E7;margin:75px 0 0 0;}#FANWE_SHOU_BOX .FSBIB_ITEM{border-right: 1px solid #E7E7E7;border-bottom: 1px solid #E7E7E7;padding:4px;float: left;position:relative;background: white;text-align:center;overflow:hidden; text-decoration:none;}#FANWE_SHOU_BOX .FSBIB_ITEM img{border:0; vertical-align:middle;}#FANWE_SHOU_BOX .FSBIB_ITEM input{border:none; width:129px; height:32px; background:url("+FanweShou.url+"shou/publish.png) no-repeat; padding:0; position:absolute;cursor:pointer; display:none; text-indent:-200px; overflow:hidden;}#FANWE_SHOU_BOX .FSBIB_ITEM:hover input{display:block;}#FANWE_SHOU_BOX .FSBIB_ITEM em{font-style: normal;font-size: 10px;font-family: Arial;position: absolute;bottom:10px;text-align: center;width: 85px;height: 21px;z-index: 1000;background: url("+FanweShou.url+"shou/size.png) no-repeat;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+FanweShou.url+"shou/size.png', sizingMethod='scale'); _background:none;line-height: 21px;z-index: 1; color:#000;}";
			if(!FanweShou.ShouStyle)
			{
				FanweShou.ShouStyle = document.createElement("style");
				FanweShou.ShouStyle.type = "text/css";
				FanweShou.ShouStyle.media = "screen";
				head.appendChild(FanweShou.ShouStyle);
			}
			if(FanweShou.browserType)
				FanweShou.ShouStyle.styleSheet.cssText = css;
			else
				FanweShou.ShouStyle.innerHTML = css;
		}
		
		FanweShou.addIframeEvent = function(){
			FanweShou.IframeHandler;
			if(window.postMessage)
			{
				FanweShou.IframeHandler = function(event){
					event = event || window.event;
					if(event.data.indexOf('publish') > -1)
					{
						FanweShou.checkPublish(event.data);
					}
					else if(event.data == 'getimgs')
					{
						FanweShou.ShouIframe.contentWindow.postMessage(FanweShou.postImgs,"*");
					}
					else if(event.data == 'close')
					{
						FanweShou.cancel();
						window.addEventListener ? window.removeEventListener("message",FanweShou.IframeHandler,false) : window.detachEvent("onmessage",FanweShou.IframeHandler);
					}
				}
				window.addEventListener ? window.addEventListener("message",FanweShou.IframeHandler,false) : window.attachEvent("onmessage",FanweShou.IframeHandler);
			}
			else
			{
				FanweShou.IframeHandler = window.setInterval(function(){
					if(FanweShou.ShouIframe.contentWindow.name.indexOf('publish') > -1)
					{
						FanweShou.checkPublish(FanweShou.ShouIframe.contentWindow.name);
					}
					else if(FanweShou.ShouIframe.contentWindow.name == 'close')
					{
						FanweShou.cancel();
						window.clearInterval(FanweShou.IframeHandler);
					}
				},500);
			}
		}
		
		FanweShou.checkPublish = function(data){
			data = data.split(",");
			var status = data[2];
			if(data[1] == 'g')
			{
				if(status == 1)
				{
					FanweShou.publishGoods();
					return;
				}
				FanweShou.cancel();
				
				if(status == 0)
					alert("亲，没有获取商品信息，等会再分享该商品吧！");
				else if(status == -1)
					alert("亲，你已分享过这个商品了哦！");
				else if(status == -2)
					alert("亲，"+FanweShou.siteName+" 禁止发布这个商品，分享其他商品吧！");
				else if(status == -3)
					alert("亲，该店铺的商品已被禁止分享，分享其他店铺的商品吧！");
				else if(status == -4)
					alert("亲，这种分类的商品已被禁止分享，分享其他分类的商品吧！");
			}
			else
			{
				if(status == 0)
					alert("亲，分享图片发生错误，再分享一次吧！");
				else if(status == -1)
					alert("亲，没能获取你分享的图片，分享其他图片吧！");
				else if(status == -2)
					alert("亲，你分享的图片太大了，分享其他图片吧！");
				FanweShou.showImgs();
			}
		}
		
		FanweShou.getDomain = function(){
			var regExp = new RegExp(":\/\/([^\/]*)(?:\/)?");
			var domain = window.location.href.match(regExp);
			if(domain)
			{
				domain = domain[1].split(".");
				domain.shift();
				domain = domain.join(".");
			}
			return domain;       
		}
		
		FanweShou.cancel = function(){
			FanweShou.isCancel = true;
			FanweShou.ImageBox.innerHTML = "";
			FanweShou.ImageBox.style.display = "none";
			FanweShou.Toolbar.style.display = "none";
			FanweShou.ShouIframe.style.display = "none";
			FanweShou.CheckPageBox.style.display = "none";
			FanweShou.ShouBg.style.display = "none";
			FanweShou.ShouBox.style.display = "none";
		}
		
		FanweShou.publishGoods = function(){
			if(FanweShou.isCancel)
				return;
			
			FanweShou.ShouIframe.style.display = "block";
			FanweShou.CheckPageBox.style.display = "none";
			FanweShou.ShouIframe.src=FanweShou.url+"shou.php?type=g&href="+encodeURIComponent(window.location.href)+"&t="+(new Date()).getTime();
		}
		
		FanweShou.publishPhoto = function(src,width,height){
			if(FanweShou.isCancel)
				return;
			
			FanweShou.ShouIframe.style.display = "block";
			FanweShou.Toolbar.style.display = "none";
			FanweShou.ImageBox.style.display = "none";
			FanweShou.ShouIframe.src=FanweShou.url+"shou.php?type=p&href="+src+"&w="+width+"&h="+height+"&t="+(new Date()).getTime();
		}
		
		FanweShou.showImgs = function(){
			FanweShou.ShouIframe.style.display = "none";
			FanweShou.CheckPageBox.style.display = "none";
			FanweShou.Toolbar.style.display = "block";
			FanweShou.ImageBox.style.display = "block";
			document.body.scrollTop = 0;
			
			if(!FanweShou.imgs)
				return;
			
			var img,scale,left,top,height,width,bleft,btop,sleft;
			var size = FanweShou.getImgWidthByPage() - 1;
			var html = "";
			
			for(var i = 0; i < FanweShou.imgs.length; i++)
			{
				img = FanweShou.imgs[i];
				if(size/img.width > size/img.height)
				{
					scale = size / img.height;
					height = size;
					width = img.width * scale;
				}
				else
				{
					scale = size / img.width;
					width = size;
					height = img.height * scale;
				}
				
				left = (size - width) / 2;
				top = (size - height) / 2;
				bleft = (size - 129) / 2;
				btop = (size - 32) / 2;
				sleft = (size - 85) / 2;
				
				html += '<a class="FSBIB_ITEM" href="javascript:;" style="width:'+size+'px;height:'+size+'px;" rel="'+encodeURIComponent(img.src)+'"><img style="width:'+width+'px;height:'+height+'px;margin-top:'+top+'px;margin-left:'+left+'px;" src="'+img.src+'"><input type="button" value="发布" style="left:'+bleft+'px;top:'+btop+'px;" onclick="FanweShou.publishPhoto(\''+encodeURIComponent(img.src)+'\','+img.width+','+img.height+')"/><em style="left:'+sleft+'px;">'+img.width+'x'+img.height+'</em></a>';
			}
			FanweShou.imgs = null;
			FanweShou.ImageBox.innerHTML = html;
		}
		
		FanweShou.getImgWidthByPage = function(){
			var width = window.document.body.offsetWidth - 1;
			var num = Math.ceil(width / (FanweShou.imgWidth + 9));
			return width / num;
		}
		
		FanweShou.checkImgs = function(){
			var imgs = document.getElementsByTagName("img");
			var imageCount = imgs.length;
			FanweShou.imgs = new Array();
			FanweShou.postImgs = "";
			var temp = new Object();
			for(var i = 0; i < imgs.length; i++)
			{
				if(FanweShou.isCancel)
					return;
				
				if(imgs[i] && imgs[i].src)
				{
					if(temp[encodeURIComponent(imgs[i].src)])
					{
						imageCount--;
						continue;
					}
					temp[encodeURIComponent(imgs[i].src)] = true;
					
					FanweShou.getImgSize(imgs[i].src,function(src,width,height){
						if(width >= FanweShou.imgWidth && height >= FanweShou.imgHeight)
						{
							if(FanweShou.postImgs == "")
								FanweShou.postImgs += '[{"width":'+width+',"height":'+height+',"src":"'+encodeURIComponent(src)+'"}';
							else
								FanweShou.postImgs += ',{"width":'+width+',"height":'+height+',"src":"'+encodeURIComponent(src)+'"}';
							
							FanweShou.imgs.push({"width":width,"height":height,"src":src});
						}
						
						imageCount--;
						if(imageCount == 0)
						{
							temp = null;
							delete temp;
							if(FanweShou.imgs.length > 0)
							{
								FanweShou.postImgs += "]";
								FanweShou.checkPage();
							}
							else
							{
								FanweShou.cancel();
								alert("亲，页面上图片都太小了哦！");
							}
						}
					});
				}
				else
					imageCount--;
			}
		}
		
		FanweShou.getImgSize = function(src,result){
			var img= new Image();
			var bln = false;
			img.onload = function(){
				if(bln)
					return;
				result.call(this,src,img.width,img.height);
				img.onload = null;
				img.onerror = null;
				delete img;
			}
			img.onerror = function(){
				if(bln)
					return;
				result.call(this,src,0,0);
				img.onload = null;
				img.onerror = null;
				delete img;
			}
			img.src = src;
			if(img.complete){
				bln = true;
				img.onload = null;
				img.onerror = null;
				if(img.width && img.height)
					result.call(this,src,img.width,img.height);
				else
					result.call(this,src,0,0);
				delete img;
			}
		}
		
		FanweShou.showLogin = function(){
			if(FanweShou.isCancel)
				return;
			
			FanweShou.LoginPageIsShow = true;
			FanweShou.ShouIframe.style.display = "block";
			FanweShou.CheckPageBox.style.display = "none";
			FanweShou.ShouIframe.src=FanweShou.url+"shou.php?href="+encodeURIComponent(window.location.href)+"&t="+(new Date()).getTime();
		}
	
		FanweShou.checkPage = function(){
			var head = document.head || document.getElementsByTagName("head")[0];
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.src = FanweShou.url+"services/service.php?m=shou&a=init&url="+encodeURIComponent(window.location.href)+"&callback=FanweShou.checkResult&t="+(new Date()).getTime();
			head.appendChild(script);
		}
		
		FanweShou.checkResult = function(result){
			if(FanweShou.isCancel)
				return;
			
			if(result.uid == 0)
			{
				if(!FanweShou.LoginPageIsShow)
				{
					FanweShou.showLogin();
				}
			}
			else if(result.goods == 1)
			{
				FanweShou.checkPublish("publish,g,"+result.status);
			}
			else
			{
				FanweShou.showImgs();
			}
		}
	}
	else
	{
		FanweShou.setCss();
		if(document.getElementById("FST_LOGO"))
		{
			document.getElementById("FST_LOGO").src=FanweShou.url+"shou/logo.png";
		}
		FanweShou.addIframeEvent();
	}
}());
FanweShou.init();