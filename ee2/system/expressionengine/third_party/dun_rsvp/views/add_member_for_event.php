<?=form_open($add_member_for_event_url)?>

<div class="tableFooter">
	<p>Geef de gegevens op om een gebruiker toe te voegen.</p>
	<p><strong><?=$error?></strong></p>
	<p><?=$users?></p>
	<p><?=$seats_reserved?></p>
	<?php foreach(ee()->dun_rsvp_lib->get_field_fields() as $field):?>
	<p><?=form_label($field, 'fields['.$field.']').'<br/>'.form_input(array('name' => 'fields['.$field.']', 'placeholder' => $field))?></p>
	<?php endforeach;?>	
		

    <div class="tableSubmit">
		<br/>
		<?php if($count > 0):?>
       		<?=form_submit(array('name' => 'submit', 'value' => lang('Verstuur'), 'class' => 'submit'))?>
		<?php endif;?>
    </div>

    
</div>

<?=form_close()?>
