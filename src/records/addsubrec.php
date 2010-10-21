<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010  Artem Rodygin
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

init_page();

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

if (!can_subrecord_be_added($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Subrecord cannot be added.');
    header('Location: subrecords.php?id=' . $id);
    exit;
}

// get current date

$today = date_floor(time());

// subrecords form is submitted

if (try_request('submitted') == 'addsubrecform')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    $subrecords = ustrcut(try_request('subrecords'), 100);
    $subrecords = ustr_replace(',', ' ', $subrecords);

    $is_dependency = isset($_REQUEST['is_dependency']);

    mb_regex_encoding('UTF-8');
    $subrecords = mb_split(' ', $subrecords);

    foreach ($subrecords as $subrecord)
    {
        if (is_intvalue($subrecord))
        {
            $subrecord = ustr2int($subrecord);

            if (record_find($subrecord))
            {
                subrecord_add($id, $subrecord, $is_dependency);
            }
        }
    }

    record_read($id);

    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// generate subrecords form

$xml = '<form name="addsubrecform" action="javascript:submitAddSubrecForm(' . $id . ')">'
     . '<group title="' . get_html_resource(RES_SUBRECORDS_ID) . '">'
     . '<control name="subrecords">'
     . '<editbox maxlen="100"/>'
     . '</control>'
     . '<control name="is_dependency">'
     . '<checkbox checked="true">'
     . get_html_resource(RES_DEPENDENCY_ID)
     . '</checkbox>'
     . '</control>'
     . '</group>'
     . '<button default="true">'                   . get_html_resource(RES_OK_ID)     . '</button>'
     . '<button action="cancelAddSubrecForm()">' . get_html_resource(RES_CANCEL_ID) . '</button>'
     . '</form>';

echo(xml2html($xml));

?>
