<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2009  Artem Rodygin
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//------------------------------------------------------------------------------

/**
 * Reminders
 *
 * This module provides API to work with user reminders.
 * See also {@link https://github.com/etraxis/etraxis-obsolete/wiki/tbl_reminders tbl_reminders} database table.
 *
 * @package DBO
 * @subpackage Reminders
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/records.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Data restrictions.
 */
define('MAX_REMINDER_NAME',    25);
define('MAX_REMINDER_SUBJECT', 100);
/**#@-*/

/**#@+
 * Reminder group flags.
 */
define('REMINDER_FLAG_GROUP',        0);
define('REMINDER_FLAG_AUTHOR',      -1);
define('REMINDER_FLAG_RESPONSIBLE', -2);
/**#@-*/

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified reminder.
 *
 * @param int $id Reminder ID.
 * @return array Array with data if reminder is found in database, FALSE otherwise.
 */
function reminder_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[reminder_find]');
    debug_write_log(DEBUG_DUMP,  '[reminder_find] $id = ' . $id);

    $rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'reminders/oracle/fndid.sql' : 'reminders/fndid.sql',
                    $_SESSION[VAR_USERID],
                    $id);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing reminders of specified account.
 *
 * @param int $id Account ID.
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_REMINDERS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_REMINDERS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of reminders.
 */
function reminders_list ($id, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[reminders_list]');
    debug_write_log(DEBUG_DUMP,  '[reminders_list] $id = ' . $id);

    $sort_modes = array
    (
        1  => 'reminder_name asc',
        2  => 'project_name asc, reminder_name asc',
        3  => 'template_name asc, reminder_name asc',
        4  => 'state_name asc, reminder_name asc',
        5  => 'subject_text asc, reminder_name asc',
        6  => 'reminder_name desc',
        7  => 'project_name desc, reminder_name desc',
        8  => 'template_name desc, reminder_name desc',
        9  => 'state_name desc, reminder_name desc',
        10 => 'subject_text desc, reminder_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_REMINDERS_SORT, 1));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_REMINDERS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_REMINDERS_SORT, $sort);
    save_cookie(COOKIE_REMINDERS_PAGE, $page);

    return dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'reminders/oracle/list.sql' : 'reminders/list.sql',
                     $id,
                     $sort_modes[$sort]);
}

/**
 * Validates reminder information before creation or modification.
 *
 * @param string $reminder_name Reminder name.
 * @param string $subject_text Subject of reminder.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * </ul>
 */
