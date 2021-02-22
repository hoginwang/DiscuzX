<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
?>
<br />
<div style="text-align: center;">
	<?php echo adshow('footerbanner//1').adshow('footerbanner//2').adshow('footerbanner//3'); ?>
	<div id="footer">
		Powered by <strong><a target="_blank" href="http://www.discuz.net">Discuz! <?php echo $_G['setting']['version']; ?> Archiver</a></strong> &nbsp; <?php echo lang('template', 'copyright'); ?>
		<br />
		<br />
	</div>
</div>
</body>
</html>
<?php output(); ?>