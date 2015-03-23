<?php
	$base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.DUN_RSVP_MAP.AMP;
?>

<?php if (!isset($_POST['tbl_offset']) && !isset($_POST['tbl_sort'])):?>
	<div class="clear_left">&nbsp;</div>
	<p>
		<span class="button" style="float:right;"><a href="<?=$base_url?>method=add_new_field" class="less_important_bttn">Nieuw veld toevoegen</a></span>
	<div class="clear"></div>
</p>
<?php endif;?>


<?php
$this->table->set_empty(lang(DUN_RSVP_MAP.'_nodata'));
$this->table->set_template($cp_table_template);

$this->table->set_columns($table_headers);
$data = $this->table->datasource('_datasource_fields');
echo $data['table_html'];
echo $data['pagination_html'];
?>