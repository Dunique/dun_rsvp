<?php 
 $base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP;
?>

<div class="clear_left">&nbsp;</div>

<form action="<?=$base_url?>method=delete_member" method="post">
	<input type="hidden" name="confirm" value="ok"/>
	<input type="hidden" name="entry_api_id" value="<?=$entry_api_id?>"/>
	<input type="hidden" name="XID" value="<?=XID_SECURE_HASH?>"/>

	<p><strong><?=lang('entry_api_delete_check')?></strong></p>
	<p>Member: <?=$member->username?></p>
	<p class="notice"><?=lang('entry_api_delete_check_notice')?></p>

	<input type="submit" class="submit" value="<?=lang('delete')?>" name="submit">
	</p>
</form>
