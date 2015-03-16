# Dun RSVP Plus
De BCA Module

## Installation

Simpel als het is, gewoon installeren die hap.

## Template Tags

### Form Tag
```html
{exp:dun_rsvp:form}
```
### Form invite non member Tag
```html
{exp:dun_rsvp:form_invite_non_member}
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