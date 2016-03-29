<?=form_open($email_test_event)?>

<div class="tableFooter">
	<p>Optioneel kan er ook een bericht worden toegevoegd aan de uitnodiging</p>
	<p><?=form_textarea(array('name' => 'email_message', 'placeholder' => lang('optioneel bericht')))?></p>
	<div class="tableSubmit">
       <br/> <?=form_submit(array('name' => 'submit', 'value' => lang('Verstuur'), 'class' => 'submit'))?>
    </div>
</div>

<?=form_close()?>
