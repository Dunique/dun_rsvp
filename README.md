# Dun RSVP Plus
De BCA Module

## Installation

Simpel als het is, gewoon installeren die hap.

## Template Tags

### Form Tag
```html
{exp:dun_rsvp:form entry_id="1"}
```
```html
{exp:dun_rsvp:form entry_id="1"}
    <div class="rsvp_form">
    	{if logged_in}
    		{if rsvp_seats > 0}
    			<p>You have already responded to this event.</p>
    			<p><strong>Edit your response:</strong></p>
    		{if:elseif total_seats > 0 AND total_seats_remaining == 0}
    			<p>Sorry, this event has sold out!</p>
    		{if:else}
    			<p><strong>Respond to this event:</strong></p>
    			{if total_seats > 0}
    				{if total_seats_remaining == 1}
    					<p>Hurry, there is only 1 seat remaining!</p>
    				{if:elseif total_seats_remaining <= 10}
    					<p>Hurry, there are only {total_seats_remaining} seats remaining!</p>
    				{/if}
    			{/if}
    		{/if}
    		{if total_seats == 0 OR rsvp_seats > 0 OR total_seats_remaining > 0}
    			<label for="rsvp_seats">Seats Required</label>
    			<select name="rsvp_seats">
    				<option value="1" {if rsvp_seats <= 1} selected="selected"{/if}>1</option>
    				{if total_seats == 0 OR total_seats_remaining >= 2}<option value="2" {if rsvp_seats == 2} selected="selected"{/if}>2</option>{/if}
    				{if total_seats == 0 OR total_seats_remaining >= 3}<option value="3" {if rsvp_seats == 3} selected="selected"{/if}>3</option>{/if}
    				{if total_seats == 0 OR total_seats_remaining >= 4}<option value="4" {if rsvp_seats == 4} selected="selected"{/if}>4</option>{/if}
    				{if total_seats == 0 OR total_seats_remaining >= 5}<option value="5" {if rsvp_seats == 5} selected="selected"{/if}>5</option>{/if}
    			</select><br />
    
               	{rsvp_fields}
    				<label for="{field_name}">{field_label}</label><br />
    				 <input type="text" name="{field_name}" value="{field_value}" /><br />
    			{/rsvp_fields}
    		   	
    			<input name="rsvp_public" type="checkbox" value="y" {if rsvp_public == "y"}checked="checked"{/if} />
    			<label for="rsvp_public">Make my attendance status public</label><br />
    			{if rsvp_seats > 0}<input type="submit" name="rsvp_cancel" value="Cancel my RSVP" />{/if}
    			<input type="submit" name="rsvp_submit" value="{if rsvp_seats > 0}Update RSVP{if:else}Send RSVP{/if}" />
    		{/if}
    	{if:else}
    		<p>Please log in or register to respond to this event.</p>
    	{/if}
    </div>
{/exp:dun_rsvp:form}
```
### Form invite non member Tag
```html
{exp:dun_rsvp:form_invite_non_member}
```
```html
{exp:dun_rsvp:form_invite_non_member}
    <div class="rsvp_form">
    	{if logged_in}
    		{if total_seats == 0 OR rsvp_seats > 0 OR total_seats_remaining > 0}
    			<textarea name="notes"></textarea>
    			<input type="submit" name="rsvp_submit" value="Invite" />
    		{/if}
    	{if:else}
    		<p>Please log in or register to respond to this event.</p>
    	{/if}
    </div>
{/exp:dun_rsvp:form_invite_non_member}
```

### Attendance Tag
```html
{exp:dun_rsvp:attendance entry_id="12"}
    {attendee_[custom_field]}
    {attendee_count}
    {attendee_total_results}
    {no_attendance}{/no_attendance}
{/exp:dun_rsvp:attendance}
```