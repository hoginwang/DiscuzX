<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('edit', 'delete', 'related', 'batch', 'pushplus', 'verify', 'checkhtmlname')) ? $_GET['op'] : 'add';
$aid = intval($_GET['aid']);
$catid = intval($_GET['catid']);
list($seccodecheck, $secqaacheck) = seccheck('publish');

$article = $article_content = array();
if($aid) {
	$article = C::t('portal_article_title')->fetch($aid);
	if(!$article) {
		showmessage('article_not_exist', dreferer());
	}
}

loadcache('portalcategory');
$portalcategory = $_G['cache']['portalcategory'];
if($catid && empty($portalcategory[$catid])) {
	showmessage('portal_category_not_find', dreferer());
}
if(empty($article) && $catid && $portalcategory[$catid]['disallowpublish']) {
	showmessage('portal_category_disallowpublish', dreferer());
}

if(empty($catid) && $article) {
	$catid = $article['catid'];
}
$htmlstatus = !empty($_G['setting']['makehtml']['flag']) && $portalcategory[$catid]['fullfoldername'];

if(submitcheck("articlesubmit", 0, $seccodecheck, $secqaacheck)) {

	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		check_articleperm($catid);
	}

	$_POST['title'] = getstr(trim($_POST['title']), 80);
	if(strlen($_POST['title']) < 1) {
		showmessage('title_not_too_little');
	}
	$_POST['title'] = censor($_POST['title']);

	$_POST['pagetitle'] = getstr(trim($_POST['pagetitle']), 60);
	$_POST['pagetitle'] = censor($_POST['pagetitle']);
	$htmlname = basename(trim($_POST['htmlname']));

	$highlight_style = $_GET['highlight_style'];
	$style = '';
	$style = implode('|',$highlight_style);
	if(empty($_POST['summary'])) $_POST['summary'] = preg_replace("/(\s|\<strong\>##########NextPage(\[title=.*?\])?##########\<\/strong\>)+/", ' ', $_POST['content']);
	$summary = portalcp_get_summary($_POST['summary']);
	$summary = censor($summary);

	$_GET['author'] = dhtmlspecialchars($_GET['author']);
	$_GET['url'] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['url']));
	$_GET['from'] = dhtmlspecialchars($_GET['from']);
	$_GET['fromurl'] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['fromurl']));
	$_GET['dateline'] = !empty($_GET['dateline']) ? strtotime($_GET['dateline']) : TIMESTAMP;

	if(!preg_match('/^https?:\/\//is', $_GET['url'])) {
		$_GET['url'] = '';
	}

	if(!preg_match('/^https?:\/\//is', $_GET['fromurl'])) {
		$_GET['fromurl'] = '';
	}

	if(censormod($_POST['title']) || $_G['group']['allowpostarticlemod']) {
		$article_status = 1;
	} else {
		$article_status = 0;
	}

	$setarr = array(
		'title' => $_POST['title'],
		'author' => $_GET['author'],
		'from' => $_GET['from'],
		'fromurl' => $_GET['fromurl'],
		'dateline' => intval($_GET['dateline']),
		'url' => $_GET['url'],
		'allowcomment' => !empty($_POST['forbidcomment']) ? '0' : '1',
		'summary' => $summary,
		'catid' => intval($_POST['catid']),
		'tag' => article_make_tag($_POST['tag']),
		'status' => $article_status,
		'highlight' => $style,
		'showinnernav' => empty($_POST['showinnernav']) ? '0' : '1',
	);

	if(empty($setarr['catid'])) {
		showmessage('article_choose_system_category');
	}

	if($_GET['conver']) {
		$converfiles = dunserialize($_GET['conver']);
		$setarr['pic'] = $converfiles['pic'];
		$setarr['thumb'] = intval($converfiles['thumb']);
		$setarr['remote'] = intval($converfiles['remote']);
	}

	$id = 0;
	$idtype = '';

	if(empty($article)) {
		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['id'] = intval($_POST['id']);
		$setarr['htmlname'] = $htmlname;
		$table = '';
		if($setarr['id']) {
			if($_POST['idtype']=='blogid') {
				$table = 'home_blogfield';
				$setarr['idtype'] = 'blogid';
				$id = $setarr['id'];
				$idtype = $setarr['idtype'];
			} else {
				$table = 'forum_thread';
				$setarr['idtype'] = 'tid';

				require_once libfile('function/discuzcode');
				$id = C::t('forum_post')->fetch_threadpost_by_tid_invisible($setarr['id']);
				$id = $id['pid'];
				$idtype = 'pid';
			}
		}
		$aid = C::t('portal_article_title')->insert($setarr, 1);
		if($table) {
			if($_POST['idtype']=='blogid') {
				C::t('home_blogfield')->update($setarr['id'], array('pushedaid' => $aid));
			} elseif($setarr['idtype']=='tid') {
				$modarr = array(
					'tid' => $setarr['id'],
					'uid' => $_G['uid'],
					'username' => $_G['username'],
					'dateline' => TIMESTAMP,
					'action' => 'PTA',
					'status' => '1',
					'stamp' => '',
				);
				C::t('forum_threadmod')->insert($modarr);

				C::t('forum_thread')->update($setarr['id'], array('moderated' => 1, 'pushedaid' => $aid));
			}
		}
		C::t('common_member_status')->update($_G['uid'], array('lastpost' => TIMESTAMP), 'UNBUFFERED');
		C::t('portal_category')->increase($setarr['catid'], array('articles' => 1));
		C::t('portal_category')->update($setarr['catid'], array('lastpublish' => TIMESTAMP));
		C::t('portal_article_count')->insert(array('aid'=>$aid, 'catid'=>$setarr['catid'], 'viewnum'=>1));
	} else {
		if($htmlname && $article['htmlname'] !== $htmlname) {
			$setarr['htmlname'] = $htmlname;
			$oldarticlename = $article['htmldir'].$article['htmlname'];
			unlink($oldarticlename.'.'.$_G['setting']['makehtml']['extendname']);
			for($i = 1; $i < $article['contents']; $i++) {
				unlink($oldarticlename.$i.'.'.$_G['setting']['makehtml']['extendname']);
			}
		}
		C::t('portal_article_title')->update($aid, $setarr);
	}

	//外链图片自动本地化（远程附件方式未测试）
	$content = replaceContentsRemoteImages(getstr($_POST['content'], 0, 0, 0, 0, 1), $aid);
	$content = censor($content);
	if(censormod($content) || $_G['group']['allowpostarticlemod']) {
		$article_status = 1;
	} else {
		$article_status = 0;
	}

	$regexp = '/(\<strong\>##########NextPage(\[title=(.*?)\])?##########\<\/strong\>)+/is';
	preg_match_all($regexp, $content ,$arr);
	$pagetitle = !empty($arr[3]) ? $arr[3] : array();
	$pagetitle = array_map('trim', $pagetitle);
	array_unshift($pagetitle, $_POST['pagetitle']);
	$contents = preg_split($regexp, $content);
	$cpostcount = count($contents);

	$dbcontents = C::t('portal_article_content')->fetch_all($aid);

	$pagecount = $cdbcount = count($dbcontents);
	if($cdbcount > $cpostcount) {
		$cdelete = array();
		foreach(array_splice($dbcontents, $cpostcount) as $value) {
			$cdelete[$value['cid']] = $value['cid'];
		}
		if(!empty($cdelete)) {
			C::t('portal_article_content')->delete($cdelete);
		}
		$pagecount = $cpostcount;
	}

	foreach($dbcontents as $key => $value) {
		C::t('portal_article_content')->update($value['cid'], array('title' => $pagetitle[$key], 'content' => $contents[$key], 'pageorder' => $key+1));
		unset($pagetitle[$key], $contents[$key]);
	}

	if($cdbcount < $cpostcount) {
		foreach($contents as $key => $value) {
			C::t('portal_article_content')->insert(array('aid' => $aid, 'id' => $setarr['id'], 'idtype' => $setarr['idtype'], 'title' => $pagetitle[$key], 'content' => $contents[$key], 'pageorder' => $key+1, 'dateline' => TIMESTAMP));
		}
		$pagecount = $cpostcount;
	}

	$updatearticle = array('contents' => $pagecount);
	if($article_status == 1) {
		$updatearticle['status'] = 1;
		updatemoderate('aid', $aid);
		manage_addnotify('verifyarticle');
	}

	$updatearticle = array_merge($updatearticle, portalcp_article_pre_next($catid, $aid));
	C::t('portal_article_title')->update($aid, $updatearticle);

	$newaids = array();
	$_POST['attach_ids'] = explode(',', $_POST['attach_ids']);
	foreach ($_POST['attach_ids'] as $newaid) {
		$newaid = intval($newaid);
		if($newaid) $newaids[$newaid] = $newaid;
	}
	if($newaids) {
		C::t('portal_attachment')->update_to_used($newaids, $aid);
	}

	addrelatedarticle($aid, $_POST['raids']);

	if($_GET['from_idtype'] && $_GET['from_id']) {

		$id = intval($_GET['from_id']);
		$notify = array();
		switch ($_GET['from_idtype']) {
			case 'blogid':
				$blog = C::t('home_blog')->fetch($id);
				if(!empty($blog)) {
					$notify = array(
						'url' => "home.php?mod=space&uid=$blog[uid]&do=blog&id=$id",
						'subject' => $blog['subject']
					);
					$touid = $blog['uid'];
				}
				break;
			case 'tid':
				$thread = C::t('forum_thread')->fetch($id);
				if(!empty($thread)) {
					$notify = array(
						'url' => "forum.php?mod=viewthread&tid=$id",
						'subject' => $thread['subject']
					);
					$touid = $thread['authorid'];
				}
				break;
		}
		if(!empty($notify)) {
			$notify['newurl'] = 'portal.php?mod=view&aid='.$aid;
			notification_add($touid, 'pusearticle', 'puse_article', $notify, 1);
		}
	}

	if(trim($_GET['from']) != '') {
		$from_cookie = '';
		$from_cookie_array = array();
		$from_cookie = getcookie('from_cookie');
		$from_cookie_array = explode("\t", $from_cookie);
		$from_cookie_array[] = $_GET['from'];
		$from_cookie_array = array_unique($from_cookie_array);
		$from_cookie_array = array_filter($from_cookie_array);
		$from_cookie_num = count($from_cookie_array);
		$from_cookie_start = $from_cookie_num - 10;
		$from_cookie_start = $from_cookie_start > 0 ? $from_cookie_start : 0;
		$from_cookie_array = array_slice($from_cookie_array, $from_cookie_start, $from_cookie_num);
		$from_cookie = implode("\t", $from_cookie_array);
		dsetcookie('from_cookie', $from_cookie);
	}
	dsetcookie('clearUserdata', 'home');
	$op = 'add_success';
	$article_add_url = 'portal.php?mod=portalcp&ac=article&catid='.$catid;


	$article = C::t('portal_article_title')->fetch($aid);
	$viewarticleurl = $_POST['url'] ? "portal.php?mod=list&catid=$_POST[catid]" : fetch_article_url($article);

	include_once template("portal/portalcp_article");dexit();

} elseif(submitcheck('pushplussubmit')) {

	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('no_article_specified_for_pushplus', dreferer());
	}

	$tourl = !empty($_POST['toedit']) ? 'portal.php?mod=portalcp&ac=article&op=edit&aid='.$aid : dreferer();
	$pids = (array)$_POST['pushpluspids'];
	$posts = array();
	$tid = intval($_GET['tid']);
	if($tid && $pids) {
		foreach(C::t('forum_post')->fetch_all('tid:'.$tid, $pids) as $value) {
			if($value['tid'] != $tid) {
				continue;
			}
			$posts[$value['pid']] = $value;
		}
	}
	if(empty($posts)) {
		showmessage('no_posts_for_pushplus', dreferer());
	}

	$pageorder = C::t('portal_article_content')->fetch_max_pageorder_by_aid($aid);
	$pageorder = intval($pageorder + 1);
	$inserts = array();
	foreach($posts as $post) {
		$summary = portalcp_get_postmessage($post);
		$summary .= lang('portalcp', 'article_pushplus_info', array('author'=>$post['author'], 'url'=>'forum.php?mod=redirect&goto=findpost&ptid='.$post['tid'].'&pid='.$post['pid']));
		$inserts[] = array('aid'=>$aid, 'content'=>$summary, 'pageorder'=>$pageorder, 'dateline'=>$_G['timestamp'], 'id'=>$post[pid], 'idtype' =>'pid');
		$pageorder++;
	}
	C::t('portal_article_content')->insert_batch($inserts);
	$pluscount = C::t('portal_article_content')->count_by_aid($aid);
	C::t('portal_article_title')->update($aid, array('contents' => $pluscount, 'owncomment' => 1));
	$commentnum = C::t('portal_comment')->count_by_id_idtype($aid, 'aid');
	C::t('portal_article_count')->update($aid, array('commentnum'=>intval($commentnum)));
	showmessage('pushplus_do_success', $tourl, array(), array('header'=>1, 'refreshtime'=>0));

} elseif(submitcheck('verifysubmit')) {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article, true);
	} else {
		showmessage('article_not_exist', dreferer());
	}
	if($_POST['status'] == '0') {
		C::t('portal_article_title')->update($aid, array('status'=>'0'));
		updatemoderate('aid', $aid, 2);
		$tourl = dreferer(fetch_article_url($article));
		showmessage('article_passed', $tourl);

	} elseif($_POST['status'] == '2') {
		C::t('portal_article_title')->update($aid, array('status'=>'2'));
		updatemoderate('aid', $aid, 1);
		$tourl = dreferer(fetch_article_url($article));
		showmessage('article_ignored', $tourl);

	} elseif($_POST['status'] == '-1') {
		include_once libfile('function/delete');
		deletearticle(array($aid), 0);
		updatemoderate('aid', $aid, 2);

		$tourl = dreferer('portal.php?mod=portalcp&catid='.$article['catid']);
		showmessage('article_deleted', $tourl);

	} else {
		showmessage('select_operation');
	}
}

