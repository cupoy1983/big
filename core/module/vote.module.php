<?php
class VoteModule
{
	public function detail()
	{
		global $_FANWE;
		$id = (int)$_FANWE['request']['id'];
		$vote = FS('Vote')->getById($id);
		if(empty($vote))
			fHeader('location: '.FU('index/index'));

		$vote['time'] = getBeforeTimelag($vote['create_time']);
		if($vote['expiration_time'] > 0)
			$vote['end_time'] = getEndTimelag($vote['expiration_time']);
		
		$vote['share'] = FS('Share')->getShareDetail($vote['share_id']);
		$vote['content'] = $vote['share']['content'];
		$vote['share_content'] = str_replace(array('#','@',"\r","\n"),array('＃','＠',' ',' '),$vote['share']['content']);
		$vote_user = FS('User')->getUserShowName($vote['uid']);
		$vote_users = FS('Vote')->getUsers($id,9);
		$vote_options = FS('Vote')->getOptions($id,$vote['num']);
		$user_votes = FS('Vote')->getVotesByUid($vote['uid'],9);
		$partici_votes = FS('Vote')->getParticiVotesByUid($vote['uid'],9);
		$hot_votes = FS('Vote')->getHotVotes(9);
		
		
		$user_is_vote = false;
		if($_FANWE['uid'] > 0)
			$user_is_vote = FS('Vote')->getUserIsVote($id,$_FANWE['uid']);
		
		$_FANWE['nav_title'] = $vote['title'].'-'.lang('common','vote');
		
		$page_args = array(
			'id'=>$id
		);

		$count = (int)FDB::resultFirst('SELECT COUNT(id) FROM '.FDB::table('vote_post').' WHERE vid = '.$id);
		
		$pager = buildPage('vote/detail',$page_args,$count,$_FANWE['page'],10);
		$post_list = FS('Vote')->getPostList($id,$pager['limit']);
		
		$args = array(
			'share_list'=>&$post_list,
			'pager'=>&$pager,
			'current_share_id'=>$vote['share_id']
		);
		$post_html = tplFetch("inc/share/post_share_list",$args);

		include template('page/vote/vote_detail');
		display();
	}
	
	public function edit()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			fHeader('location: '.FU('index/index'));
			
		$id= (int)$_FANWE['request']['id'];
		if($id == 0)
			fHeader('location: '.FU('index/index'));
			
		$vote = FS('Vote')->getById($id);
		if(empty($vote))
			fHeader('location: '.FU('index/index'));
		
		if($vote['uid'] != $_FANWE['uid'])
			fHeader('location: '.FU('index/index'));
		
		$vote['share'] = FS('Share')->getShareDetail($vote['share_id']);
		$_FANWE['nav_title'] = lang('common','vote');
		$vote_options = FS('Vote')->getOptions($id,$vote['num']);

		include template('page/vote/vote_edit');
		display();
	}
	
	public function update()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			fHeader('location: '.FU('index/index'));
			
		$id= (int)$_FANWE['request']['id'];
		if($id == 0)
			fHeader('location: '.FU('index/index'));
			
		$vote = FS('Vote')->getById($id);
		if(empty($vote))
			fHeader('location: '.FU('index/index'));
		
		if($vote['uid'] != $_FANWE['uid'])
			fHeader('location: '.FU('index/index'));
		
		FS("Vote")->update($id,$_FANWE['request']);
		fHeader('Location: '.FU('vote/detail',array('id'=>$id)));
	}
}
?>