function reminder_validate ($reminder_name, $subject_text)
{
    debug_write_log(DEBUG_TRACE, '[reminder_validate]');
    debug_write_log(DEBUG_DUMP,  '[reminder_validate] $reminder_name = ' . $reminder_name);
    debug_write_log(DEBUG_DUMP,  '[reminder_validate] $subject_text  = ' . $subject_text);

    if (ustrlen($reminder_name) == 0 ||
        ustrlen($subject_text)  == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[reminder_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    return NO_ERROR;
}

/**
 * Creates new reminder.
 *
 * @param string $reminder_name Reminder name.
 * @param string $subject_text Subject of reminder.
 * @param string $state_id State ID.
 * @param string $group_id Group ID.
 * @param string $group_flag Group flag.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - reminder is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - reminder with specified name already exists</li>
 * </ul>
 */
function reminder_create ($reminder_name, $subject_text, $state_id, $group_id, $group_flag)
{
    debug_write_log(DEBUG_TRACE, '[reminder_create]');
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $reminder_name = ' . $reminder_name);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $subject_text  = ' . $subject_text);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $state_id      = ' . $state_id);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $group_id      = ' . $group_id);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $group_flag    = ' . $group_flag);

    // Check that user doesn't have another reminder with the same name.
    $rs = dal_query('reminders/fndk.sql', $_SESSION[VAR_USERID], ustrtolower($reminder_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[reminder_create] Reminder already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create a reminder.
    dal_query('reminders/create.sql',
              $_SESSION[VAR_USERID],
              $reminder_name,
              ustrlen($subject_text) == 0 ? NULL : $subject_text,
              $state_id,
              is_null($group_id) ? NULL : $group_id,
              $group_flag);

    return NO_ERROR;
}

/**
 * Modifies specified reminder.
 *
 * @param int $id ID of reminder to be modified.
 * @param string $reminder_name New Reminder name.
 * @param string $subject_text New Subject of reminder.
 * @param string $state_id New State ID.
 * @param string $group_id New Group ID.
 * @param string $group_flag New Group flag.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - reminder is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - reminder with specified name already exists</li>
 * </ul>
 */
function reminder_modify ($id, $reminder_name, $subject_text, $state_id, $group_id, $group_flag)
{
    debug_write_log(DEBUG_TRACE, '[reminder_modify]');
    debug_write_log(DEBUG_DUMP,  '[reminder_modify] $id            = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $reminder_name = ' . $reminder_name);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $subject_text  = ' . $subject_text);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $state_id      = ' . $state_id);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $group_id      = ' . $group_id);
    debug_write_log(DEBUG_DUMP,  '[reminder_create] $group_flag    = ' . $group_flag);

    // Check that user doesn't have another reminder with the same name, besides this one.
    $rs = dal_query('reminders/fndku.sql', $id, $_SESSION[VAR_USERID], ustrtolower($reminder_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[reminder_modify] Reminder already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the reminder.
    dal_query('reminders/modify.sql',
              $id,
              $reminder_name,
              ustrlen($subject_text) == 0 ? NULL : $subject_text,
              $state_id,
              is_null($group_id) ? NULL : $group_id,
              $group_flag);

    return NO_ERROR;
}

/**
 * Deletes specified reminder.
 *
 * @param int $id ID of reminder to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function reminder_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[reminder_delete]');
    debug_write_log(DEBUG_DUMP,  '[reminder_delete] $id = ' . $id);

    dal_query('reminders/delete.sql', $id);

    return NO_ERROR;
}

/**
 * Checks whether reminder can be created.
 *
 * @return bool TRUE if reminder can be created, FALSE otherwise.
 */
function can_reminder_be_created ()
{
    debug_write_log(DEBUG_TRACE, '[can_reminder_be_created]');

    $rs = dal_query(DATABASE_DRIVER == DRIVER_ORACLE9 ? 'reminders/oracle/plist.sql' : 'reminders/plist.sql',
                    $_SESSION[VAR_USERID]);

    return ($rs->rows != 0);
}

/**
 * Generates and returns message body for reminder about specified records.
 *
 * @param array $records Array of records data.
 * @param int $locale ID of language. If omitted, then language of current user, or (when user is not logged in) default language will be used (see {@link LANG_DEFAULT}).
 * @return string Generated message body.
 */
function reminder_message ($records, $locale = NULL)
{
    debug_write_log(DEBUG_TRACE, '[reminder_message]');
    debug_write_log(DEBUG_DUMP,  '[reminder_message] $locale = ' . $locale);

    $message =
        '<html>' .
        '<body>' .
        '<b><font color="red">' . get_html_resource(RES_ALERT_DO_NOT_REPLY_ID) . '</font></b><br/>' .
        '<table border="1" cellspacing="0" cellpadding="5">' .
        '<tr valign="top">' .
        '<td nowrap><b>' . get_html_resource(RES_ID_ID,      $locale) . '</b></td>' .
        '<td nowrap><b>' . get_html_resource(RES_STATE_ID,   $locale) . '</b></td>' .
        '<td nowrap><b>' . get_html_resource(RES_PROJECT_ID, $locale) . '</b></td>' .
        '<td><b>'        . get_html_resource(RES_SUBJECT_ID, $locale) . '</b></td>' .
        '<td nowrap><b>' . get_html_resource(RES_AUTHOR_ID,  $locale) . '</b></td>' .
        '</tr>';

    while (($row = $records->fetch()))
    {
        $message .=
            '<tr valign="top">' .
            '<td align="left" nowrap><a href="' . WEBROOT . 'records/view.php?id=' . $row['record_id'] . '">' . record_id($row['record_id'], $row['template_prefix']) . '</a></td>' .
            '<td align="center" nowrap>' . ustr2html($row['state_abbr'])      . '</td>' .
            '<td align="left" nowrap>'   . ustr2html($row['project_name'])    . '</td>' .
            '<td align="left">'          . ustr2html($row['subject'])         . '</td>' .
            '<td align="left" nowrap>'   . ustr2html($row['fullname']) . '</td>' .
            '</tr>';
    }

    $message .=
        '</table>' .
        '</body>' .
        '</html>';

    return $message;
}

/**
 * Sends specified reminder to all interested parties.
 *
 * @param array $reminder Array with data of reminder (e.g. how it's returned by {@link reminder_find}).
 * @return int Always {@link NO_ERROR}.
 */
function reminder_send ($reminder)
{
    debug_write_log(DEBUG_TRACE, '[reminder_send]');
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["project_id"]    = ' . $reminder['project_id']);
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["project_name"]  = ' . $reminder['project_name']);
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["template_id"]   = ' . $reminder['template_id']);
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["template_name"] = ' . $reminder['template_name']);
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["reminder_name"] = ' . $reminder['reminder_name']);
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["subject_text"]  = ' . $reminder['subject_text']);
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["state_id"]      = ' . $reminder['state_id']);
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["group_id"]      = ' . $reminder['group_id']);
    debug_write_log(DEBUG_DUMP,  '[reminder_send] $reminder["group_flag"]    = ' . $reminder['group_flag']);

    global $locale_info;

    $account = account_find($_SESSION[VAR_USERID]);

    // Since sending email can takes a time, disable PHP execution timeout.
    if (!ini_get('safe_mode'))
    {
        set_time_limit(0);
    }

    switch ($reminder['group_flag'])
    {
        // Reminder is dedicated to specified group.
        case REMINDER_FLAG_GROUP:

            $records = dal_query('reminders/rlist.sql', $reminder['state_id']);

            if ($records->rows == 0)
            {
                debug_write_log(DEBUG_NOTICE, '[reminder_send] Reminder is empty and will not be sent.');
            }
            else
            {
                $supported_locales = array_keys($locale_info);

                foreach ($supported_locales as $locale)
                {
                    $to = array();
                    $rs = dal_query('reminders/members.sql', $reminder['group_id'], $locale);

                    while (($row = $rs->fetch()))
                    {
                        array_push($to, $row['email']);
                    }

                    if (count($to) != 0)
                    {
                        $recipients = implode(', ', array_unique($to));
                        $message    = reminder_message($records, $locale);

                        if (EMAIL_NOTIFICATIONS_ENABLED)
                        {
                            debug_write_log(DEBUG_NOTICE, '[reminder_send] Sending email.');
                            sendmail($account['fullname'], $account['email'], $recipients, $reminder['subject_text'], $message);
                        }
                        else
                        {
                            debug_write_log(DEBUG_NOTICE, '[reminder_send] Email notifications are disabled.');
                        }
                    }
                }
            }

            break;

        // Reminder is dedicated to records submitters.
        case REMINDER_FLAG_AUTHOR:

            $rs = dal_query('reminders/alista.sql', $reminder['state_id']);

            while (($row = $rs->fetch()))
            {
                $records = dal_query('reminders/rlista.sql', $reminder['state_id'], $row['account_id']);

                if ($records->rows == 0)
                {
                    debug_write_log(DEBUG_NOTICE, '[reminder_send] Reminder is empty and will not be sent.');
                }
                else
                {
                    $message = reminder_message($records, $row['locale']);

                    if (EMAIL_NOTIFICATIONS_ENABLED)
                    {
                        debug_write_log(DEBUG_NOTICE, '[reminder_send] Sending email.');
                        sendmail($account['fullname'], $account['email'], $row['email'], $reminder['subject_text'], $message);
                    }
                    else
                    {
                        debug_write_log(DEBUG_NOTICE, '[reminder_send] Email notifications are disabled.');
                    }
                }
            }

            break;

        // Reminder is dedicated to current records assignees.
        case REMINDER_FLAG_RESPONSIBLE:

            $rs = dal_query('reminders/alistr.sql', $reminder['state_id']);

            while (($row = $rs->fetch()))
            {
                $records = dal_query('reminders/rlistr.sql', $reminder['state_id'], $row['account_id']);

                if ($records->rows == 0)
                {
                    debug_write_log(DEBUG_NOTICE, '[reminder_send] Reminder is empty and will not be sent.');
                }
                else
                {
                    $message = reminder_message($records, $row['locale']);

                    if (EMAIL_NOTIFICATIONS_ENABLED)
                    {
                        debug_write_log(DEBUG_NOTICE, '[reminder_send] Sending email.');
                        sendmail($account['fullname'], $account['email'], $row['email'], $reminder['subject_text'], $message);
                    }
                    else
                    {
                        debug_write_log(DEBUG_NOTICE, '[reminder_send] Email notifications are disabled.');
                    }
                }
            }

            break;

        default:

            debug_write_log(DEBUG_WARNING, '[reminder_send] Unknown reminder group flags = ' . $reminder['group_flag']);
    }

    // Restore PHP execution timeout, disabled above.
    if (!ini_get('safe_mode'))
    {
        ini_restore('max_execution_time');
    }

    return NO_ERROR;
}

?>