if($op == 'delete') {

	if(!$aid) {
		showmessage('article_edit_nopermission');
	}
	check_articleperm($article['catid'], $aid, $article);

	if(submitcheck('deletesubmit')) {
		include_once libfile('function/delete');
		$article = deletearticle(array(intval($_POST['aid'])), intval($_POST['optype']));
		showmessage('article_delete_success', "portal.php?mod=list&catid={$article[0][catid]}");
	}

} elseif($op == 'related') {

	$raid = intval($_GET['raid']);
	$ra = array();
	if($raid) {
		$ra = C::t('portal_article_title')->fetch($raid);
	}

} elseif($op == 'batch') {

	check_articleperm($catid);

	$aids = $_POST['aids'];
	$optype = $_POST['optype'];
	if(empty($optype) || $optype == 'push') showmessage('article_action_invalid');
	$aids = array_map('intval', $aids);
	$aids = array_filter($aids);
	if(empty($aids)) showmessage('article_not_choose');

	if (submitcheck('batchsubmit')) {
		if ($optype == 'trash' || $optype == 'delete') {
				require_once libfile('function/delete');
				$istrash = $optype == 'trash' ? 1 : 0;
				$article = deletearticle($aids, $istrash);
				showmessage('article_delete_success', dreferer("portal.php?mod=portalcp&ac=category&catid={$article[0][catid]}"));
		} elseif($optype == 'move') {
			if($catid) {
				$categoryUpdate = array();
				foreach(C::t('portal_article_title')->fetch_all($aids) as $s_article) {
					$categoryUpdate[$s_article['catid']] = $categoryUpdate[$s_article['catid']] ? --$categoryUpdate[$s_article['catid']] : -1;
					$categoryUpdate[$catid] = $categoryUpdate[$catid] ? ++$categoryUpdate[$catid] : 1;
				}
				foreach($categoryUpdate as $scatid=>$scatnum) {
					if($scatnum) {
						C::t('portal_category')->increase($scatid, array('articles' => $scatnum));
					}
				}
				C::t('portal_article_title')->update($aids, array('catid'=>$catid));
				showmessage('article_move_success', dreferer("portal.php?mod=portalcp&ac=category&catid=$catid"));
			} else {
				showmessage('article_move_select_cat', dreferer());
			}
		}

	}

} elseif($op == 'verify') {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('article_not_exist', dreferer());
	}

} elseif($op == 'pushplus') {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('no_article_specified_for_pushplus', dreferer());
	}

	$pids = (array)$_POST['topiclist'];
	$tid = intval($_GET['tid']);
	$pushedids = array();
	$pushcount = $pushedcount = 0;
	if(!empty($pids)) {
		foreach(C::t('portal_article_content')->fetch_all($aid) as $value) {
			$pushedids[] = intval($value['id']);
			$pushedcount++;
		}
		$pids = array_diff($pids, $pushedids);
	}
	$pushcount = count($pids);

	if(empty($pids)) {
		showmessage($pushedids ? 'all_posts_pushed_already' : 'no_posts_for_pushplus');
	}

} else if($op == 'checkhtmlname') {
	$htmlname = basename(trim($_GET['htmlname']));
	if($htmlstatus) {
		$_time = !empty($article) ? $article['dateline'] : TIMESTAMP;
		if(file_exists(helper_makehtml::fetch_dir($portalcategory[$catid]['fullfoldername'], $_time).$htmlname.'.'.$_G['setting']['makehtml']['extendname'])) {
			showmessage('html_existed');
		} else {
			showmessage('html_have_no_exists');
		}
	} else {
		showmessage('make_html_closed');
	}

} else {

	if(empty($_G['cache']['portalcategory'])) {
		showmessage('portal_has_not_category');
	}

	if(!checkperm('allowmanagearticle') && !checkperm('allowpostarticle')) {
		$allowcategorycache = array();
		if($allowcategory = getallowcategory($_G['uid'])) {
			foreach($allowcategory as $catid => $category) {
				$allowcategorycache[$catid] = $_G['cache']['portalcategory'][$catid];
			}
		}
		foreach($allowcategorycache as &$_value) {
			if($_value['upid'] && !isset($allowcategorycache[$_value['upid']])) {
				$_value['level'] = 0;
			}
		}
		$_G['cache']['portalcategory'] = $allowcategorycache;
	}

	if(empty($_G['cache']['portalcategory'])) {
		showmessage('portal_article_add_nopermission');
	}

	$category = $_G['cache']['portalcategory'];
	$cate = $category[$catid];
	$categoryselect = category_showselect('portal', 'catid', true, !empty($article['catid']) ? $article['catid'] : $catid);

	if($aid) {
		$catid = intval($article['catid']);
	}

	if($aid && $article['highlight']) {
		$stylecheck = '';
		$stylecheck = explode('|', $article['highlight']);
	}

	$from_cookie_str = '';
	$from_cookie = array();
	$from_cookie_str = stripcslashes(getcookie('from_cookie'));
	$from_cookie = explode("\t", $from_cookie_str);
	$from_cookie = array_filter($from_cookie);

	if($article) {

		foreach(C::t('portal_article_content')->fetch_all($aid) as $key => $value) {
			$nextpage = '';
			if($key > 0) {
				$pagetitle = $value['title'] ? '[title='.$value['title'].']' : '';
				$nextpage = "\r\n".'<strong>##########NextPage'.$pagetitle.'##########</strong>';
			} else {
				$article_content['title'] = $value['title'];
			}
			$article_content['content'] .= $nextpage.$value['content'];
		}

		$article['attach_image'] = $article['attach_file'] = '';
		foreach(C::t('portal_attachment')->fetch_all_by_aid($aid) as $value) {
			if($value['isimage']) {
				if($article['pic']) {
					$value['pic'] = $article['pic'];
				}
			} else {
			}
			$attachs[] = $value;
		}
		if($article['idtype'] == 'tid') {
			foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$article['id'], 'tid', $article['id']) as $value) {
				if($value['isimage']) {
					if($article['pic']) {
						$value['pic'] = $article['pic'];
					}
					$value['attachid'] = $value['aid'];
				} else {
				}
				$value['from'] = 'forum';
				$attachs[] = $value;
			}
		}

		if($article['pic']) {
			$article['conver'] = addslashes(serialize(array('pic'=>$article['pic'], 'thumb'=>$article['thumb'], 'remote'=>$article['remote'])));
		}

		$article['related'] = array();
		if(($relateds = C::t('portal_article_related')->fetch_all_by_aid($aid))) {
			foreach(C::t('portal_article_title')->fetch_all(array_keys($relateds)) as $raid=>$value) {
				$article['related'][$raid] = $value['title'];
			}
		}
	}

	$_GET['from_id'] = empty ($_GET['from_id'])?0:intval($_GET['from_id']);
	if($_GET['from_idtype'] != 'blogid') $_GET['from_idtype'] = 'tid';

	$idtypes = array($_GET['from_idtype'] => ' selected');
	if($_GET['from_idtype'] && $_GET['from_id']) {

		$havepush = C::t('portal_article_title')->fetch_count_for_idtype($_GET['from_id'], $_GET['from_idtype']);
		if($havepush) {
			if($_GET['from_idtype'] == 'blogid') {
				showmessage('article_push_blogid_invalid_repeat', '', array(), array('return'=>true));
			} else {
				showmessage('article_push_tid_invalid_repeat', '', array(), array('return'=>true));
			}
		}

		switch ($_GET['from_idtype']) {
		case 'blogid':
			$blog = array_merge(
				C::t('home_blog')->fetch($_GET['from_id']),
				C::t('home_blogfield')->fetch($_GET['from_id'])
			);
			if($blog) {
				if($blog['friend']) {
					showmessage('article_push_invalid_private');
				}
				$article['title'] = getstr($blog['subject'], 0);
				$article['summary'] = portalcp_get_summary($blog['message']);
				$article['fromurl'] = 'home.php?mod=space&uid='.$blog[uid].'&do=blog&id='.$blog[blogid];
				$article['author'] = $blog['username'];
				$article_content['content'] = dhtmlspecialchars($blog['message']);
			}
			break;
		default:
			$posttable = getposttablebytid($_GET['from_id']);
			$thread = C::t('forum_thread')->fetch($_GET['from_id']);
			$thread = array_merge($thread, C::t('forum_post')->fetch_threadpost_by_tid_invisible($_GET['from_id']));
			if($thread) {
				$article['title'] = $thread['subject'];
				$thread['message'] = portalcp_get_postmessage($thread, $_GET['getauthorall']);
				$article['summary'] = portalcp_get_summary($thread['message']);
				$article['fromurl'] = 'forum.php?mod=viewthread&tid='.$thread['tid'];
				$article['author'] = $thread['author'];
				$article_content['content'] = dhtmlspecialchars($thread['message']);

				$article['attach_image'] = $article['attach_file'] = '';
				foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$thread['tid'], 'pid', $thread['pid'], 'aid DESC') as $attach) {
					$attachcode = '[attach]'.$attach['aid'].'[/attach]';
					if(!strexists($article_content['content'], $attachcode)) {
						$article_content['content'] .= '<br /><br />'.$attachcode;
					}
					if($attach['isimage']) {
						if($article['pic']) {
							$attach['pic'] = $article['pic'];
						}
					} else {
					}
					$attach['from'] = 'forum';
					$attachs[] = $attach;
				}
			}
			break;
		}
	}

	if(!empty($article['dateline'])) {
		$article['dateline'] = dgmdate($article['dateline']);
	}
	if(!empty($attachs)) {
		$article['attachs'] = get_upload_content($attachs);
	}
	$article_tags = article_parse_tags($article['tag']);
	$tag_names = article_tagnames();
}
require_once libfile('function/upload');
$swfconfig = getuploadconfig($_G['uid'], 0, false);
require_once libfile('function/spacecp');
$albums = getalbums($_G['uid']);
include_once template("portal/portalcp_article");

