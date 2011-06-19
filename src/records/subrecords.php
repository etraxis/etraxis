<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2010-2011  Artem Rodygin
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

// records list is submitted

if (try_request('submitted') == 'subrecords')
{
    debug_write_log(DEBUG_NOTICE, 'Data are submitted.');

    foreach ($_REQUEST as $request)
    {
        if (substr($request, 0, 3) == 'rec')
        {
            subrecord_remove($id, intval(substr($request, 3)));
        }
    }

    $rs = dal_query('depends/list.sql', $record['record_id']);
    echo(sprintf('%s (%u)', get_html_resource(RES_SUBRECORDS_ID), $rs->rows));
    exit;
}

// local JS functions

$resTitle  = get_js_resource(RES_ATTACH_SUBRECORD_ID);
$resOK     = get_js_resource(RES_OK_ID);
$resNext   = get_js_resource(RES_NEXT_ID);
$resCancel = get_js_resource(RES_CANCEL_ID);

$xml = <<<JQUERY
<script>

function createSubrecordForceToStep2 ()
{
    var items = $("#projectform #project").children().length;

    if (items == 1)
    {
        createSubrecordStep2();
    }
}

function createSubrecordForceToStep3 ()
{
    var items = $("#templateform #template").children().length;

    if (items == 1)
    {
        createSubrecordStep3();
    }
}

function createSubrecordStep1 ()
{
    jqModal("{$resTitle}", "create.php?parent={$id}", "{$resNext}", "{$resCancel}", "createSubrecordStep2()", null, "createSubrecordForceToStep2()");
}

function createSubrecordStep2 ()
{
    closeModal();
    jqModal("{$resTitle}", "create.php?parent={$id}&amp;" + $("#projectform").serialize(), "{$resNext}", "{$resCancel}", "createSubrecordStep3()", null, "createSubrecordForceToStep3()");
}

function createSubrecordStep3 ()
{
    closeModal();
    jqModal("{$resTitle}", "create.php?parent={$id}&amp;" + $("#templateform").serialize(), "{$resOK}", "{$resCancel}", "$('#mainform').submit()");
}

function addSubrecord ()
{
    jqModal("{$resTitle}", "addsubrec.php?id={$id}", "{$resOK}", "{$resCancel}", "$('#addsubrecform').submit()");
}

function removeSubrecordSuccess (data)
{
    var index = $("#tabs").tabs("option", "selected") + 1;
    $("[href=#ui-tabs-" + index + "]").html(data);
    reloadTab();
}

</script>
JQUERY;

// generate buttons

$xml .= '<buttonset>';

$xml .= (can_subrecord_be_added($record, $permissions)
            ? '<button action="createSubrecordStep1()">'
            : '<button disabled="true">')
      . get_html_resource(RES_CREATE_SUBRECORD_ID)
      . '</button>';

$xml .= (can_subrecord_be_added($record, $permissions)
            ? '<button action="addSubrecord()">'
            : '<button disabled="true">')
      . get_html_resource(RES_ATTACH_SUBRECORD_ID)
      . '</button>';

$xml .= '</buttonset>';

$xml .= (can_subrecord_be_removed($record, $permissions)
            ? '<button action="$(\'#subrecords\').submit()">'
            : '<button disabled="true">')
      . get_html_resource(RES_REMOVE_SUBRECORD_ID)
      . '</button>';

// generate list of subrecords

$list = subrecords_list($id);

if ($list->rows != 0)
{
    $columns = array
    (
        RES_ID_ID,
        RES_STATE_ID,
        RES_SUBJECT_ID,
        RES_RESPONSIBLE_ID,
    );

    $xml .= '<form name="subrecords" action="subrecords.php?id=' . $id . '" success="removeSubrecordSuccess">'
          . '<list>'
          . '<hrow>'
          . '<hcell checkboxes="true"/>';

    foreach ($columns as $column)
    {
        $xml .= "<hcell>" . get_html_resource($column) . '</hcell>';
    }

    $xml .= '</hrow>';

    while (($row = $list->fetch()))
    {
        if (is_record_closed($row))
        {
            $color = 'grey';
        }
        elseif ($row['is_dependency'])
        {
            $color = 'red';
        }
        else
        {
            $color = NULL;
        }

        $xml .= "<row name=\"rec{$row['record_id']}\" url=\"view.php?id={$row['record_id']}\" color=\"{$color}\">"
              . '<cell align="left" nowrap="true">' . record_id($row['record_id'], $row['template_prefix']) . '</cell>'
              . '<cell align="left">' . ustr2html($row['state_abbr']) . '</cell>'
              . '<cell align="left">' . update_references($row['subject'], BBCODE_SEARCH_ONLY) . '</cell>'
              . '<cell align="left">' . (is_null($row['fullname']) ? get_html_resource(RES_NONE_ID) : ustr2html($row['fullname'])) . '</cell>'
              . '</row>';
    }

    $xml .= '</list>'
          . '</form>';
}

echo(xml2html($xml));

?>
