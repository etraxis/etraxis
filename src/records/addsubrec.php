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

init_page(LOAD_INLINE);

// check that requested record exists

$id     = ustr2int(try_request('id'));
$record = record_find($id);

if (!$record)
{
    debug_write_log(DEBUG_NOTICE, 'Record cannot be found.');
    header('HTTP/1.1 307 index.php');
    exit;
}

// get current user's permissions and verify them

$permissions = record_get_permissions($record['template_id'], $record['creator_id'], $record['responsible_id']);

if (!can_subrecord_be_added($record, $permissions))
{
    debug_write_log(DEBUG_NOTICE, 'Subrecord cannot be added.');
    header('HTTP/1.1 307 subrecords.php?id=' . $id);
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

    $rs = dal_query('depends/list.sql', $record['record_id']);
    echo(sprintf('%s (%u)', get_html_resource(RES_SUBRECORDS_ID), $rs->rows));
    exit;
}
else
{
    debug_write_log(DEBUG_NOTICE, 'Data are being requested.');
}

// local JS functions

$xml = <<<JQUERY
<script>

function addSubrecordSuccess (data)
{
    var index = $("#tabs").tabs("option", "selected") + 1;
    $("[href=#ui-tabs-" + index + "]").html(data);
    closeModal();
    reloadTab();
}

</script>
JQUERY;

// generate subrecords form

$xml .= '<form name="addsubrecform" action="addsubrec.php?id=' . $id . '" success="addSubrecordSuccess">'
      . '<group>'
      . '<control name="subrecords">'
      . '<label>' . get_html_resource(RES_ID_ID) . '</label>'
      . '<editbox maxlen="100"/>'
      . '</control>'
      . '<control name="is_dependency">'
      . '<label/>'
      . '<checkbox checked="true">'
      . get_html_resource(RES_DEPENDENCY_ID)
      . '</checkbox>'
      . '</control>'
      . '</group>'
      . '</form>';

echo(xml2html($xml));

?>