function portalcp_get_summary($message) {
	$message = preg_replace(array("/\[attach\].*?\[\/attach\]/", "/\&[a-z]+\;/i", "/\<script.*?\<\/script\>/"), '', $message);
	$message = preg_replace("/\[.*?\]/", '', $message);
	$message = getstr(strip_tags($message), 200);
	return $message;
}

function portalcp_get_postmessage($post, $getauthorall = '') {
	global $_G;
	$forum = C::t('forum_forum')->fetch($post['fid']);
	require_once libfile('function/discuzcode');
	$language = lang('forum/misc');
	if($forum['type'] == 'sub' && $forum['status'] == 3) {
		loadcache('grouplevels');
		$grouplevel = $_G['grouplevels'][$forum['level']];
		$group_postpolicy = $grouplevel['postpolicy'];
		if(is_array($group_postpolicy)) {
			$forum = array_merge($forum, $group_postpolicy);
		}
	}
	$post['message'] = preg_replace($language['post_edit_regexp'], '', $post['message']);

	$_message = '';
	if($getauthorall) {
		foreach(C::t('forum_post')->fetch_all_by_tid('tid:'.$post['tid'], $post['tid'], true, '', 0, 0, null, null, $post['authorid']) as $value){
			if(!$value['first']) {
				$value['message'] = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", '', $value['message']);
				$value['message'] = discuzcode($value['message'], $value['smileyoff'], $value['bbcodeoff'], $value['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $forum['allowhtml'], 0, 0, $value['authorid'], $forum['allowmediacode'], $value['pid']);
				portalcp_parse_postattch($value);
				$_message .= '<br /><br />'.$value['message'];
			}
		}
	}

	$msglower = strtolower($post['message']);
	if(strpos($msglower, '[/media]') !== FALSE) {
		$post['message'] = preg_replace_callback("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", 'portalcp_get_postmessage_callback_parsearticlemedia_12', $post['message']);
	}
	if(strpos($msglower, '[/audio]') !== FALSE) {
		$post['message'] = preg_replace_callback("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", 'portalcp_get_postmessage_callback_parsearticlemedia_2', $post['message']);
	}
	if(strpos($msglower, '[/flash]') !== FALSE) {
		$post['message'] = preg_replace_callback("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", 'portalcp_get_postmessage_callback_parsearticlemedia_4', $post['message']);
	}

	$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $forum['allowhtml'], 0, 0, $post['authorid'], $forum['allowmediacode'], $post['pid']);
	portalcp_parse_postattch($post);

	if(strpos($post['message'], '[/flash1]') !== FALSE) {
		$post['message'] = str_replace('[/flash1]', '[/flash]', $post['message']);
	}
	return $post['message'].$_message;
}

function portalcp_get_postmessage_callback_parsearticlemedia_12($matches) {
	return parsearticlemedia($matches[1], $matches[2]);
}

function portalcp_get_postmessage_callback_parsearticlemedia_2($matches) {
	return parsearticlemedia('mid,0,0', $matches[2]);
}

function portalcp_get_postmessage_callback_parsearticlemedia_4($matches) {
	return parsearticlemedia('swf,0,0', $matches[4]);
}

function portalcp_parse_postattch(&$post) {
	static $allpostattchs = null;
	if($allpostattchs === null) {
		foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$post['tid'], 'tid', $post['tid']) as $attch) {
			$allpostattchs[$attch['pid']][$attch['aid']] = $attch['aid'];
		}
	}
	$attachs = $allpostattchs[$post['pid']];
	if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
		$attachs = array_diff($allpostattchs[$post['pid']], $matchaids[1]);
	}
	if($attachs) {
		$add = '';
		foreach($attachs as $attachid) {
			$add .= '<br/>'.'[attach]'.$attachid.'[/attach]';
		}
		$post['message'] .= $add;
	}
}
function parsearticlemedia($params, $url) {
	global $_G;

	$params = explode(',', $params);
	$width = intval($params[1]) > 800 ? 800 : intval($params[1]);
	$height = intval($params[2]) > 600 ? 600 : intval($params[2]);
	$url = addslashes($url);
	if($flv = parseflv($url, 0, 0)) {
		if(!empty($flv) && preg_match("/\.flv$/i", $flv['flv'])) {
			$flv['flv'] = $_G['style']['imgdir'].'/flvplayer.swf?&autostart=true&file='.urlencode($flv['flv']);
		}
		$url = $flv['flv'];
		$params[0] = 'swf';
	}
	if(in_array(count($params), array(3, 4))) {
		$type = $params[0];
		$url = str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url));
		switch($type) {
			case 'mp3':
			case 'wma':
			case 'ra':
			case 'ram':
			case 'wav':
			case 'mid':
				return '[flash=mp3]'.$url.'[/flash1]';
			case 'rm':
			case 'rmvb':
			case 'rtsp':
				return '[flash=real]'.$url.'[/flash1]';
			case 'swf':
				return '[flash]'.$url.'[/flash1]';
			case 'asf':
			case 'asx':
			case 'wmv':
			case 'mms':
			case 'avi':
			case 'mpg':
			case 'mpeg':
			case 'mov':
				return '[flash=media]'.$url.'[/flash1]';
			default:
				return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		}
	}
	return;
}

