<?php
	// event details
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(lang('rsvp_plus_event_details'), '');

	$this->table->add_row(lang('event_entry_id'), $event['entry_id']);
	$this->table->add_row(lang('event_title'), $event['title']);
	$this->table->add_row(lang('event_date'), $this->localize->set_human_time($event['entry_date']));
	$this->table->add_row(lang('event_seats_available'), $event['total_seats'] == 0 ? 'unlimited' : $event['total_seats']);
	$this->table->add_row(lang('event_seats_reserved'),
			sprintf(lang('event_seats_reserved_format'),
				$event['total_seats_reserved'],
				$event['total_members_responded'],
				$event['total_seats'] == 0 ? 'unlimited' : $event['total_seats_remaining'],
				$event['total_seats']));
    $this->table->add_row(lang('event_invitation_decline'), $event['total_members_declined']);
	
	if(ee()->dun_rsvp_settings->item('enable_non_member_invites'))
	{
		$this->table->add_row(lang('total_seats_reserved_non_member'), '<a href="'.$view_non_member_invites_link.'">'.$event_non_member_invites.'</a>');
	}
	

	echo $this->table->generate();

?>

<div style="padding: 5px 0 15px 0;">
	
	<?php if(in_array('rsvp_plus_edit_entry', ee()->dun_rsvp_settings->item('button_rights'))):?>
		<a title="<?= lang('rsvp_plus_edit_entry'); ?>" class="submit" href="<?= $edit_entry_link; ?>"><?= lang('rsvp_plus_edit_entry'); ?></a>
	<?php endif;?>
	<?php if(in_array('rsvp_plus_attendance_export', ee()->dun_rsvp_settings->item('button_rights'))):?>
		<a title="<?= lang('rsvp_plus_attendance_export'); ?>" class="submit" href="<?= $attendance_export_link; ?>"><?= lang('rsvp_plus_attendance_export'); ?></a>
	<?php endif;?>
	<?php if(in_array('rsvp_plus_email_attendees', ee()->dun_rsvp_settings->item('button_rights'))):?>
		<a title="<?= lang('rsvp_plus_email_attendees'); ?>" class="submit" href="<?= $email_link; ?>"><?= lang('rsvp_plus_email_attendees'); ?></a>
	<?php endif;?>
	<?php if(in_array('rsvp_plus_email_event', ee()->dun_rsvp_settings->item('button_rights'))):?>
		<a title="<?= lang('rsvp_plus_email_event'); ?>" class="submit" href="<?= $email_event; ?>"><?= lang('rsvp_plus_email_event'); ?></a>
	<?php endif;?>
	<?php if(in_array('rsvp_plus_email_reminder', ee()->dun_rsvp_settings->item('button_rights'))):?>
		<a title="<?= lang('rsvp_plus_email_reminder'); ?>" class="submit" href="<?= $email_event; ?>"><?= lang('rsvp_plus_email_reminder'); ?></a>
	<?php endif;?>
	<?php if(in_array('rsvp_plus_test_email_event', ee()->dun_rsvp_settings->item('button_rights'))):?>
		<a title="<?= lang('rsvp_plus_test_email_event'); ?>" class="submit" href="<?= $email_test_event; ?>"><?= lang('rsvp_plus_test_email_event'); ?></a>
	<?php endif;?>
	<?php if(in_array('rsvp_plus_add_member', ee()->dun_rsvp_settings->item('button_rights'))):?>
		<a title="<?= lang('rsvp_plus_add_member'); ?>" class="submit" href="<?= $add_member; ?>"><?= lang('rsvp_plus_add_member'); ?></a>
	<?php endif;?>
	

</div>

<?php
	// attendance list
	$this->table->clear();
	$this->table->set_template($cp_table_template);
	
	$this->table->set_heading(array_merge(array('ID', 'screen name', 'email', lang('seats_reserved')), $fields, array('delete')));

	foreach ($attendance as $key => $user)
	{
		//$user['response_id']
		$row = array($key+1, $user['screen_name'], $user['email'], $user['seats_reserved']);
		foreach($fields as $field)
		{
			$row[] = isset($user[$field])? $user[$field] : '';
		}
		$row[] = '<a href="'.ee()->dun_rsvp_settings->item('base_url').'&method=delete_attendee&entry_id='.$event['entry_id'].'&member_id='.$user['member_id'].'">'.lang('delete').'</a>';
		$this->table->add_row($row);
	}

	echo $this->table->generate();
?>
<?= $pagination ?>