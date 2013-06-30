<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: awfigq <awfigq@qq.com>
// +----------------------------------------------------------------------
/**
 +------------------------------------------------------------------------------
 * 分类管理
 +------------------------------------------------------------------------------
 */
class GoodsCatesAction extends CommonAction
{
    function index()
	{
		$sql = 'SELECT sm.class,sm.name FROM '.C("DB_PREFIX").'goods_cates AS gc 
			INNER JOIN '.C("DB_PREFIX").'sharegoods_module AS sm ON sm.class = gc.type 
			GROUP BY gc.type ORDER BY sm.sort ASC,sm.id ASC';
		$types = M()->query($sql);
		$this->assign('types',$types);
		
		if(count($types) > 0)
		{
			$type = $_REQUEST['type'];
			if(empty($type))
			{
				$type = current($types);
				$type = $type['class'];
			}
			
			$sql = 'SELECT gc.* FROM '.C("DB_PREFIX").'goods_cate_disable AS gcd 
				INNER JOIN '.C("DB_PREFIX").'goods_cates AS gc ON gc.id = gcd.id AND gc.type = gcd.type AND gc.pid = gcd.pid 
				WHERE gcd.type = \''.$type.'\'';
			$select_list = M()->query($sql);
			$this->assign('select_list',$select_list);
			
			$cate_list = D("GoodsCates")->where("pid='' AND type='$type'")->order('sort ASC')->select();
			$this->assign('cate_list',$cate_list);
			$this->assign('type',$type);
			$this->assign('collect_url',U("GoodsCates/collect",array("type"=>$type,'isInit'=>1)));
		}
        $this->display();
    }
	
    function collect()
	{
		$isInit =  (int)$_REQUEST['isInit'];
		$type = $_REQUEST['type'];
		if($isInit == 1)
		{
			M()->query('TRUNCATE TABLE '.C("DB_PREFIX").'goods_cate_collect');
			D('GoodsCateCollect')->add(array('cid'=>0));
			@file_put_contents(FANWE_ROOT.'/public/records/cate.sort.php','0');
			$tips = '开始获取 '.$type.' 分类';
		}
		else
		{
			$ccount = (int)@file_get_contents(FANWE_ROOT.'/public/records/cate.sort.php');
			$count = D('GoodsCateCollect')->count();
			if($count == 0)
			{
				$sql = 'DELETE FROM '.C("DB_PREFIX").'goods_cates_gl  
					WHERE (SELECT COUNT(*) FROM '.C("DB_PREFIX").'goods_cates WHERE 
					id = goods_cates_gl.f_cate_id AND type = goods_cates_gl.type) = 0';
				M()->query($sql);
				
				$sql = 'DELETE FROM '.C("DB_PREFIX").'goods_cate_disable  
					WHERE (SELECT COUNT(*) FROM '.C("DB_PREFIX").'goods_cates WHERE 
					id = goods_cate_disable.id AND type = goods_cate_disable.type) = 0';
				M()->query($sql);
				
				vendor('common');
				if($type == 'taobao')
				{
					include fimport('class/taobao','api');
					$taobao = new Taobao();
					$taobao->collectShopCates();
				}
				
				$this->assign('jumpUrl',U("GoodsCates/index",array("type"=>$type)));
				$this->success("采集成功");
				exit;	
			}
			$tips = '已获取 '.$ccount.' 个 '.$type.' 分类';
			$tips .= '<br/>还有 '.$count.' 个 子分类未获取';
		}
		$this->assign("tips",$tips);
		
		ob_start();
		ob_end_flush(); 
		ob_implicit_flush(1);
		$this->display();
		
		vendor('common');
		include fimport('class/'.$type,'api');
		$api = ucfirst(strtolower($type));
		$api = new $api();
		$api->collectCates();
		echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('GoodsCates/collect',array('type'=>$type,'time'=>TIME_UTC)).'";},500);</script>');
    }
	
    public function update()
	{
        $type = $_REQUEST['type'];
		$cates = json_decode($_REQUEST['catesjson'],true);
		M()->query('DELETE FROM '.C("DB_PREFIX")."goods_cate_disable WHERE type='$type'");
        foreach($cates as $cate)
		{
            $cate['type'] = $type;
			D("GoodsCateDisable")->add($cate);
        }
		
        $this->assign('jumpUrl', U('GoodsCates/index', array('type'=>$type)) );
        $this->success (L('EDIT_SUCCESS'));
    }
	
    public function getSelect()
	{
        $id = $_REQUEST['id'];
		$type = $_REQUEST['type'];
        $data = D("GoodsCates")->where("pid='$id' and type='$type'")->order('sort asc')->select();
        $data_html ='';
        if($data)
		{
             $data_html ='<select multiple="multiple" class="cateSelect" style="height:400px;">';
            foreach($data as $k=>$v){
                $temp_html = '<option parentID="'.$v['pid'].'" value="'.$v['id'].'"> '.$v['name'].'</option>';
                $data_html.=$temp_html;
            }
            $data_html.='</select>';
       }
       echo $data_html;exit;
    }

}
?>