function portalcp_article_pre_next($catid, $aid) {
	$data = array(
		'preaid' => C::t('portal_article_title')->fetch_preaid_by_catid_aid($catid, $aid),
		'nextaid' => C::t('portal_article_title')->fetch_nextaid_by_catid_aid($catid, $aid),
	);
	if($data['preaid']) {
		C::t('portal_article_title')->update($data['preaid'], array(
			'preaid' => C::t('portal_article_title')->fetch_preaid_by_catid_aid($catid, $data['preaid']),
			'nextaid' => C::t('portal_article_title')->fetch_nextaid_by_catid_aid($catid, $data['preaid']),
			)
		);
	}
	return $data;
}

//下载远程图片
function replaceContentsRemoteImages($content, $aid){
    //error_reporting(5);
    global $_G;

    //require_once("Snoopy.class.php");
    $snoopy = new Snoopy();

    //本地url中的域名
    $host_domain = $_G['siteurl'];
   
    preg_match_all("@<img(.*)src=['|\"](.*)['|\"](.*)>@imsU", $content, $match);

    //封面图片
    $pic_cover = '';

    $imagesHrefArr = $match[2];
    foreach($imagesHrefArr as $key => $imageurl) {
        $res = $snoopy->fetch($imageurl);
        if($res) {
            $results = $snoopy->results;

            //后缀名
            $upload = new discuz_upload();
            $ext = $upload->fileext($imageurl);

            //保存路径+文件名
            $dirname = "./data/attachment/portal/".date("Ym/d/");
            @mkdir($dirname, 0777, true);
            $filename = $dirname.random(22);
            $savename = $filename.".".$ext;

            //将第一个图片记录为封面图片
            if(empty($pic_cover)) {
                $pic_cover = $savename;
            }
            
            //保存大图
            $res = file_put_contents($savename, $results);

            //缩略图名
            require_once libfile('class/image');
            $image = new image();
            $thumbimgwidth = 300;
            $thumbimgheight = 300;
            $thumb = $image->Thumb($savename, '', $thumbimgwidth, $thumbimgheight, 2);
            $thumbname = $image->target;

            //判断当前图片外，有无a标签
            $pattern = "@<a [^>]*>[\s]*". preg_quote($match[0][$key])."[\s]*</a>@imsU";
            $r = preg_match($pattern, $content, $match2);

            /*
            //替换远程图片为本地图
            $maxWidth = 848;
            //若图片大小超过一定尺寸，替换为缩略图
            if($image->imginfo['width'] > $maxWidth) {
                //缩略图肯定会有a链接，查看原图
                $content = str_replace($match[0][$key], "<a href='{$savename}' target='_blank'><img src='{$thumbname}' /></a>", $content);
                $is_thumb = 1;
            }else{
            */
                //若不需要缩略图，则根据原img标签外是否有a标签，再判断是否需要加a标签
                if($r > 0) {
                    $content = str_replace($match[0][$key], "<a href='{$savename}' target='_blank'><img src='{$savename}' /></a>", $content);
                }else{
                    $content = str_replace($match[0][$key], "<img src='{$savename}' />", $content);
                }
                $is_thumb = 0;
            /*
            }
            */

            //将图片设为该文章的附件
            $setarr = array(
                'uid' => $_G['uid'],
                'filename' => '',
                'attachment' => str_replace("./data/attachment/portal/", "", $savename),
                'filesize' => filesize($savename),
                'isimage' => 1,
                'thumb' => $is_thumb,
                'remote' => 0,
                'filetype' => array_pop(explode(".", $savename)),
                'dateline' => $_G['timestamp'],
                'aid' => $aid
            );
            $r = C::t('portal_attachment')->insert($setarr, true);
        }
    }

    //将第一个图片设为封面（若有的话）
    if($pic_cover){
        $setarr = array(
            'pic' => str_replace('./data/attachment/', '', $pic_cover),
            'thumb' => 1,
        );
        $r = C::t('portal_article_title')->update($aid, $setarr);
    }

    //return
    return $content;
}

