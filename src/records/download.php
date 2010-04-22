<?php

/**
 * @package eTraxis
 * @ignore
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2006-2010 by Artem Rodygin
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
//  Artem Rodygin           2006-11-15      bug-381: Attachments of some types are not opened in valid applications.
//  Artem Rodygin           2006-11-30      bug-402: Attachments cannot be downloaded.
//  Artem Rodygin           2008-01-11      bug-663: Author permissions are ignored.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-04-21      bug-706: PHP Notice: Undefined variable: record
//  Artem Rodygin           2008-11-18      new-762: Forward logged in user to the page he has tried to open before authentication.
//  Artem Rodygin           2009-01-12      bug-784: Logged in user must be forwarded to the page he has tried to open before authentication.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-12-07      bug-857: Problem with russian language and filetype.
//  Giacomo Giustozzi       2010-01-28      new-902: Transparent gzip compression of attachments
//  Artem Rodygin           2010-04-22      bug-931: Attachments compression issues.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/records.php');
/**#@-*/

init_page(GUEST_IS_ALLOWED);

$id         = ustr2int(try_request('id'));
$attachment = attachment_find($id);

if (!$attachment)
{
    debug_write_log(DEBUG_NOTICE, 'Attachment cannot be found.');
    header('Location: index.php');
    exit;
}

$permissions = record_get_permissions($attachment['template_id'], $attachment['creator_id'], $attachment['responsible_id']);

if (!can_record_be_displayed($permissions))
{
    if (get_user_level() == USER_LEVEL_GUEST)
    {
        save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
    }

    debug_write_log(DEBUG_NOTICE, 'Attachment cannot be displayed.');
    header('Location: index.php');
    exit;
}

$filename = stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === FALSE
          ? $attachment['attachment_name']
          : urlencode($attachment['attachment_name']);

header('Pragma: private');
header('Cache-Control: private, must-revalidate');
header('Content-type: ' . $attachment['attachment_type']);
header('Content-Disposition: attachment; filename=' . $filename);

if (extension_loaded('zlib'))
{
    readgzfile(ATTACHMENTS_PATH . $id);
}
else
{
    readfile(ATTACHMENTS_PATH . $id);
}

?>
