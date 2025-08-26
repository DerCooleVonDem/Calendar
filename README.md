# Calendar Plugin

## Overview
The Calendar plugin allows server administrators to schedule events at specific dates and times. Users can view upcoming events using the `/calendar view` command, and view past events with a separate button. The system is form-based, with an admin-only editor accessible via `/calendar edit`.

## Features
- Schedule server events with title, description, date, time, and location
- View upcoming and past events
- Admin-only event editor for creating, editing, and deleting events
- Configurable settings
- Optional event notification when players join the server
- Form-based interface using CoreAPI

## Installation
1. Make sure you have [CoreAPI](https://github.com/DerCooleVonDem/CoreAPI) installed
2. Place the Calendar plugin in your server's `plugins` folder
3. Restart your server
4. Configure the plugin settings (optional)

## Usage

### For Users
- `/calendar view` - View upcoming and past events

### For Administrators
- `/calendar edit` - Open the event editor to create, edit, or delete events
- `/calendar settings` - Configure plugin settings

## Commands

| Command | Description | Permission |
|---------|-------------|------------|
| `/calendar` | Main command to access the calendar | `calendar.command` |
| `/calendar view` | View upcoming and past events | `calendar.command` |
| `/calendar edit` | Open the event editor | `calendar.edit` |
| `/calendar settings` | Configure plugin settings | `calendar.edit` |

## Permissions

| Permission | Description | Default |
|------------|-------------|---------|
| `calendar.command` | Allows access to the calendar and viewing events | `true` |
| `calendar.edit` | Allows editing events and configuring settings | `op` |

## Configuration
The plugin's configuration file (`config.yml`) contains the following options:

```yaml
# Whether to show the current/upcoming event when a player joins
show_on_join: false

# Maximum number of events to display in the list
max_events_display: 10
```

## Event Management

### Creating an Event
1. Run `/calendar edit`
2. Click "Create New Event"
3. Fill in the event details:
   - Title: The name of the event
   - Description: Details about the event
   - Date: The date and time of the event (format: YYYY-MM-DD HH:MM)
   - Location: Where the event will take place
   - Active: Whether the event is active or not
4. Submit the form to create the event

### Editing an Event
1. Run `/calendar edit`
2. Click "Edit Existing Event"
3. Select the event you want to edit
4. Modify the event details
5. Submit the form to save the changes

### Deleting an Event
1. Run `/calendar edit`
2. Click "Delete Event"
3. Select the event you want to delete
4. Confirm the deletion

## Viewing Events

### Viewing Upcoming Events
1. Run `/calendar` or `/calendar view`
2. Click "Upcoming Events"
3. Select an event to view its details

### Viewing Past Events
1. Run `/calendar` or `/calendar view`
2. Click "Past Events"
3. Select an event to view its details

## Configuring Settings

### Enabling Event Notifications on Join
1. Run `/calendar settings`
2. Toggle "Show Event on Join" to enable/disable notifications
3. Submit the form to save the settings

### Setting Maximum Events to Display
1. Run `/calendar settings`
2. Enter a number for "Max Events to Display"
3. Submit the form to save the settings

## Dependencies
- CoreAPI - Required for commands, forms, and other functionality

## Support
If you encounter any issues or have suggestions for improvements, please report them on the [GitHub repository](https://github.com/JonasWindmann/Calendar/issues).

## License
This plugin is licensed under the MIT License. See the LICENSE file for details.
