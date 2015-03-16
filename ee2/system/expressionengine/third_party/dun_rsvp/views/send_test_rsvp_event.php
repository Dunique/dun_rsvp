<?=form_open($email_test_event)?>

<div class="tableFooter">
	<p>Geeft je e-mailadres op waar de test e-mail naar toe gestuurd mag worden.</p>
	<strong><?=$email_require?></strong>
	<p><?=form_input(array('name' => 'email', 'placeholder' => lang('email')))?></p>
	<p><?=form_input(array('name' => 'name', 'placeholder' => lang('name')))?></p>
	<div class="tableSubmit">
       <br/> <?=form_submit(array('name' => 'submit', 'value' => lang('Verstuur'), 'class' => 'submit'))?>
    </div>

    
</div>

<?=form_close()?>
