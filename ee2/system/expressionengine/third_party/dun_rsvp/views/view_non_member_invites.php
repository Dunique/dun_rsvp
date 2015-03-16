<div class="clear_left">&nbsp;</div>
<div id="save_settings">
    <?php
    $this->table->set_template($cp_pad_table_template);
    $this->table->set_heading(
        array('data' => 'name', 'style' => 'width:25%;'),
        array('data' => 'email', 'style' => 'width:25%;'),
        array('data' => 'invited_by', 'style' => 'width:25%;'),
        'delete'
    );

    foreach ($invites as $key => $val)
    {
        $this->table->add_row($val['name'], $val['email'], $val['invited_by'], '<a href="'.$delete_link.$val['invite_non_member_id'].'">Delete</a>');
    }
    echo $this->table->generate();
    ?>
    
    <?php $this->table->clear()?>

</div>