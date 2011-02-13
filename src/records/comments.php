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

init_page(LOAD_TAB, GUEST_IS_ALLOWED);

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_record_be_displayed($permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be displayed.');
    exit;
}

// comment is submitted

if (try_request('submitted') == 'commentform' ||
    try_request('submitted') == 'confidentialform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    if (can_comment_be_added($record, $permissions))
    {
        $is_confidential = (try_request('submitted') == 'confidentialform');

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

    $rs = dal_query('comments/list.sql', $record['record_id'], ($permissions & PERMIT_CONFIDENTIAL_COMMENTS) ? EVENT_CONFIDENTIAL_COMMENT : EVENT_UNUSED);
    echo(sprintf('%s (%u)', get_html_resource(RES_COMMENTS_ID), $rs->rows));
    exit;
}

// mark the record as read

record_read($id);

// get list of comments

$comments = dal_query('comments/list.sql', $id, ($permissions & PERMIT_CONFIDENTIAL_COMMENTS) ? EVENT_CONFIDENTIAL_COMMENT : EVENT_UNUSED);

// local JS functions

$xml = <<<JQUERY
<script>

function addConfidentialComment ()
{
    $("#commentform :input[name=submitted]").val("confidentialform");
    $("#commentform").submit();
    $("#commentform :input[name=submitted]").val("commentform");
}

function previewComment ()
{
    $("#previewdiv").load("preview.php", $("#commentform").serialize());
}

function commentSuccess (data)
{
    var index = $("#tabs").tabs("option", "selected") + 1;
    $("[href=#ui-tabs-" + index + "]").html(data);
    reloadTab();
}

</script>
JQUERY;

// whether user is allowed to add new comment

if (can_comment_be_added($record, $permissions))
{
    $xml .= '<form name="commentform" action="comments.php?id=' . $id . '" success="commentSuccess">'
          . '<group title="' . get_html_resource(RES_COMMENT_ID) . '">'
          . '<control name="comment">'
          . '<textbox rows="' . $_SESSION[VAR_TEXTROWS] . '" resizeable="true" maxlen="' . MAX_COMMENT_BODY . '">'
          . '</textbox>'
          . '</control>'
          . '</group>'
          . '<buttonset>'
          . '<button default="true">' . get_html_resource(RES_ADD_COMMENT_ID) . '</button>';

    if ($permissions & PERMIT_CONFIDENTIAL_COMMENTS)
    {
        $xml .= '<button action="addConfidentialComment()">'
              . get_html_resource(RES_ADD_CONFIDENTIAL_COMMENT_ID)
              . '</button>';
    }

    $xml .= '</buttonset>'
          . '<button action="previewComment()">' . get_html_resource(RES_PREVIEW_ID) . '</button>'
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

echo(xml2html($xml));

?>
