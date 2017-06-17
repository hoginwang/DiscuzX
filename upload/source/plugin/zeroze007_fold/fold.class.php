<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_zeroze007_fold {

	var $count = 0;

	function discuzcode($param) {
		global $_G, $pid;
		// 如果内容中没有 fold 的话则不尝试正则匹配
		if (strpos($_G['discuzcodemessage'], '[/fold]') !== false) {
			// 只在处理是discuzcode的时候触发
			if($param['caller'] == 'discuzcode') {
				//$_G['discuzcodemessage'] = preg_replace('/\s?\[fold(=[^\]]*){0,1}\][\n\r]*(.+?)[\n\r]*\[\/fold\]\s?/ies', '$this->_show_fold(\'\\2\', \'\\1\')', $_G['discuzcodemessage']);
				$_G['discuzcodemessage'] = preg_replace_callback('/\s?\[fold(=[^\]]*){0,1}\][\n\r]*(.+?)[\n\r]*\[\/fold\]\s?/is', 'plugin_zeroze007_fold::_show_fold_matches', $_G['discuzcodemessage']);
			} else {
				//$_G['discuzcodemessage'] = preg_replace('/\s?\[fold(=[^\]]*){0,1}\][\n\r]*(.+?)[\n\r]*\[\/fold\]\s?/ies', '', $_G['discuzcodemessage']);
				$_G['discuzcodemessage'] = preg_replace_callback('/\s?\[fold(=[^\]]*){0,1}\][\n\r]*(.+?)[\n\r]*\[\/fold\]\s?/is', create_function('$matches', 'return "";'), $_G['discuzcodemessage']);
			}
		}
	}

	function _show_fold($message, $subject) {
		global $pid;
		
		$this->count ++;
		$subject = ltrim($subject, '=');
		$id = $pid . '_fold' . $this->count;
		$id_s = $pid . '_fold' . $this->count . '_s';
		include template('zeroze007_fold:discuzcode');

		return trim($return);
	}
	
	function _show_fold_matches($matches) {
		return $this->_show_fold($matches[2], $matches[1]);
	}

	function global_footer() {
		$return = "";
		// 仅论坛生效
		if (CURSCRIPT == 'forum') {			
			include template('zeroze007_fold:global_footer');
		}
		return $return;
	}

	
	function global_footer_mobile() {
		return $this->global_footer();
	}
	
}

class plugin_zeroze007_fold_forum extends plugin_zeroze007_fold {

	function post_editorctrl_left() {		
		$str = '<a id="e_fold" class="zeroze007_fold_menu" title="Toggle" href="javascript:;">Toggle</a>';
		return $str;
	}
}
class mobileplugin_zeroze007_fold extends plugin_zeroze007_fold {}
