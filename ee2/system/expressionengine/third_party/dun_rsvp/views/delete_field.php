<?php 
 $base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.DUN_RSVP_MAP.AMP.'method=delete_field'.AMP.'field='.$_GET['field'];
?>

<div class="clear_left">&nbsp;</div>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.DUN_RSVP_MAP.AMP.'method=delete_field'.AMP.'field='.$_GET['field'])?>

	<p><strong>Weet je zeker dat je dit veld wilt verwijderen.</strong></p>
	<p class="notice">Deze actie kan niet ongedaan worden</p>

	<input type="submit" class="submit" value="<?=lang('delete')?>" name="submit">
	</p>
</form>