//Snoopy采集类
class Snoopy
{
    /**** Public variables ****/

    /* user definable vars */

    var $scheme = 'http'; // http or https
    var $host = "www.php.net"; // host name we are connecting to
    var $port = 80; // port we are connecting to
    var $proxy_host = ""; // proxy host to use
    var $proxy_port = ""; // proxy port to use
    var $proxy_user = ""; // proxy user to use
    var $proxy_pass = ""; // proxy password to use

    var $agent = "Snoopy v2.0.0"; // agent we masquerade as
    var $referer = ""; // referer info to pass
    var $cookies = array(); // array of cookies to pass
    // $cookies["username"]="joe";
    var $rawheaders = array(); // array of raw headers to send
    // $rawheaders["Content-type"]="text/html";

    var $maxredirs = 5; // http redirection depth maximum. 0 = disallow
    var $lastredirectaddr = ""; // contains address of last redirected address
    var $offsiteok = true; // allows redirection off-site
    var $maxframes = 0; // frame content depth maximum. 0 = disallow
    var $expandlinks = true; // expand links to fully qualified URLs.
    // this only applies to fetchlinks()
    // submitlinks(), and submittext()
    var $passcookies = true; // pass set cookies back through redirects
    // NOTE: this currently does not respect
    // dates, domains or paths.

    var $user = ""; // user for http authentication
    var $pass = ""; // password for http authentication

    // http accept types
    var $accept = "image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*";

    var $results = ""; // where the content is put

    var $error = ""; // error messages sent here
    var $response_code = ""; // response code returned from server
    var $headers = array(); // headers returned from server sent here
    var $maxlength = 500000; // max return data length (body)
    var $read_timeout = 0; // timeout on read operations, in seconds
    // supported only since PHP 4 Beta 4
    // set to 0 to disallow timeouts
    var $timed_out = false; // if a read operation timed out
    var $status = 0; // http request status

    var $temp_dir = "/tmp"; // temporary directory that the webserver
    // has permission to write to.
    // under Windows, this should be C:\temp

    var $curl_path = false;
    // deprecated, snoopy no longer uses curl for https requests,
    // but instead requires the openssl extension.

    // send Accept-encoding: gzip?
    var $use_gzip = true;

    // file or directory with CA certificates to verify remote host with
    var $cafile;
    var $capath;

    /**** Private variables ****/

    var $_maxlinelen = 4096; // max line length (headers)

    var $_httpmethod = "GET"; // default http request method
    var $_httpversion = "HTTP/1.0"; // default http request version
    var $_submit_method = "POST"; // default submit method
    var $_submit_type = "application/x-www-form-urlencoded"; // default submit type
    var $_mime_boundary = ""; // MIME boundary for multipart/form-data submit type
    var $_redirectaddr = false; // will be set if page fetched is a redirect
    var $_redirectdepth = 0; // increments on an http redirect
    var $_frameurls = array(); // frame src urls
    var $_framedepth = 0; // increments on frame depth

    var $_isproxy = false; // set if using a proxy server
    var $_fp_timeout = 30; // timeout for socket connection

    /*======================================================================*\
        Function:	fetch
        Purpose:	fetch the contents of a web page
                    (and possibly other protocols in the
                    future like ftp, nntp, gopher, etc.)
        Input:		$URI	the location of the page to fetch
        Output:		$this->results	the output text from the fetch
    \*======================================================================*/

    function fetch($URI)
    {

        $URI_PARTS = parse_url($URI);
        if (!empty($URI_PARTS["user"]))
            $this->user = $URI_PARTS["user"];
        if (!empty($URI_PARTS["pass"]))
            $this->pass = $URI_PARTS["pass"];
        if (empty($URI_PARTS["query"]))
            $URI_PARTS["query"] = '';
        if (empty($URI_PARTS["path"]))
            $URI_PARTS["path"] = '';

        $fp = null;

        switch (strtolower($URI_PARTS["scheme"])) {
            case "https":
                if (!extension_loaded('openssl')) {
                    trigger_error("openssl extension required for HTTPS", E_USER_ERROR);
                    exit;
                }
                $this->port = 443;
            case "http":
                $this->scheme = strtolower($URI_PARTS["scheme"]);
                $this->host = $URI_PARTS["host"];
                if (!empty($URI_PARTS["port"]))
                    $this->port = $URI_PARTS["port"];
                if ($this->_connect($fp)) {
                    if ($this->_isproxy) {
                        // using proxy, send entire URI
                        $this->_httprequest($URI, $fp, $URI, $this->_httpmethod);
                    } else {
                        $path = $URI_PARTS["path"] . ($URI_PARTS["query"] ? "?" . $URI_PARTS["query"] : "");
                        // no proxy, send only the path
                        $this->_httprequest($path, $fp, $URI, $this->_httpmethod);
                    }

                    $this->_disconnect($fp);

                    if ($this->_redirectaddr) {
                        /* url was redirected, check if we've hit the max depth */
                        if ($this->maxredirs > $this->_redirectdepth) {
                            // only follow redirect if it's on this site, or offsiteok is true
                            if (preg_match("|^https?://" . preg_quote($this->host) . "|i", $this->_redirectaddr) || $this->offsiteok) {
                                /* follow the redirect */
                                $this->_redirectdepth++;
                                $this->lastredirectaddr = $this->_redirectaddr;
                                $this->fetch($this->_redirectaddr);
                            }
                        }
                    }

                    if ($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0) {
                        $frameurls = $this->_frameurls;
                        $this->_frameurls = array();

                        while (list(, $frameurl) = each($frameurls)) {
                            if ($this->_framedepth < $this->maxframes) {
                                $this->fetch($frameurl);
                                $this->_framedepth++;
                            } else
                                break;
                        }
                    }
                } else {
                    return false;
                }
                return $this;
                break;
            default:
                // not a valid protocol
                $this->error = 'Invalid protocol "' . $URI_PARTS["scheme"] . '"\n';
                return false;
                break;
        }
        return $this;
    }

    /*======================================================================*\
        Function:	submit
        Purpose:	submit an http(s) form
        Input:		$URI	the location to post the data
                    $formvars	the formvars to use.
                        format: $formvars["var"] = "val";
                    $formfiles  an array of files to submit
                        format: $formfiles["var"] = "/dir/filename.ext";
        Output:		$this->results	the text output from the post
    \*======================================================================*/

