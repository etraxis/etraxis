<?php

/**
 * States
 *
 * This module provides API to work with eTraxis states.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_states tbl_states} database table.
 *
 * @package DBO
 * @subpackage States
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
//  Artem Rodygin           2005-03-06      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-24      new-009: Records filter.
//  Artem Rodygin           2005-08-22      bug-046: Query 'filters/fsdelall.sql' is not found.
//  Artem Rodygin           2005-08-23      bug-047: Removable state will not be removed in some cases.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-04      bug-087: New state with empty name or abbreviation can be created.
//  Artem Rodygin           2005-09-12      new-108: Increase maximum length of state name up to 50 characters.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-22      bug-166: Some filters & subscriptions should be removed when a project, template, or state has been deleted.
//  Artem Rodygin           2005-10-22      bug-163: Some filters are malfunctional.
//  Artem Rodygin           2006-02-10      new-209: Default permissions for new states.
//  Artem Rodygin           2006-03-16      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-04-21      bug-242: Unexpected message "State with entered name or abbreviation already exists".
//  Artem Rodygin           2006-04-21      new-247: The 'responsible' user role should be obliterated.
//  Artem Rodygin           2006-06-25      new-222: Email reminders.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2007-01-05      new-491: [SF1647212] Group-wide transition permission.
//  Artem Rodygin           2007-09-10      new-579: Rework "state abbreviation" into "state short name".
//  Artem Rodygin           2007-09-11      new-574: Filter should allow to specify several states.
//  Yury Udovichenko        2007-11-19      new-623: Default state in states list.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-01-28      new-531: LDAP Guest users
//  Artem Rodygin           2008-02-03      new-601: [SF1814666] Export and Import Templates
//  Artem Rodygin           2008-03-20      bug-687: "XML parser error" on template import, if zero is specified in 'critical_age' template's parameter.
//  Artem Rodygin           2008-04-20      new-703: Separated permissions set for current responsible.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-01-08      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-03-24      bug-803: "XML parser error" on import of preliminary exported template.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-09-09      new-826: Native unicode support for Microsoft SQL Server.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/fields.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_STATE_NAME', 50);
define('MAX_STATE_ABBR', 50);
/**#@-*/

/**#@+
 * State type.
 */
define('STATE_TYPE_INITIAL',      1);
define('STATE_TYPE_INTERMEDIATE', 2);
define('STATE_TYPE_FINAL',        3);
/**#@-*/

/**#@+
 * State responsibility.
 */
define('STATE_RESPONSIBLE_REMAIN', 1);
define('STATE_RESPONSIBLE_ASSIGN', 2);
define('STATE_RESPONSIBLE_REMOVE', 3);
/**#@-*/

/**#@+
 * State role.
 */
define('STATE_ROLE_AUTHOR',      -1);
define('STATE_ROLE_RESPONSIBLE', -2);
define('STATE_ROLE_REGISTERED',  -3);
define('MIN_STATE_ROLE', STATE_ROLE_REGISTERED);
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified state.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id State ID}.
 * @return array Array with data if template is found in database, FALSE otherwise.
 */
function state_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[state_find]');
    debug_write_log(DEBUG_DUMP,  '[state_find] $id = ' . $id);

    $rs = dal_query('states/fndid.sql', $id);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing states of specified template,
 * sorted in accordance with current sort mode.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_templates_template_id Template ID}.
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_STATES_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_STATES_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of states.
 */
