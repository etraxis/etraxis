<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2006-2010  Artem Rodygin
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
        debug_write_log(DEBUG_NOTICE, 'Guest must be logged in.');
        save_cookie(COOKIE_URI, $_SERVER['REQUEST_URI']);
        header('Location: ../logon/index.php');
        exit;
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
header('Content-Type: ' . $attachment['attachment_type']);
header('Content-Disposition: attachment; filename="' . $filename . '"');

if (extension_loaded('zlib'))
{
    readgzfile(ATTACHMENTS_PATH . $id);
}
else
{
    readfile(ATTACHMENTS_PATH . $id);
}

?>