    function submit($URI, $formvars = "", $formfiles = "")
    {
        unset($postdata);

        $postdata = $this->_prepare_post_body($formvars, $formfiles);

        $URI_PARTS = parse_url($URI);
        if (!empty($URI_PARTS["user"]))
            $this->user = $URI_PARTS["user"];
        if (!empty($URI_PARTS["pass"]))
            $this->pass = $URI_PARTS["pass"];
        if (empty($URI_PARTS["query"]))
            $URI_PARTS["query"] = '';
        if (empty($URI_PARTS["path"]))
            $URI_PARTS["path"] = '';

        switch (strtolower($URI_PARTS["scheme"])) {
            case "https":
                if (!extension_loaded('openssl')) {
                    trigger_error("openssl extension required for HTTPS", E_USER_ERROR);
                    exit;
                }
                $this->port = 443;
            case "http":
                $this->scheme = strtolower($URI_PARTS["scheme"]);
                $this->host = $URI_PARTS["host"];
                if (!empty($URI_PARTS["port"]))
                    $this->port = $URI_PARTS["port"];
                if ($this->_connect($fp)) {
                    if ($this->_isproxy) {
                        // using proxy, send entire URI
                        $this->_httprequest($URI, $fp, $URI, $this->_submit_method, $this->_submit_type, $postdata);
                    } else {
                        $path = $URI_PARTS["path"] . ($URI_PARTS["query"] ? "?" . $URI_PARTS["query"] : "");
                        // no proxy, send only the path
                        $this->_httprequest($path, $fp, $URI, $this->_submit_method, $this->_submit_type, $postdata);
                    }

                    $this->_disconnect($fp);

                    if ($this->_redirectaddr) {
                        /* url was redirected, check if we've hit the max depth */
                        if ($this->maxredirs > $this->_redirectdepth) {
                            if (!preg_match("|^" . $URI_PARTS["scheme"] . "://|", $this->_redirectaddr))
                                $this->_redirectaddr = $this->_expandlinks($this->_redirectaddr, $URI_PARTS["scheme"] . "://" . $URI_PARTS["host"]);

                            // only follow redirect if it's on this site, or offsiteok is true
                            if (preg_match("|^https?://" . preg_quote($this->host) . "|i", $this->_redirectaddr) || $this->offsiteok) {
                                /* follow the redirect */
                                $this->_redirectdepth++;
                                $this->lastredirectaddr = $this->_redirectaddr;
                                if (strpos($this->_redirectaddr, "?") > 0)
                                    $this->fetch($this->_redirectaddr); // the redirect has changed the request method from post to get
                                else
                                    $this->submit($this->_redirectaddr, $formvars, $formfiles);
                            }
                        }
                    }

                    if ($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0) {
                        $frameurls = $this->_frameurls;
                        $this->_frameurls = array();

                        while (list(, $frameurl) = each($frameurls)) {
                            if ($this->_framedepth < $this->maxframes) {
                                $this->fetch($frameurl);
                                $this->_framedepth++;
                            } else
                                break;
                        }
                    }

                } else {
                    return false;
                }
                return $this;
                break;
            default:
                // not a valid protocol
                $this->error = 'Invalid protocol "' . $URI_PARTS["scheme"] . '"\n';
                return false;
                break;
        }
        return $this;
    }

    /*======================================================================*\
        Function:	fetchlinks
        Purpose:	fetch the links from a web page
        Input:		$URI	where you are fetching from
        Output:		$this->results	an array of the URLs
    \*======================================================================*/

    function fetchlinks($URI)
    {
        if ($this->fetch($URI) !== false) {
            if ($this->lastredirectaddr)
                $URI = $this->lastredirectaddr;
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++)
                    $this->results[$x] = $this->_striplinks($this->results[$x]);
            } else
                $this->results = $this->_striplinks($this->results);

