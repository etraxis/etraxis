<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2010  Artem Rodygin
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
 * @package eTraxis
 * @ignore
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/records.php');
require_once('../dbo/events.php');
/**#@-*/

init_page(GUEST_IS_ALLOWED);

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_displayed($permissions))
{
    if (get_user_level() == USER_LEVEL_GUEST)
    {
        save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
    }

    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    header('Location: index.php');
    exit;
}

// comment is submitted

if (try_request('submitted') == 'commentform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    if (can_comment_be_added($record, $permissions))
    {
        $is_confidential = try_request('confidential', FALSE);

        if ($is_confidential && ($permissions & PERMIT_CONFIDENTIAL_COMMENTS) == 0)
        {
            debug_write_log(DEBUG_NOTICE, 'Lack of permissions to add confidential comments.');
        }
        else
        {
            $comment = ustrcut($_REQUEST['comment'], MAX_COMMENT_BODY);

            $rs = dal_query('records/efnd2.sql', $id, $_SESSION[VAR_USERID], ($is_confidential ? EVENT_CONFIDENTIAL_COMMENT : EVENT_COMMENT_ADDED), time() - 3);

            if ($rs->rows != 0)
            {
                debug_write_log(DEBUG_NOTICE, 'Double click issue is detected.');
            }
            else
            {
                comment_add($id, $comment, $is_confidential);
        }
        }
    }
    else
    {
        debug_write_log(DEBUG_NOTICE, 'Comment cannot be added.');
    }
}

// mark the record as read

record_read($id);

// page's title

$title = ustrprocess(get_html_resource(RES_RECORD_X_ID), record_id($id, $record['template_prefix']));

// generate breadcrumbs and tabs

$xml = '<breadcrumbs>'
     . '<breadcrumb url="index.php">' . get_html_resource(RES_RECORDS_ID) . '</breadcrumb>'
     . '<breadcrumb url="comments.php?id=' . $id . '">' . $title . '</breadcrumb>'
     . '</breadcrumbs>'
     . '<tabs>'
     . gen_record_tabs($record, RECORD_TAB_COMMENTS)
     . '<content>';

// get list of comments

$comments = dal_query('comments/list.sql', $id);

// whether user is allowed to add new comment

if (can_comment_be_added($record, $permissions))
{
    $xml .= '<form name="commentform" action="comments.php?id=' . $id . '">'
          . '<script src="preview.js"></script>'
          . '<group title="' . get_html_resource(RES_COMMENT_ID) . '">'
          . '<control name="comment">'
          . '<textbox rows="' . HTML_TEXTBOX_MIN_HEIGHT . '" resizeable="true" maxlen="' . MAX_COMMENT_BODY . '">'
          . '</textbox>'
          . '</control>'
          . '</group>'
          . '<button default="true">' . get_html_resource(RES_ADD_COMMENT_ID) . '</button>';

    if ($permissions & PERMIT_CONFIDENTIAL_COMMENTS)
    {
        $xml .= '<button action="document.comment.action=\'comments.php?id=' . $id . '&amp;confidential=1\'; document.comment.submit();">'
              . get_html_resource(RES_ADD_CONFIDENTIAL_COMMENT_ID)
              . '</button>';
    }

    $xml .= HTML_SPLITTER
          . '<button name="preview">' . get_html_resource(RES_PREVIEW_ID) . '</button>'
          . '<div id="previewdiv"/>'
          . '<note>' . get_html_resource(RES_LINK_TO_ANOTHER_RECORD_ID) . '</note>'
          . '</form>';
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Comment cannot be added.');

    if ($comments->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'No comments are found.');

        $xml .= get_html_resource(RES_NONE2_ID);
    }
}

// go through the list of all comments

while (($comment = $comments->fetch()))
{
    if ($comment['event_type'] == EVENT_CONFIDENTIAL_COMMENT &&
        ($permissions & PERMIT_CONFIDENTIAL_COMMENTS) == 0)
    {
        continue;
    }

    $group_title = sprintf('%s - %s (%s)',
                           get_datetime($comment['event_time']),
                           $comment['fullname'],
                           account_get_username($comment['username']));

    $xml .= '<group title="' . ustr2html($group_title) . '">'
          . ($comment['is_confidential']
                ? '<text label="' . get_html_resource(RES_CONFIDENTIAL_ID) . '">'
                : '<text>')
          . update_references($comment['comment_body'])
          . '</text>'
          . '</group>';
}

$xml .= '</content>'
      . '</tabs>';

echo(xml2html($xml, $title));

?>
