<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2005-2009 by Artem Rodygin
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
//  Artem Rodygin           2005-06-05      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-31      new-006: Records search.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-09      new-155: Browser header should contain detailed page info.
//  Artem Rodygin           2005-11-17      new-176: Change eTraxis design.
//  Artem Rodygin           2006-08-13      new-305: Note with explanation of links to other records should be added where needed.
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2007-01-11      bug-481: Double click on comment submitting causes two equal comments creation.
//  Artem Rodygin           2007-01-17      new-480: User should be able to add a comment directly on the same page the ticket is opened.
//  Artem Rodygin           2007-07-16      new-546: Confidential comments.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/records.php');
/**#@-*/

init_page();

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('Location: index.php');
    exit;
}

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_comment_be_added($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Comment cannot be added.');
    header('Location: view.php?id=' . $id);
    exit;
}

if (try_request('submitted') == 'comment')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $is_confidential = try_request('confidential', FALSE);

    if ($is_confidential && ($permissions & PERMIT_CONFIDENTIAL_COMMENTS) == 0)
    {
        debug_write_log(DEBUG_NOTICE, 'Lack of permissions to add confidential comments.');
        header('Location: view.php?id=' . $id);
        exit;
    }

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

header('Location: view.php?id=' . $id);

?>