function state_list ($id, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[state_list]');
    debug_write_log(DEBUG_DUMP,  '[state_list] $id = ' . $id);

    $sort_modes = array
    (
        1 => 'state_name asc',
        2 => 'state_abbr asc',
        3 => 'state_type asc, state_name asc',
        4 => 'responsible asc, state_name asc',
        5 => 'state_name desc',
        6 => 'state_abbr desc',
        7 => 'state_type desc, state_name desc',
        8 => 'responsible desc, state_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_STATES_SORT, 3));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_STATES_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_STATES_SORT, $sort);
    save_cookie(COOKIE_STATES_PAGE, $page);

    return dal_query('states/list.sql', $id, $sort_modes[$sort]);
}

/**
 * Validates state information before creation or modification.
 *
 * @param string $state_name {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_name State name}.
 * @param string $state_abbr {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_abbr State abbreviation}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * </ul>
 */
function state_validate ($state_name, $state_abbr)
{
    debug_write_log(DEBUG_TRACE, '[state_validate]');
    debug_write_log(DEBUG_DUMP,  '[state_validate] $state_name = ' . $state_name);
    debug_write_log(DEBUG_DUMP,  '[state_validate] $state_abbr = ' . $state_abbr);

    if (ustrlen($state_name) == 0 ||
        ustrlen($state_abbr) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[state_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    return NO_ERROR;
}

/**
 * Creates new state.
 *
 * @param int $template_id {@link http://www.etraxis.org/docs-schema.php#tbl_templates_template_id ID} of template which new state will belong to.
 * @param string $state_name {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_name State name}.
 * @param string $state_abbr {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_abbr State abbreviation}.
 * @param int $state_type {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_type Type of state}.
 * @param int $next_state_id {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id ID} of state, which should be next by default in this dataflow (NULL by default).
 * @param int $responsible {@link http://www.etraxis.org/docs-schema.php#tbl_templates_description State responsibility} ({@STATE_RESPONSIBLE_REMOVE} by default).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - state is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - state with specified {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_name name}
 * or {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_abbr abbreviation} already exists</li>
 * <li>{@link ERROR_NOT_FOUND} - failure on attempt to create state</li>
 * </ul>
 */
function state_create ($template_id, $state_name, $state_abbr, $state_type, $next_state_id = NULL, $responsible = STATE_RESPONSIBLE_REMOVE)
{
    debug_write_log(DEBUG_TRACE, '[state_create]');
    debug_write_log(DEBUG_DUMP,  '[state_create] $template_id   = ' . $template_id);
    debug_write_log(DEBUG_DUMP,  '[state_create] $state_name    = ' . $state_name);
    debug_write_log(DEBUG_DUMP,  '[state_create] $state_abbr    = ' . $state_abbr);
    debug_write_log(DEBUG_DUMP,  '[state_create] $state_type    = ' . $state_type);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $next_state_id = ' . $next_state_id);
    debug_write_log(DEBUG_DUMP,  '[state_create] $responsible   = ' . $responsible);

    // Check that there is no state with the same name or abbreviation in the specified template.
    $rs = dal_query('states/fndk.sql', $template_id, ustrtolower($state_name), ustrtolower($state_abbr));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[state_create] State already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create a state.
    dal_query('states/create.sql',
              $template_id,
              $state_name,
              $state_abbr,
              $state_type,
              is_null($next_state_id) ? NULL : $next_state_id,
              $responsible);

    $rs = dal_query('states/fndk.sql', $template_id, ustrtolower($state_name), ustrtolower($state_abbr));

    if ($rs->rows == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[state_create] State cannot be found.');
        return ERROR_NOT_FOUND;
    }

    return NO_ERROR;
}

/**
 * Modifies specified state.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id ID} of state to be modified.
 * @param int $template_id {@link http://www.etraxis.org/docs-schema.php#tbl_templates_template_id ID} of template which the state belongs to.
 * @param string $state_name New {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_name state name}.
 * @param string $state_abbr New {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_abbr state abbreviation}.
 * @param int $next_state_id New {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id ID} of state, which will be next by default in this dataflow.
 * @param int $responsible New {@link http://www.etraxis.org/docs-schema.php#tbl_templates_description state responsibility}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - state is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - state with specified {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_name name}
 * or {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_abbr abbreviation} already exists</li>
 * </ul>
 */
function state_modify ($id, $template_id, $state_name, $state_abbr, $next_state_id, $responsible)
{
    debug_write_log(DEBUG_TRACE, '[state_modify]');
    debug_write_log(DEBUG_DUMP,  '[state_modify] $id            = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $template_id   = ' . $template_id);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $state_name    = ' . $state_name);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $state_abbr    = ' . $state_abbr);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $next_state_id = ' . $next_state_id);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $responsible   = ' . $responsible);

    // Check that there is no state with the same name or abbreviation, besides this one.
    $rs = dal_query('states/fndku.sql', $id, $template_id, ustrtolower($state_name), ustrtolower($state_abbr));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[state_modify] State already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the state.
    dal_query('states/modify.sql',
              $id,
              $state_name,
              $state_abbr,
              is_null($next_state_id) ? NULL : $next_state_id,
              $responsible);

    return NO_ERROR;
}

/**
 * Checks whether state can be deleted.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id ID} of state to be deleted.
 * @return bool TRUE if state can be deleted, FALSE otherwise.
 */
function is_state_removable ($id)
{
    debug_write_log(DEBUG_TRACE, '[is_state_removable]');
    debug_write_log(DEBUG_DUMP,  '[is_state_removable] $id = ' . $id);

    $rs = dal_query('states/efndc.sql', $id);

    return ($rs->fetch(0) == 0);
}

/**
 * Deletes specified state.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id ID} of state to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function state_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[state_delete]');
    debug_write_log(DEBUG_DUMP,  '[state_delete] $id = ' . $id);

    dal_query('filters/fadelalls.sql', $id);
    dal_query('filters/fdelalls.sql',  $id);

    dal_query('states/rdelall.sql',  $id);
    dal_query('states/ftdelall.sql', $id);
    dal_query('states/fsdelall.sql', $id);
    dal_query('states/lvdelall.sql', $id);
    dal_query('states/fpdelall.sql', $id);
    dal_query('states/fdelall.sql',  $id);
    dal_query('states/gtdelall.sql', $id);
    dal_query('states/rtdelall.sql', $id);
    dal_query('states/delete.sql',   $id);

    return NO_ERROR;
}

/**
 * Marks specified state as initial in its dataflow.
 *
 * @param int $template_id {@link http://www.etraxis.org/docs-schema.php#tbl_templates_template_id ID} of template which the state belongs to.
 * @param int $state_id {@link http://www.etraxis.org/docs-schema.php#tbl_states_state_id ID} of state to be made initial.
 * @return int Always {@link NO_ERROR}.
 */
function state_set_initial ($template_id, $state_id)
{
    debug_write_log(DEBUG_TRACE, '[state_set_initial]');
    debug_write_log(DEBUG_DUMP,  '[state_set_initial] $template_id = ' . $template_id);
    debug_write_log(DEBUG_DUMP,  '[state_set_initial] $state_id    = ' . $state_id);

    dal_query('states/clrinit.sql', $template_id);
    dal_query('states/setinit.sql', $template_id, $state_id);

    return NO_ERROR;
}

/**
 * Exports all states of the specified template to XML code (see also {@link template_export}).
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_templates_template_id ID} of template, which states should be exported.
 * @param array &$groups Array of IDs of groups, affected by this template (used for output only).
 * @return string Generated XML code.
 */
function state_export ($id, &$groups)
{
    debug_write_log(DEBUG_TRACE, '[state_export]');
    debug_write_log(DEBUG_DUMP,  '[state_export] $id = ' . $id);

    // Allocation of state types to XML code.
    $state_type = array
    (
        STATE_TYPE_INITIAL      => 'initial',
        STATE_TYPE_INTERMEDIATE => 'intermed',
        STATE_TYPE_FINAL        => 'final',
    );

    // Allocation of state responsibility to XML code.
    $state_resp = array
    (
        STATE_RESPONSIBLE_REMAIN => 'remain',
        STATE_RESPONSIBLE_ASSIGN => 'assign',
        STATE_RESPONSIBLE_REMOVE => 'remove',
    );

    $xml = "    <states>\n";

    // List all states of the template.
    $rs = dal_query('states/list2.sql', $id);

    // Add XML code for each found state.
    while (($state = $rs->fetch()))
    {
        // Add XML code for general state information.
        $xml .= sprintf("      <state name=\"%s\" abbr=\"%s\" type=\"%s\" responsible=\"%s\"",
                        ustr2html($state['state_name']),
                        ustr2html($state['state_abbr']),
                        $state_type[$state['state_type']],
                        $state_resp[$state['responsible']]);

        // Add XML code for "next state by default", if such information is specified for the state.
        $xml .= (is_null($state['next_state']) ? ">\n" : " next=\"" . ustr2html($state['next_state']) . "\">\n");

        // If state is not final, enumerate all possible transition from this state.
        if ($state['state_type'] != STATE_TYPE_FINAL)
        {
            $xml .= "        <transitions>\n";

            // List all transitions for system role "author".
            $rst = dal_query('states/rtlist2.sql', $state['state_id'], STATE_ROLE_AUTHOR);

            if ($rst->rows != 0)
            {
                $xml .= "          <author>\n";

                while (($next = $rst->fetch()))
                {
                    $xml .= "            <state>" . ustr2html($next['state_name']) . "</state>\n";
                }

                $xml .= "          </author>\n";
            }

            // List all transitions for system role "responsible".
            $rst = dal_query('states/rtlist2.sql', $state['state_id'], STATE_ROLE_RESPONSIBLE);

            if ($rst->rows != 0)
            {
                $xml .= "          <responsible>\n";

                while (($next = $rst->fetch()))
                {
                    $xml .= "            <state>" . ustr2html($next['state_name']) . "</state>\n";
                }

                $xml .= "          </responsible>\n";
            }

            // List all transitions for system role "registered".
            $rst = dal_query('states/rtlist2.sql', $state['state_id'], STATE_ROLE_REGISTERED);

            if ($rst->rows != 0)
            {
                $xml .= "          <registered>\n";

                while (($next = $rst->fetch()))
                {
                    $xml .= "            <state>" . ustr2html($next['state_name']) . "</state>\n";
                }

                $xml .= "          </registered>\n";
            }

            // Enumerate local groups of the same project and all global groups.
            $rsg = dal_query('groups/list.sql', $state['project_id'], 'is_global, group_name');

            while (($group = $rsg->fetch()))
            {
                // List all transitions for this group.
                $rst = dal_query('states/gtlist2.sql', $state['state_id'], $group['group_id']);

                if ($rst->rows != 0)
                {
                    // Save ID of processed group for future reference.
                    array_push($groups, $group['group_id']);

                    // Add XML code for group name and type.
                    $xml .= sprintf("          <group name=\"%s\" type=\"%s\">\n",
                                    ustr2html($group['group_name']),
                                    (is_null($group['project_id']) ? 'global' : 'local'));

                    // Add XML code for transition information.
                    while (($next = $rst->fetch()))
                    {
                        $xml .= "            <state>" . ustr2html($next['state_name']) . "</state>\n";
                    }

                    $xml .= "          </group>\n";
                }
            }

            $xml .= "        </transitions>\n";
        }

        // Export all existing fields of the state.
        $xml .= field_export($state['state_id'], $groups);
        $xml .= "      </state>\n";
    }

    $xml .= "    </states>\n";

    return $xml;
}

?>
