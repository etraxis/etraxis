<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2005-2011  Artem Rodygin
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
 * States
 *
 * This module provides API to work with eTraxis states.
 * See also {@link http://code.google.com/p/etraxis/wiki/DatabaseSchema#tbl_states tbl_states} database table.
 *
 * @package DBO
 * @subpackage States
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/fields.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

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

// State type resources.
$state_type_res = array
(
    STATE_TYPE_INITIAL      => RES_INITIAL_ID,
    STATE_TYPE_INTERMEDIATE => RES_INTERMEDIATE_ID,
    STATE_TYPE_FINAL        => RES_FINAL_ID,
);

/**#@+
 * State responsibility.
 */
define('STATE_RESPONSIBLE_REMAIN', 1);
define('STATE_RESPONSIBLE_ASSIGN', 2);
define('STATE_RESPONSIBLE_REMOVE', 3);
/**#@-*/

// State responsibility resources.
$state_responsible_res = array
(
    STATE_RESPONSIBLE_REMAIN => RES_REMAIN_ID,
    STATE_RESPONSIBLE_ASSIGN => RES_ASSIGN_ID,
    STATE_RESPONSIBLE_REMOVE => RES_REMOVE_ID,
);

/**#@+
 * State role.
 */
define('STATE_ROLE_AUTHOR',      -1);
define('STATE_ROLE_RESPONSIBLE', -2);
define('STATE_ROLE_REGISTERED',  -3);
define('MIN_STATE_ROLE', STATE_ROLE_REGISTERED);
/**#@-*/

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified state.
 *
 * @param int $id State ID.
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
 * @param int $id Template ID.
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_STATES_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_STATES_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of states.
 */
function states_list ($id, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[states_list]');
    debug_write_log(DEBUG_DUMP,  '[states_list] $id = ' . $id);

    $sort_modes = array
    (
        1  => 'state_name asc',
        2  => 'state_abbr asc',
        3  => 'state_type asc, state_name asc',
        4  => 'responsible asc, state_name asc',
        5  => 'next_state asc, state_name asc',
        6  => 'state_name desc',
        7  => 'state_abbr desc',
        8  => 'state_type desc, state_name desc',
        9  => 'responsible desc, state_name desc',
        10 => 'next_state desc, state_name desc',
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
 * @param string $state_name State name.
 * @param string $state_abbr State abbreviation.
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
 * @param int $template_id ID of template which new state will belong to.
 * @param string $state_name State name.
 * @param string $state_abbr State abbreviation.
 * @param int $state_type Type of state.
 * @param int $next_state_id ID of state, which should be next by default in this dataflow (NULL by default).
 * @param int $responsible State responsibility ({@STATE_RESPONSIBLE_REMOVE} by default).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - state is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - state with specified name or abbreviation already exists</li>
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
    debug_write_log(DEBUG_DUMP,  '[state_create] $next_state_id = ' . $next_state_id);
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
 * @param int $id ID of state to be modified.
 * @param int $template_id ID of template which the state belongs to.
 * @param string $state_old_name Current state name.
 * @param string $state_new_name New state name.
 * @param string $state_abbr New state abbreviation.
 * @param int $next_state_id New ID of state, which will be next by default in this dataflow.
 * @param int $responsible New state responsibility.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - state is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - state with specified name or abbreviation already exists</li>
 * </ul>
 */
function state_modify ($id, $template_id, $state_old_name, $state_new_name, $state_abbr, $next_state_id, $responsible)
{
    debug_write_log(DEBUG_TRACE, '[state_modify]');
    debug_write_log(DEBUG_DUMP,  '[state_modify] $id             = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $template_id    = ' . $template_id);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $state_old_name = ' . $state_old_name);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $state_new_name = ' . $state_new_name);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $state_abbr     = ' . $state_abbr);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $next_state_id  = ' . $next_state_id);
    debug_write_log(DEBUG_DUMP,  '[state_modify] $responsible    = ' . $responsible);

    // Check that there is no state with the same name or abbreviation, besides this one.
    $rs = dal_query('states/fndku.sql', $id, $template_id, ustrtolower($state_new_name), ustrtolower($state_abbr));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[state_modify] State already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Update existing views.
    dal_query('states/views.sql',
              $state_old_name,
              $state_new_name);

    // Modify the state.
    dal_query('states/modify.sql',
              $id,
              $state_new_name,
              $state_abbr,
              is_null($next_state_id) ? NULL : $next_state_id,
              $responsible);

    return NO_ERROR;
}

/**
 * Checks whether state can be deleted.
 *
 * @param int $id ID of state to be deleted.
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
 * @param int $id ID of state to be deleted.
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
    dal_query('states/sadelall.sql', $id);
    dal_query('states/clrdef.sql',   $id);
    dal_query('states/delete.sql',   $id);

    return NO_ERROR;
}

/**
 * Marks specified state as initial in its dataflow.
 *
 * @param int $template_id ID of template which the state belongs to.
 * @param int $state_id ID of state to be made initial.
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
 * @param int $id ID of template, which states should be exported.
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

        // If state must be assigned, enumerate groups of allowed responsibles.
        if ($state['responsible'] == STATE_RESPONSIBLE_ASSIGN)
        {
            $rsr = dal_query('states/saallowed.sql', $state['state_id']);

            if ($rsr->rows != 0)
            {
                $xml .= "        <responsibles>\n";

                while (($row = $rsr->fetch()))
                {
                    $xml .= sprintf("          <group type=\"%s\">%s</group>\n",
                                    ($row['is_global'] ? 'global' : 'local'),
                                    ustr2html($row['group_name']));
                }

                $xml .= "        </responsibles>\n";
            }
        }

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

/**
 * Imports states described as XML code into the specified template.
 *
 * @param int $template_id ID of destination template.
 * @param string $xml Valid XML code.
 * @param string &$error In case of failure - the error message (used as output only).
 * @return bool Whether the import was successful.
 */
function states_import ($template_id, $xml, &$error)
{
    debug_write_log(DEBUG_TRACE, '[states_import]');
    debug_write_log(DEBUG_DUMP,  '[states_import] $template_id = ' . $template_id);

    // Allocation of XML code to state types.
    $state_type = array
    (
        'initial'  => STATE_TYPE_INITIAL,
        'intermed' => STATE_TYPE_INTERMEDIATE,
        'final'    => STATE_TYPE_FINAL,
    );

    // Allocation of XML code to state responsibility.
    $state_resp = array
    (
        'remain' => STATE_RESPONSIBLE_REMAIN,
        'assign' => STATE_RESPONSIBLE_ASSIGN,
        'remove' => STATE_RESPONSIBLE_REMOVE,
    );

    // Enumerate states.
    $states = $xml->xpath('./states/state');

    if ($states !== FALSE)
    {
        // Create all states before setting transitions btw them.
        foreach ($states as $state)
        {
            $state['name'] = ustrcut($state['name'], MAX_STATE_NAME);
            $state['abbr'] = ustrcut($state['abbr'], MAX_STATE_ABBR);

            // Validate state.
            switch (state_validate($state['name'], $state['abbr']))
            {
                case NO_ERROR:
                    break;  // nop
                case ERROR_INCOMPLETE_FORM:
                    $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                    return FALSE;
                default:
                    debug_write_log(DEBUG_WARNING, '[states_import] State validation failure.');
                    $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                    return FALSE;
            }

            $type        = strval($state['type']);
            $responsible = strval($state['responsible']);

            if (!array_key_exists($type, $state_type))
            {
                $type = 'intermed';
            }

            if (!array_key_exists($responsible, $state_resp))
            {
                $responsible = 'remain';
            }

            // Create state.
            switch (state_create($template_id,
                                 $state['name'],
                                 $state['abbr'],
                                 $state_type[$type],
                                 NULL,
                                 $state_resp[$responsible]))
            {
                case NO_ERROR:
                    break;  // nop
                case ERROR_ALREADY_EXISTS:
                    $error = get_html_resource(RES_ALERT_STATE_ALREADY_EXISTS_ID);
                    return FALSE;
                default:
                    debug_write_log(DEBUG_WARNING, '[states_import] State creation failure.');
                    $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                    return FALSE;
            }
        }

        // Set up rest of the stuff on already created states.
        foreach ($states as $state)
        {
            $state['name'] = ustrcut($state['name'], MAX_STATE_NAME);

            // Find the state.
            $rs = dal_query('states/fndk2.sql', $template_id, ustrtolower($state['name']));

            if ($rs->rows == 0)
            {
                debug_write_log(DEBUG_WARNING, '[states_import] Created state not found.');
                $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                return FALSE;
            }

            $row        = $rs->fetch();
            $project_id = $row['project_id'];
            $state_id   = $row['state_id'];

            // Set author transitions.
            $transitions = $state->xpath('./transitions/author/state');

            if ($transitions !== FALSE)
            {
                foreach ($transitions as $transit)
                {
                    $rs = dal_query('states/fndk2.sql', $template_id, ustrtolower(ustrcut($transit, MAX_STATE_NAME)));

                    if ($rs->rows != 0)
                    {
                        dal_query('states/rtadd.sql', $state_id, $rs->fetch('state_id'), STATE_ROLE_AUTHOR);
                    }
                }
            }

            // Set responsible transitions.
            $transitions = $state->xpath('./transitions/responsible/state');

            if ($transitions !== FALSE)
            {
                foreach ($transitions as $transit)
                {
                    $rs = dal_query('states/fndk2.sql', $template_id, ustrtolower(ustrcut($transit, MAX_STATE_NAME)));

                    if ($rs->rows != 0)
                    {
                        dal_query('states/rtadd.sql', $state_id, $rs->fetch('state_id'), STATE_ROLE_RESPONSIBLE);
                    }
                }
            }

            // Set registered transitions.
            $transitions = $state->xpath('./transitions/registered/state');

            if ($transitions !== FALSE)
            {
                foreach ($transitions as $transit)
                {
                    $rs = dal_query('states/fndk2.sql', $template_id, ustrtolower(ustrcut($transit, MAX_STATE_NAME)));

                    if ($rs->rows != 0)
                    {
                        dal_query('states/rtadd.sql', $state_id, $rs->fetch('state_id'), STATE_ROLE_REGISTERED);
                    }
                }
            }

            // Enumerate groups transitions.
            $groups = $state->xpath('./transitions/group');

            if ($groups !== FALSE)
            {
                foreach ($groups as $group)
                {
                    // Set group transitions.
                    if (isset($group->state))
                    {
                        $rs = dal_query('groups/fndk.sql',
                                        $group['type'] == 'global' ? 'is null' : '=' . $project_id,
                                        ustrtolower(ustrcut($group['name'], MAX_GROUP_NAME)));

                        if ($rs->rows != 0)
                        {
                            $group_id = $rs->fetch('group_id');

                            foreach ($group->state as $transit)
                            {
                                $rs = dal_query('states/fndk2.sql', $template_id, ustrtolower(ustrcut($transit, MAX_STATE_NAME)));

                                if ($rs->rows != 0)
                                {
                                    dal_query('states/gtadd.sql', $state_id, $rs->fetch('state_id'), $group_id);
                                }
                            }
                        }
                    }
                }
            }

            // Set groups of allowed responsibles.
            if (strval($state['responsible']) == 'assign')
            {
                $groups = $state->xpath('./responsibles/group');

                if ($groups !== FALSE)
                {
                    foreach ($groups as $group)
                    {
                        $rs = dal_query('groups/fndk.sql',
                                        $group['type'] == 'global' ? 'is null' : '=' . $project_id,
                                        ustrtolower(ustrcut($group, MAX_GROUP_NAME)));

                        if ($rs->rows != 0)
                        {
                            dal_query('states/saadd.sql', $state_id, $rs->fetch('group_id'));
                        }
                    }
                }
            }

            // Set "Next by default" where it's specified.
            if (isset($state['next']))
            {
                $rs = dal_query('states/fndk2.sql', $template_id, ustrtolower(ustrcut($state['next'], MAX_STATE_NAME)));

                if ($rs->rows != 0)
                {
                    dal_query('states/setnext.sql', $state_id, $rs->fetch('state_id'));
                }
            }

            // Import fields.
            if (!fields_import($template_id, $state_id, $state, $error))
            {
                return FALSE;
            }
        }
    }

    return TRUE;
}

?>
