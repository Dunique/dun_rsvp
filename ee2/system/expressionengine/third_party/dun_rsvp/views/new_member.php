<style>
	.api_settings {
		border-bottom: 1px solid #D0D7DF;
		border-left: 1px solid #D0D7DF;
	}
</style>

<script>
	$(function(){
		var new_tr = false;
		var settings = '';

		$('input[value="entry"]').parent().find('input').click(function(){
			
			if($(this).is(':checked')) {

				switch($(this).val()) {
					case 'entry' : 
						/*$.getJSON("<?=$ajax_url?>", {'function':'get_channels','member_id':$('[name="member_id"]').val()},function(e){

						});
						new_tr = true;*/
					break;

				}

				if(new_tr) {
					$(this).parent().parent().after('<tr id="api_setting_'+$(this).val()+'"><td><b>'+$(this).val()+' settings</b></td><td>'+settings+'</td></tr>');
				}

				
			} else {$('#api_setting_'+$(this).val()).remove();}
			
			//reset the odd even classes
			$('.mainTable tr').removeClass('odd even');
			$('.mainTable tr:odd').addClass('odd');
			$('.mainTable tr:even').addClass('even');
		});
	});
</script>


<div class="clear_left">&nbsp;</div>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP.'method=add_member')?>

<?php
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('channel_preference'), 'style' => 'width:50%;'),
    lang('setting')
);

//build the checkbox
$services = '';
foreach($connection_services as $key=>$val)
{
	$services .= '<span>'.form_checkbox('connection_services[]', $key).' '.$val.'</span> <i><u>('.($urls[$key]).')</u></i>&nbsp;&nbsp;&nbsp;&nbsp;<br/>';
}
$api = '';
foreach($apis as $key=>$val)
{
	$api .= form_checkbox('api[]', $key).' '.$val.'&nbsp;&nbsp;&nbsp;';
}

$this->table->add_row(lang('Member', 'member'), form_dropdown( 'member_id', $members, ''));
$this->table->add_row(lang('connection_type', 'connection_type'), $services);
$this->table->add_row(lang('apis', 'apis'), $api);
$this->table->add_row(lang('active', 'active'), form_dropdown( 'active', $active, ''));
$this->table->add_row(lang('log', 'log').'<div class="subtext"><a href="http://devot-ee.com/add-ons/omnilog" target="_blank">Omnilog</a> required for the logging of the services.</div>', form_dropdown( 'logging', $logging, ''));
//$this->table->add_row(lang('debug', 'debug'), form_dropdown( 'debug', $debug, '')); //@tmp disabled, not yet implemented

echo $this->table->generate();
?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
<?php $this->table->clear()?>
<?=form_close()?>