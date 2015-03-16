<div class="clear_left">&nbsp;</div>
<div id="save_settings">
    <?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.DUN_RSVP_MAP.AMP.'method=settings')?>
    
    <?php
    $this->table->set_template($cp_pad_table_template);
    $this->table->set_heading(
        array('data' => lang(DUN_RSVP_MAP.'_preference'), 'style' => 'width:25%;'),
        lang(DUN_RSVP_MAP.'_setting')
    );
    foreach ($settings['default'] as $key => $val)
    {
    	//subtext
    	$subtext = '';
        $extra_html = '';
    	if(is_array($val))
    	{
    	    $subtext = isset($val[1]) ? '<div class="subtext">'.$val[1].'</div>' : '' ;
            $extra_html = isset($val[2]) ? '<div class="extra_html">'.$val[2].'</div>' : '' ;
            $val = $val[0];
    	}
        $this->table->add_row(lang($key, $key).$subtext, $val.$extra_html);
    }
    echo $this->table->generate();
    ?>
    
    
    <p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
    <?php $this->table->clear()?>
    <?=form_close()?>
</div>