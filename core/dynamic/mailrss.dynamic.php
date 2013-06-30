<?php
//订阅动态
function getBestRssCate()
{
	global $_FANWE;
	$args = array();
	$cache_file = getTplCache('inc/mailrss/best_rss_cate',array(),1);
	if(getCacheIsUpdate($cache_file,SHARE_CACHE_TIME,1))
	{
            
	}
	return tplFetch('inc/mailrss/best_rss_cate',$args,'');
}
function getMeRss(){
    global $_FANWE;
    $args = array();
    $cache_file = getTplCache('inc/mailrss/me_rss',array(),1);
    if(getCacheIsUpdate($cache_file, SHARE_CACHE_TIME,1)){
        
    }
    return tplFetch('inc/mailrss/me_rss',$args,'');
}
?>