            if ($this->expandlinks)
                $this->results = $this->_expandlinks($this->results, $URI);
            return $this;
        } else
            return false;
    }

    /*======================================================================*\
        Function:	fetchform
        Purpose:	fetch the form elements from a web page
        Input:		$URI	where you are fetching from
        Output:		$this->results	the resulting html form
    \*======================================================================*/

    function fetchform($URI)
    {

        if ($this->fetch($URI) !== false) {

            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++)
                    $this->results[$x] = $this->_stripform($this->results[$x]);
            } else
                $this->results = $this->_stripform($this->results);

            return $this;
        } else
            return false;
    }


    /*======================================================================*\
        Function:	fetchtext
        Purpose:	fetch the text from a web page, stripping the links
        Input:		$URI	where you are fetching from
        Output:		$this->results	the text from the web page
    \*======================================================================*/

    function fetchtext($URI)
    {
        if ($this->fetch($URI) !== false) {
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++)
                    $this->results[$x] = $this->_striptext($this->results[$x]);
            } else
                $this->results = $this->_striptext($this->results);
            return $this;
        } else
            return false;
    }

    /*======================================================================*\
        Function:	submitlinks
        Purpose:	grab links from a form submission
        Input:		$URI	where you are submitting from
        Output:		$this->results	an array of the links from the post
    \*======================================================================*/

    function submitlinks($URI, $formvars = "", $formfiles = "")
    {
        if ($this->submit($URI, $formvars, $formfiles) !== false) {
            if ($this->lastredirectaddr)
                $URI = $this->lastredirectaddr;
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++) {
                    $this->results[$x] = $this->_striplinks($this->results[$x]);
                    if ($this->expandlinks)
                        $this->results[$x] = $this->_expandlinks($this->results[$x], $URI);
                }
            } else {
                $this->results = $this->_striplinks($this->results);
                if ($this->expandlinks)
                    $this->results = $this->_expandlinks($this->results, $URI);
            }
            return $this;
        } else
            return false;
    }

    /*======================================================================*\
        Function:	submittext
        Purpose:	grab text from a form submission
        Input:		$URI	where you are submitting from
        Output:		$this->results	the text from the web page
    \*======================================================================*/

    function submittext($URI, $formvars = "", $formfiles = "")
    {
        if ($this->submit($URI, $formvars, $formfiles) !== false) {
            if ($this->lastredirectaddr)
                $URI = $this->lastredirectaddr;
            if (is_array($this->results)) {
                for ($x = 0; $x < count($this->results); $x++) {
                    $this->results[$x] = $this->_striptext($this->results[$x]);
                    if ($this->expandlinks)
                        $this->results[$x] = $this->_expandlinks($this->results[$x], $URI);
                }
            } else {
                $this->results = $this->_striptext($this->results);
                if ($this->expandlinks)
                    $this->results = $this->_expandlinks($this->results, $URI);
            }
            return $this;
        } else
            return false;
    }


    /*======================================================================*\
        Function:	set_submit_multipart
        Purpose:	Set the form submission content type to
                    multipart/form-data
    \*======================================================================*/
    function set_submit_multipart()
    {
        $this->_submit_type = "multipart/form-data";
        return $this;
    }


    /*======================================================================*\
        Function:	set_submit_normal
        Purpose:	Set the form submission content type to
                    application/x-www-form-urlencoded
    \*======================================================================*/
    function set_submit_normal()
    {
        $this->_submit_type = "application/x-www-form-urlencoded";
        return $this;
    }




    /*======================================================================*\
        Private functions
    \*======================================================================*/


    /*======================================================================*\
        Function:	_striplinks
        Purpose:	strip the hyperlinks from an html document
        Input:		$document	document to strip.
        Output:		$match		an array of the links
    \*======================================================================*/

    function _striplinks($document)
    {
        preg_match_all("'<\s*a\s.*?href\s*=\s*			# find <a href=
						([\"\'])?					# find single or double quote
						(?(1) (.*?)\\1 | ([^\s\>]+))		# if quote found, match up to next matching
													# quote, otherwise match up to next space
						'isx", $document, $links);


        // catenate the non-empty matches from the conditional subpattern

        while (list($key, $val) = each($links[2])) {
            if (!empty($val))
                $match[] = $val;
        }

        while (list($key, $val) = each($links[3])) {
            if (!empty($val))
                $match[] = $val;
        }

        // return the links
        return $match;
    }

    /*======================================================================*\
        Function:	_stripform
        Purpose:	strip the form elements from an html document
        Input:		$document	document to strip.
        Output:		$match		an array of the links
    \*======================================================================*/

    function _stripform($document)
    {
        preg_match_all("'<\/?(FORM|INPUT|SELECT|TEXTAREA|(OPTION))[^<>]*>(?(2)(.*(?=<\/?(option|select)[^<>]*>[\r\n]*)|(?=[\r\n]*))|(?=[\r\n]*))'Usi", $document, $elements);

        // catenate the matches
        $match = implode("\r\n", $elements[0]);

        // return the links
        return $match;
    }


    /*======================================================================*\
        Function:	_striptext
        Purpose:	strip the text from an html document
        Input:		$document	document to strip.
        Output:		$text		the resulting text
    \*======================================================================*/

    function _striptext($document)
    {

        // I didn't use preg eval (//e) since that is only available in PHP 4.0.
        // so, list your entities one by one here. I included some of the
        // more common ones.

        $search = array("'<script[^>]*?>.*?</script>'si", // strip out javascript
            "'<[\/\!]*?[^<>]*?>'si", // strip out html tags
            "'([\r\n])[\s]+'", // strip out white space
            "'&(quot|#34|#034|#x22);'i", // replace html entities
            "'&(amp|#38|#038|#x26);'i", // added hexadecimal values
            "'&(lt|#60|#060|#x3c);'i",
            "'&(gt|#62|#062|#x3e);'i",
            "'&(nbsp|#160|#xa0);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&(reg|#174);'i",
            "'&(deg|#176);'i",
            "'&(#39|#039|#x27);'",
            "'&(euro|#8364);'i", // europe
            "'&a(uml|UML);'", // german
            "'&o(uml|UML);'",
            "'&u(uml|UML);'",
            "'&A(uml|UML);'",
            "'&O(uml|UML);'",
            "'&U(uml|UML);'",
            "'&szlig;'i",
        );
        $replace = array("",
            "",
            "\\1",
            "\"",
            "&",
            "<",
            ">",
            " ",
            chr(161),
            chr(162),
            chr(163),
            chr(169),
            chr(174),
            chr(176),
            chr(39),
            chr(128),
            "ä",
            "ö",
            "ü",
            "Ä",
            "Ö",
            "Ü",
            "ß",
        );

        $text = preg_replace($search, $replace, $document);

        return $text;
    }

    /*======================================================================*\
        Function:	_expandlinks
        Purpose:	expand each link into a fully qualified URL
        Input:		$links			the links to qualify
                    $URI			the full URI to get the base from
        Output:		$expandedLinks	the expanded links
    \*======================================================================*/

    function _expandlinks($links, $URI)
    {

        preg_match("/^[^\?]+/", $URI, $match);

        $match = preg_replace("|/[^\/\.]+\.[^\/\.]+$|", "", $match[0]);
        $match = preg_replace("|/$|", "", $match);
        $match_part = parse_url($match);
        $match_root =
            $match_part["scheme"] . "://" . $match_part["host"];

        $search = array("|^http://" . preg_quote($this->host) . "|i",
            "|^(\/)|i",
            "|^(?!http://)(?!mailto:)|i",
            "|/\./|",
            "|/[^\/]+/\.\./|"
        );

        $replace = array("",
            $match_root . "/",
            $match . "/",
            "/",
            "/"
        );

        $expandedLinks = preg_replace($search, $replace, $links);

        return $expandedLinks;
    }

    /*======================================================================*\
        Function:	_httprequest
        Purpose:	go get the http(s) data from the server
        Input:		$url		the url to fetch
                    $fp			the current open file pointer
                    $URI		the full URI
                    $body		body contents to send if any (POST)
        Output:
    \*======================================================================*/

    function _httprequest($url, $fp, $URI, $http_method, $content_type = "", $body = "")
    {
        $cookie_headers = '';
        if ($this->passcookies && $this->_redirectaddr)
            $this->setcookies();

        $URI_PARTS = parse_url($URI);
        if (empty($url))
            $url = "/";
        $headers = $http_method . " " . $url . " " . $this->_httpversion . "\r\n";
        if (!empty($this->host) && !isset($this->rawheaders['Host'])) {
            $headers .= "Host: " . $this->host;
            if (!empty($this->port) && $this->port != '80')
                $headers .= ":" . $this->port;
            $headers .= "\r\n";
        }
        if (!empty($this->agent))
            $headers .= "User-Agent: " . $this->agent . "\r\n";
        if (!empty($this->accept))
            $headers .= "Accept: " . $this->accept . "\r\n";
        if ($this->use_gzip) {
            // make sure PHP was built with --with-zlib
            // and we can handle gzipp'ed data
            if (function_exists('gzinflate')) {
                $headers .= "Accept-encoding: gzip\r\n";
            } else {
                trigger_error(
                    "use_gzip is on, but PHP was built without zlib support." .
                    "  Requesting file(s) without gzip encoding.",
                    E_USER_NOTICE);
            }
        }
        if (!empty($this->referer))
            $headers .= "Referer: " . $this->referer . "\r\n";
        if (!empty($this->cookies)) {
            if (!is_array($this->cookies))
                $this->cookies = (array)$this->cookies;

            reset($this->cookies);
            if (count($this->cookies) > 0) {
                $cookie_headers .= 'Cookie: ';
                foreach ($this->cookies as $cookieKey => $cookieVal) {
                    $cookie_headers .= $cookieKey . "=" . urlencode($cookieVal) . "; ";
                }
                $headers .= substr($cookie_headers, 0, -2) . "\r\n";
            }
        }
        if (!empty($this->rawheaders)) {
            if (!is_array($this->rawheaders))
                $this->rawheaders = (array)$this->rawheaders;
            while (list($headerKey, $headerVal) = each($this->rawheaders))
                $headers .= $headerKey . ": " . $headerVal . "\r\n";
        }
        if (!empty($content_type)) {
            $headers .= "Content-type: $content_type";
            if ($content_type == "multipart/form-data")
                $headers .= "; boundary=" . $this->_mime_boundary;
            $headers .= "\r\n";
        }
        if (!empty($body))
            $headers .= "Content-length: " . strlen($body) . "\r\n";
        if (!empty($this->user) || !empty($this->pass))
            $headers .= "Authorization: Basic " . base64_encode($this->user . ":" . $this->pass) . "\r\n";

        //add proxy auth headers
        if (!empty($this->proxy_user))
            $headers .= 'Proxy-Authorization: ' . 'Basic ' . base64_encode($this->proxy_user . ':' . $this->proxy_pass) . "\r\n";


        $headers .= "\r\n";

        // set the read timeout if needed
        if ($this->read_timeout > 0)
            socket_set_timeout($fp, $this->read_timeout);
        $this->timed_out = false;

        fwrite($fp, $headers . $body, strlen($headers . $body));

        $this->_redirectaddr = false;
        unset($this->headers);

        // content was returned gzip encoded?
        $is_gzipped = false;

        while ($currentHeader = fgets($fp, $this->_maxlinelen)) {
            if ($this->read_timeout > 0 && $this->_check_timeout($fp)) {
                $this->status = -100;
                return false;
            }

            if ($currentHeader == "\r\n")
                break;

            // if a header begins with Location: or URI:, set the redirect
            if (preg_match("/^(Location:|URI:)/i", $currentHeader)) {
                // get URL portion of the redirect
                preg_match("/^(Location:|URI:)[ ]+(.*)/i", chop($currentHeader), $matches);
                // look for :// in the Location header to see if hostname is included
                if (!preg_match("|\:\/\/|", $matches[2])) {
                    // no host in the path, so prepend
                    $this->_redirectaddr = $URI_PARTS["scheme"] . "://" . $this->host . ":" . $this->port;
                    // eliminate double slash
                    if (!preg_match("|^/|", $matches[2]))
                        $this->_redirectaddr .= "/" . $matches[2];
                    else
                        $this->_redirectaddr .= $matches[2];
                } else
                    $this->_redirectaddr = $matches[2];
            }

            if (preg_match("|^HTTP/|", $currentHeader)) {
                if (preg_match("|^HTTP/[^\s]*\s(.*?)\s|", $currentHeader, $status)) {
                    $this->status = $status[1];
                }
                $this->response_code = $currentHeader;
            }

            if (preg_match("/Content-Encoding: gzip/", $currentHeader)) {
                $is_gzipped = true;
            }

            $this->headers[] = $currentHeader;
        }

        $results = '';
        do {
            $_data = fread($fp, $this->maxlength);
            if (strlen($_data) == 0) {
                break;
            }
            $results .= $_data;
        } while (true);

        // gunzip
        if ($is_gzipped) {
            // per http://www.php.net/manual/en/function.gzencode.php
            $results = substr($results, 10);
            $results = gzinflate($results);
        }

        if ($this->read_timeout > 0 && $this->_check_timeout($fp)) {
            $this->status = -100;
            return false;
        }

        // check if there is a a redirect meta tag

        if (preg_match("'<meta[\s]*http-equiv[^>]*?content[\s]*=[\s]*[\"\']?\d+;[\s]*URL[\s]*=[\s]*([^\"\']*?)[\"\']?>'i", $results, $match)) {
            $this->_redirectaddr = $this->_expandlinks($match[1], $URI);
        }

        // have we hit our frame depth and is there frame src to fetch?
        if (($this->_framedepth < $this->maxframes) && preg_match_all("'<frame\s+.*src[\s]*=[\'\"]?([^\'\"\>]+)'i", $results, $match)) {
            $this->results[] = $results;
            for ($x = 0; $x < count($match[1]); $x++)
                $this->_frameurls[] = $this->_expandlinks($match[1][$x], $URI_PARTS["scheme"] . "://" . $this->host);
        } // have we already fetched framed content?
        elseif (is_array($this->results))
            $this->results[] = $results;
        // no framed content
        else
            $this->results = $results;

        return $this;
    }

    /*======================================================================*\
        Function:	setcookies()
        Purpose:	set cookies for a redirection
    \*======================================================================*/

    function setcookies()
    {
        for ($x = 0; $x < count($this->headers); $x++) {
            if (preg_match('/^set-cookie:[\s]+([^=]+)=([^;]+)/i', $this->headers[$x], $match))
                $this->cookies[$match[1]] = urldecode($match[2]);
        }
        return $this;
    }


    /*======================================================================*\
        Function:	_check_timeout
        Purpose:	checks whether timeout has occurred
        Input:		$fp	file pointer
    \*======================================================================*/

    function _check_timeout($fp)
    {
        if ($this->read_timeout > 0) {
            $fp_status = socket_get_status($fp);
            if ($fp_status["timed_out"]) {
                $this->timed_out = true;
                return true;
            }
        }
        return false;
    }

    /*======================================================================*\
        Function:	_connect
        Purpose:	make a socket connection
        Input:		$fp	file pointer
    \*======================================================================*/

    function _connect(&$fp)
    {
        if (!empty($this->proxy_host) && !empty($this->proxy_port)) {
            $this->_isproxy = true;

            $host = $this->proxy_host;
            $port = $this->proxy_port;

            if ($this->scheme == 'https') {
                trigger_error("HTTPS connections over proxy are currently not supported", E_USER_ERROR);
                exit;
            }
        } else {
            $host = $this->host;
            $port = $this->port;
        }

        $this->status = 0;

        $context_opts = array();

        if ($this->scheme == 'https') {
            // if cafile or capath is specified, enable certificate
            // verification (including name checks)
            if (isset($this->cafile) || isset($this->capath)) {
                $context_opts['ssl'] = array(
                    'verify_peer' => true,
                    'CN_match' => $this->host,
                    'disable_compression' => true,
                );

                if (isset($this->cafile))
                    $context_opts['ssl']['cafile'] = $this->cafile;
                if (isset($this->capath))
                    $context_opts['ssl']['capath'] = $this->capath;
            }
                    
            $host = 'ssl://' . $host;
        }

        $context = stream_context_create($context_opts);

        if (version_compare(PHP_VERSION, '5.0.0', '>')) {
            if($this->scheme == 'http')
                $host = "tcp://" . $host;
            $fp = stream_socket_client(
                "$host:$port",
                $errno,
                $errmsg,
                $this->_fp_timeout,
                STREAM_CLIENT_CONNECT,
                $context);
        } else {
            $fp = fsockopen(
                $host,
                $port,
                $errno,
                $errstr,
                $this->_fp_timeout,
                $context);
        }

        if ($fp) {
            // socket connection succeeded
            return true;
        } else {
            // socket connection failed
            $this->status = $errno;
            switch ($errno) {
                case -3:
                    $this->error = "socket creation failed (-3)";
                case -4:
                    $this->error = "dns lookup failure (-4)";
                case -5:
                    $this->error = "connection refused or timed out (-5)";
                default:
                    $this->error = "connection failed (" . $errno . ")";
            }
            return false;
        }
    }

    /*======================================================================*\
        Function:	_disconnect
        Purpose:	disconnect a socket connection
        Input:		$fp	file pointer
    \*======================================================================*/

    function _disconnect($fp)
    {
        return (fclose($fp));
    }


    /*======================================================================*\
        Function:	_prepare_post_body
        Purpose:	Prepare post body according to encoding type
        Input:		$formvars  - form variables
                    $formfiles - form upload files
        Output:		post body
    \*======================================================================*/

    function _prepare_post_body($formvars, $formfiles)
    {
        settype($formvars, "array");
        settype($formfiles, "array");
        $postdata = '';

        if (count($formvars) == 0 && count($formfiles) == 0)
            return;

        switch ($this->_submit_type) {
            case "application/x-www-form-urlencoded":
                reset($formvars);
                while (list($key, $val) = each($formvars)) {
                    if (is_array($val) || is_object($val)) {
                        while (list($cur_key, $cur_val) = each($val)) {
                            $postdata .= urlencode($key) . "[]=" . urlencode($cur_val) . "&";
                        }
                    } else
                        $postdata .= urlencode($key) . "=" . urlencode($val) . "&";
                }
                break;

            case "multipart/form-data":
                $this->_mime_boundary = "Snoopy" . md5(uniqid(microtime()));

                reset($formvars);
                while (list($key, $val) = each($formvars)) {
                    if (is_array($val) || is_object($val)) {
                        while (list($cur_key, $cur_val) = each($val)) {
                            $postdata .= "--" . $this->_mime_boundary . "\r\n";
                            $postdata .= "Content-Disposition: form-data; name=\"$key\[\]\"\r\n\r\n";
                            $postdata .= "$cur_val\r\n";
                        }
                    } else {
                        $postdata .= "--" . $this->_mime_boundary . "\r\n";
                        $postdata .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
                        $postdata .= "$val\r\n";
                    }
                }

                reset($formfiles);
                while (list($field_name, $file_names) = each($formfiles)) {
                    settype($file_names, "array");
                    while (list(, $file_name) = each($file_names)) {
                        if (!is_readable($file_name)) continue;

                        $fp = fopen($file_name, "r");
                        $file_content = fread($fp, filesize($file_name));
                        fclose($fp);
                        $base_name = basename($file_name);

                        $postdata .= "--" . $this->_mime_boundary . "\r\n";
                        $postdata .= "Content-Disposition: form-data; name=\"$field_name\"; filename=\"$base_name\"\r\n\r\n";
                        $postdata .= "$file_content\r\n";
                    }
                }
                $postdata .= "--" . $this->_mime_boundary . "--\r\n";
                break;
        }

        return $postdata;
    }

    /*======================================================================*\
    Function:	getResults
    Purpose:	return the results of a request
    Output:		string results
    \*======================================================================*/

    function getResults()
    {
        return $this->results;
    }
}

?>