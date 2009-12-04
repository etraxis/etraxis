<?php

/**
 * Localization
 *
 * This module contains prompts translated in English (US).
 * All the prompts are in ISO-8859-1 encoding.
 *
 * @package Engine
 * @subpackage Localization
 * @author Artem Rodygin
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2004-2009 by Artem Rodygin
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
//--------------------------------------------------------------------------------------------------
//  Author                  Date            Description of modifications
//--------------------------------------------------------------------------------------------------
//  Artem Rodygin           2004-11-17      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-20      new-009: Records filter.
//  Artem Rodygin           2005-07-28      new-012: Records field 'description' should be renamed with 'subject'.
//  Artem Rodygin           2005-07-30      new-018: The 'History' menuitem is useless and should be removed.
//  Artem Rodygin           2005-07-30      new-006: Records search.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-02      new-017: Email notifications filter.
//  Artem Rodygin           2005-08-06      new-019: Fields default values.
//  Artem Rodygin           2005-08-09      new-008: Predefined metrics.
//  Artem Rodygin           2005-08-13      new-020: Clone the records.
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-08-18      bug-034: When record is being postponed, resumed or assigned the confirmations are not displayed.
//  Artem Rodygin           2005-08-18      new-035: Customizable list size.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-08-25      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-08-29      new-068: System settings in 'config.php' should be accessable through web-interface.
//  Artem Rodygin           2005-09-04      bug-085: Members of global groups cannot view project records if they haven't any permissions in the project.
//  Artem Rodygin           2005-09-05      new-090: Add 'Select all' button to project permissions page.
//  Artem Rodygin           2005-09-06      new-094: Record creator should be displayed in general information of record.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-12      new-107: Number of displayed records should be present on the list of records.
//  Artem Rodygin           2005-09-13      new-116: Remove user login from the subject of email notifications.
//  Artem Rodygin           2005-09-15      new-122: User should be able to create a filter to display postponed records only.
//  Artem Rodygin           2005-09-17      new-125: Email notifications advanced filter.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-15      new-153: Users should *always* receieve notifications about records which are created by them or assigned on.
//  Artem Rodygin           2005-10-27      new-169: Append 'add comment' URL to email notifications.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-01-20      new-196: It's not clear that record is postponed when one is being viewed.
//  Artem Rodygin           2006-02-01      bug-208: 'Total records' prompt should be changed to 'Total'.
//  Artem Rodygin           2006-02-10      new-197: Postpone should have a timer for autoresume.
//  Artem Rodygin           2006-02-10      new-210: Hard to find out a prompt corresponding to specified resource ID.
//  Artem Rodygin           2006-03-16      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-05-12      bug-172: Extra long comments are cut when submitted.
//  Artem Rodygin           2006-05-16      new-005: Oracle support.
//  Artem Rodygin           2006-06-19      new-236: Single record subscription.
//  Artem Rodygin           2006-06-25      new-222: Email reminders.
//  Artem Rodygin           2006-06-28      new-272: When reminder is sent a notification should be displayed to user.
//  Artem Rodygin           2006-06-28      new-274: "Crumbs" for creation and modification of filters or subscriptions are not clear.
//  Artem Rodygin           2006-07-24      bug-201: 'Access Forbidden' error with cyrillic named attachments.
//  Artem Rodygin           2006-08-07      bug-300: Cannot login with Active Directory credentials.
//  Artem Rodygin           2006-08-13      new-305: Note with explanation of links to other records should be added where needed.
//  Artem Rodygin           2006-08-20      new-313: Implement HTTP authentication.
//  Artem Rodygin           2006-10-14      new-137: Custom queries.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2006-11-05      new-365: Filters sharing.
//  Artem Rodygin           2006-11-07      new-366: Export to CSV.
//  Artem Rodygin           2006-11-12      new-368: User should be able to subscribe other persons.
//  Artem Rodygin           2006-11-15      bug-381: Attachments of some types are not opened in valid applications.
//  Artem Rodygin           2006-11-15      new-374: Carbon copies in subscriptions.
//  Artem Rodygin           2006-11-18      bug-388: "Configuration" page does not display path where binary attachments are stored.
//  Artem Rodygin           2006-11-18      bug-389: Motorola LDAP server returns "Insufficient rights" error.
//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2006-12-04      new-405: Default filter for new user.
//  Artem Rodygin           2006-12-10      new-432: Maintenance notice banner.
//  Artem Rodygin           2006-12-17      new-457: Default filter for new user.
//  Artem Rodygin           2006-12-20      new-459: 'Filters' and 'Subscriptions' pages should contain ability to clear current selection.
//  Artem Rodygin           2006-12-22      new-462: Postpone date should be displayed as separate field.
//  Artem Rodygin           2006-12-23      new-463: Date field names should be extended with date format explanation.
//  Artem Rodygin           2006-12-27      bug-470: State permissions must not be used when record is being created.
//  Artem Rodygin           2006-12-27      new-472: User must have ability to log out.
//  Artem Rodygin           2006-12-28      new-474: Rename field permissions to make them more clear.
//  Artem Rodygin           2006-12-30      new-475: Turning subscriptions on and off is not clear.
//  Artem Rodygin           2007-01-15      new-483: JavaScript ability notice.
//  Artem Rodygin           2007-04-03      new-512: Banner about 'no reply on autogenerated message' for notifications.
//  Artem Rodygin           2007-04-03      new-499: Records dump to text file.
//  Artem Rodygin           2007-07-02      new-533: Links between records.
//  Artem Rodygin           2007-07-14      new-545: Chart legend is required.
//  Artem Rodygin           2007-07-16      new-546: Confidential comments.
//  Artem Rodygin           2007-07-28      bug-552: Misprint in default filter for created records.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-08-27      [rhonda] Hide author from 'Innovation' templates.
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Artem Rodygin           2007-09-12      new-576: [SF1788286] Export to CSV
//  Artem Rodygin           2007-09-13      new-566: Choose encoding for record dump and export of records list.
//  Artem Rodygin           2007-10-02      new-513: Apply current filter set to search results.
//  Artem Rodygin           2007-10-17      new-602: Rename "Add child" to "Attach child".
//  Artem Rodygin           2007-10-24      new-564: Filters set.
//  Yury Udovichenko        2007-11-02      new-562: Ability to show only last values of any state.
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-11      bug-624: dbx_error(): Too many tables; MySQL can only use 61 tables in a join
//  Artem Rodygin           2007-11-13      new-599: Separated "Age" in custom views.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Yury Udovichenko        2007-11-14      new-548: Custom links in text fields.
//  Yury Udovichenko        2007-11-19      new-623: Default state in states list.
//  Yury Udovichenko        2007-11-20      new-536: Ability to hide postpone records from the list.
//  Artem Rodygin           2007-11-29      new-637: Subject of notifications should contain subject of records.
//  Artem Rodygin           2007-11-29      new-617: Add 'no view' and 'no filter set' to related comboboxes.
//  Artem Rodygin           2007-12-27      new-659: Set default language
//  Artem Rodygin           2007-12-30      bug-660: [SF1860788] PHP4 html_entity_decode() is not working
//  Artem Rodygin           2008-01-16      new-666: Buttons "Previous" & "Next" on record view page.
//  Artem Rodygin           2008-01-28      new-531: LDAP Guest users
//  Artem Rodygin           2008-01-31      new-601: [SF1814666] Export and Import Templates
//  Artem Rodygin           2008-02-27      new-676: [SF1898731] Delete Issues from Workflow
//  Artem Rodygin           2008-02-28      new-294: PostgreSQL support.
//  Artem Rodygin           2008-03-02      bug-681: Update configuration page with new options.
//  Artem Rodygin           2008-03-31      new-691: Localization module is optimized to avoid prompts duplication.
//  Artem Rodygin           2008-04-30      bug-699: Views // Names of custom columns are duplicated in the list of available columns, when there are two fields of different types with the same name.
//  Artem Rodygin           2008-05-01      new-715: Show creation time in the list of records.
//  Artem Rodygin           2008-07-02      new-729: [SF2008579] Mark all records as read
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-01-08      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-04-12      bug-806: German translation causes two ambiguous "zuruck" buttons.
//  Artem Rodygin           2009-04-24      new-817: Field permissions dialog refactoring.
//  Artem Rodygin           2009-04-26      new-818: Change buttons layout on viewing record page.
//  Artem Rodygin           2009-10-12      new-848: LDAP TLS support.
//--------------------------------------------------------------------------------------------------

$resource_english = array
(
    RES_SECTION_ALERTS =>
    /* 200 */
    'All fields marked as required should be filled in.',
    'Default value should be in range from %1 to %2.',
    'Account is disabled.',
    'Account is locked out.',
    'Invalid user name.',
    'Account with entered user name already exists.',
    'Invalid email.',
    'Passwords do not match.',
    'Password should be at least %1 characters length.',
    'Project with entered name already exists.',
    /* 210 */
    'Group with entered name already exists.',
    'Template with entered name or prefix already exists.',
    'State with entered name or abbreviation already exists.',
    'Field with entered name already exists.',
    'Invalid integer value.',
    'Integer value should be in range from %1 to %2.',
    'Value of "%1" should be in range from %2 to %3.',
    'Maximum value should be greater then minimum one.',
    'The uploaded file exceeds the "upload_max_filesize" directive in "php.ini".',
    'The size of uploaded file cannot be greater than %1 Kbytes.',
    /* 220 */
    'The uploaded file was only partially uploaded.',
    'No file was uploaded.',
    'Missing a temporary folder.',
    'Attachment with entered name already exists.',
    'Record not found.',
    'Filter with entered name already exists.',
    'Invalid date value.',
    'Date value should be in range from %1 to %2.',
    'Invalid time value.',
    'Time value should be in range from %1 to %2.',
    /* 230 */
    'Subscription with entered name already exists.',
    'Reminder with entered name already exists.',
    'Reminder is successfully sent.',
    'View with entered name already exists.',
    'Column with entered name already exists.',
    'Failed to write file to disk.',
    'File upload stopped by extension.',
    'JavaScript must be enabled.',
    'This is autogenerated message, please do not reply on it.',
    'Specified subrecord already exists.',
    /* 240 */
    'Filters set with entered name already exists.',
    'View cannot have more than %1 columns.',
    'Value of "%1" fails format check.',
    'User is not authorized.',
    'Unknown user name or bad password.',
    'Unknown authentication type.',
    'Unknown error.',
    'XML parser error.',
    'Database connection error.',

    RES_SECTION_CONFIRMS =>
    /* 300 */
    'Are you sure you want delete all selected views?',
    'Are you sure you want delete this account?',
    'Are you sure you want delete this project?',
    'Are you sure you want delete this group?',
    'Are you sure you want delete this template?',
    'Are you sure you want delete this state?',
    'Are you sure you want delete this field?',
    'Are you sure you want postpone this record?',
    'Are you sure you want resume this record?',
    'Are you sure you want assign this record?',
    /* 310 */
    'Are you sure you want delete all selected filters?',
    'Are you sure you want delete all selected subscriptions?',
    'Are you sure you want send this reminder?',
    'Are you sure you want delete this reminder?',
    'Are you sure you want delete this column?',
    'Are you sure you want exit?',
    'Are you sure you want delete all selected filters sets?',
    'Are you sure you want delete this record?',

    RES_SECTION_PROMPTS =>
    /* 1000 */
    'English',
    'Log in',
    '  OK  ',
    'Cancel',
    'Save',
    'Back',
    'Next',
    'Create',
    'Modify',
    'Delete',
    /* 1010 */
    'Records',
    'Accounts',
    'Projects',
    'Change password',
    'Fields of state "%1"',
    'none',
    'Total:',
    'save password',
    'Account information',
    'User name',
    /* 1020 */
    'Full name',
    'Email',
    'Rights',
    'administrator',
    'user',
    'Description',
    'Password',
    'Confirmation',
    'disabled',
    'locked',
    /* 1030 */
    'New account',
    'Account "%1"',
    'Project information',
    'Project name',
    'Start time',
    'suspended',
    'New project',
    'Project "%1"',
    'Groups',
    'Group information',
    /* 1040 */
    'Group name',
    'New group',
    'Group "%1"',
    'Membership',
    'Others',
    'Members',
    'Templates',
    'Template information',
    'Template name',
    'Prefix',
    /* 1050 */
    'New template',
    'Template "%1"',
    'States',
    'State information',
    'State name',
    'Abbreviation',
    'State type',
    'initial',
    'intermediate',
    'final',
    /* 1060 */
    'Responsible',
    'keep unchanged',
    'assign',
    'remove',
    'New state',
    'State "%1"',
    'Create intermediate',
    'Create final',
    'Transitions',
    'Permissions',
    /* 1070 */
    'Make initial',
    'Allowed',
    'Fields',
    'Field information',
    'Order',
    'Field name',
    'Field type',
    'number',
    'string',
    'multilined text',
    /* 1080 */
    'Required',
    'yes',
    'no',
    'Min.value',
    'Max.value',
    'Max.length',
    'required',
    'New field (step %1/%2)',
    'Field "%1"',
    'read-only',
    /* 1090 */
    'read and write',
    'General information',
    'ID',
    'Project',
    'Template',
    'State',
    'Age',
    'New record (step %1/%2)',
    'Record "%1"',
    'Go',
    /* 1100 */
    'History',
    'Postpone',
    'Resume',
    'Assign',
    'Change state',
    'Timestamp',
    'Originator',
    'Record is created in state "%1".',
    'Record is assigned on %1.',
    'Record is modified.',
    /* 1110 */
    'State is changed to "%1".',
    'Record is postponed till %1.',
    'Record is resumed.',
    'File "%1" is attached.',
    'File "%1" is removed.',
    'permission to create records',
    'permission to modify records',
    'permission to postpone records',
    'permission to resume records',
    'permission to reassign assigned records',
    /* 1120 */
    'permission to change state of assigned records',
    'permission to attach files',
    'permission to remove files',
    'Language',
    'Add comment',
    'Comment is added.',
    'permission to add comments',
    'Comment',
    'Attach file',
    'Remove file',
    /* 1130 */
    'Attachment',
    'Attachment name',
    'Attachment file',
    'Attachments',
    'No fields.',
    'Critical age',
    'Frozen time',
    'Changes',
    'Old value',
    'New value',
    /* 1140 */
    'check box',
    'record',
    'list',
    'List items',
    '%1 Kb',
    'Filters',
    'Filter name',
    'All projects',
    'All templates',
    'All states',
    /* 1150 */
    'View record',
    'Show only created by ...',
    'Show only assigned on ...',
    'show unclosed only',
    'Subject',
    'Search',
    'Search parameters',
    'Search results (filtered)',
    'Text to be searched',
    'search in fields',
    /* 1160 */
    'search in comments',
    'Status',
    'active',
    'Subscriptions',
    'notify when record is created',
    'notify when record is assigned',
    'notify when record is modified',
    'notify when state is changed',
    'notify when record is postponed',
    'notify when record is resumed',
    /* 1170 */
    'notify when comment is added',
    'notify when file is attached',
    'notify when file is removed',
    'required',
    'Postponed',
    'Due date',
    'Default value',
    'on',
    'off',
    'Metrics',
    /* 1180 */
    'Opened records',
    'Creation vs Closure',
    'week',
    'number',
    'Clone',
    'Record is cloned from "%1".',
    'Log out',
    'notify when record is cloned',
    'Settings',
    'Rows per page',
    /* 1190 */
    'Bookmarks per page',
    'Lock',
    'Unlock',
    'Group type',
    'global',
    'local',
    'Configuration',
    'Local root path',
    'Web root path',
    'Security',
    /* 1200 */
    'Minimum password length',
    'Maximum number of login attempts',
    'Locking timeout (mins)',
    'Database',
    'Database type',
    'Oracle',
    'MySQL',
    'Microsoft SQL Server',
    'Database server',
    'Database name',
    /* 1210 */
    'Database user',
    'Active Directory',
    'LDAP server',
    'Port number',
    'Search account',
    'Base DN',
    'Administrators',
    'Email notifications',
    'Maximum size',
    'Debug',
    /* 1220 */
    'Debug mode',
    'enabled (without private data)',
    'enabled (all data)',
    'Debug logs',
    'Enabled',
    'Disabled',
    '%1 min',
    'permission to view records only',
    'Select all',
    'Author',
    /* 1230 */
    'date',
    'duration',
    'show postponed only',
    'Subscription name',
    'Events',
    'Version %1',
    'role',
    'Subscribe',
    'Unsubscribe',
    'Reminders',
    /* 1240 */
    'Reminder name',
    'Reminder subject',
    'Reminder recipients',
    'New reminder (step %1/%2)',
    'Reminder "%1"',
    'permission to send reminders',
    'Send',
    'New filter',
    'Filter "%1"',
    'New subscription',
    /* 1250 */
    'Subscription "%1"',
    'Your LDAP password',
    'You can insert link to another record by specifying "rec#" and its number (e.g. "rec#305").',
    'Show only moved to states ...',
    'Share with ...',
    'Export',
    'Subscribe others...',
    'Subscribed',
    '%1 has subscribed you to the record.',
    '%1 has unsubscribed.',
    /* 1260 */
    'Carbon copy',
    'Storage',
    'LDAP attribute',
    'Views',
    'View information',
    'View name',
    'New view (step %1/%2)',
    'View "%1"',
    'No view',
    'Set',
    /* 1270 */
    'Columns',
    'Column information',
    'Column title',
    'New column',
    'Column "%1"',
    'Alignment',
    'left',
    'center',
    'right',
    'Service will be unavailable since %1 till %2 (%3)',
    /* 1280 */
    'All assigned on me',
    'All created by me',
    'Unselect all',
    'm/d/yyyy',
    'Dump',
    'Subrecords',
    'Create subrecord',
    'Attach subrecord',
    'Remove subrecord',
    'Subrecord ID',
    /* 1290 */
    'Subrecord "%1" is added.',
    'Subrecord "%1" is removed.',
    'permission to add subrecords',
    'permission to remove subrecords',
    'notify when subrecord is added',
    'notify when subrecord is removed',
    'created records',
    'closed records',
    'Confidential',
    'Add confidential comment',
    /* 1300 */
    'permission to add/read confidential comments',
    'Confidential comment is added.',
    'Parent ID',
    'dependency',
    'hidden',
    'Add separator',
    'CSV delimiter',
    'CSV encoding',
    'CSV line endings',
    'Search results (unfiltered)',
    /* 1310 */
    'Enable filters',
    'Disable filters',
    'Current filters set',
    'Save filters set',
    'Filters sets',
    'Filters set name',
    'Filters set "%1"',
    'Current view',
    'Save view',
    'Expand all',
    /* 1320 */
    'Collapse all',
    'Reset to defaults',
    'L/E',
    'PCRE to check field values',
    'Search PCRE to transform field values',
    'Replace PCRE to transform field values',
    'Next state by default',
    'Postpone status',
    'show all',
    'show active only',
    /* 1330 */
    'Event',
    'No filters set',
    'Guest access',
    'None.',
    '',
    'Guest',
    'Import',
    'permission to delete records',
    'Authentication type',
    'Default language',
    /* 1340 */
    'Password expiration (days)',
    'Session expiration (mins)',
    'LDAP enumeration',
    'PostgreSQL',
    'list of indexes',
    'list of values',
    'Created',
    'Mark as read',
    'Registered',
    'TLS',
);

?>
