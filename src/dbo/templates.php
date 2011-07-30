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
 * Templates
 *
 * This module provides API to work with eTraxis templates.
 * See also {@link http://code.google.com/p/etraxis/wiki/DatabaseSchema#tbl_templates tbl_templates} database table.
 *
 * @package DBO
 * @subpackage Templates
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/groups.php');
require_once('../dbo/states.php');
require_once('../dbo/events.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_TEMPLATE_NAME',        50);
define('MAX_TEMPLATE_PREFIX',      3);
define('MAX_TEMPLATE_DESCRIPTION', 100);
define('MIN_TEMPLATE_DAYS_COUNT',  1);
define('MAX_TEMPLATE_DAYS_COUNT',  100);
/**#@-*/

/**#@+
 * Template role.
 */
define('TEMPLATE_ROLE_AUTHOR',      -1);
define('TEMPLATE_ROLE_RESPONSIBLE', -2);
define('TEMPLATE_ROLE_REGISTERED',  -3);
define('MIN_TEMPLATE_ROLE', TEMPLATE_ROLE_REGISTERED);
/**#@-*/

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified template.
 *
 * @param int $id Template ID.
 * @return array Array with data if template is found in database, FALSE otherwise.
 */
function template_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[template_find]');
    debug_write_log(DEBUG_DUMP,  '[template_find] $id = ' . $id);

    $rs = dal_query('templates/fndid.sql', $id);

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing templates of specified project,
 * sorted in accordance with current sort mode.
 *
 * @param int $id Project ID.
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_TEMPLATES_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_TEMPLATES_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of templates.
 */
function templates_list ($id, &$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[templates_list]');
    debug_write_log(DEBUG_DUMP,  '[templates_list] $id = ' . $id);

    $sort_modes = array
    (
        1  => 'template_name asc',
        2  => 'template_prefix asc',
        3  => 'critical_age asc, template_name asc',
        4  => 'frozen_time asc, template_name asc',
        5  => 'description asc, template_name asc',
        6  => 'template_name desc',
        7  => 'template_prefix desc',
        8  => 'critical_age desc, template_name desc',
        9  => 'frozen_time desc, template_name desc',
        10 => 'description desc, template_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_TEMPLATES_SORT));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_TEMPLATES_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_TEMPLATES_SORT, $sort);
    save_cookie(COOKIE_TEMPLATES_PAGE, $page);

    return dal_query('templates/list.sql', $id, $sort_modes[$sort]);
}

/**
 * Validates template information before creation or modification.
 *
 * @param string $template_name Template name.
 * @param string $template_prefix Template prefix.
 * @param int $critical_age Critical age.
 * @param int $frozen_time Frozen time.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * <li>{@link ERROR_INVALID_INTEGER_VALUE} - value of $critical_age or $frozen_time is not an integer</li>
 * <li>{@link ERROR_INTEGER_VALUE_OUT_OF_RANGE} - value of $critical_age or $frozen_time is out of valid range</li>
 * </ul>
 */
function template_validate ($template_name, $template_prefix, $critical_age, $frozen_time)
{
    debug_write_log(DEBUG_TRACE, '[template_validate]');
    debug_write_log(DEBUG_DUMP,  '[template_validate] $template_name   = ' . $template_name);
    debug_write_log(DEBUG_DUMP,  '[template_validate] $template_prefix = ' . $template_prefix);
    debug_write_log(DEBUG_DUMP,  '[template_validate] $critical_age    = ' . $critical_age);
    debug_write_log(DEBUG_DUMP,  '[template_validate] $frozen_time     = ' . $frozen_time);

    if (ustrlen($template_name)   == 0 ||
        ustrlen($template_prefix) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[template_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    // Validate 'Critical age'.
    if (ustrlen($critical_age) != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[template_validate] Validate critical age value.');

        if (!is_intvalue($critical_age))
        {
            debug_write_log(DEBUG_NOTICE, '[template_validate] Invalid critical age value.');
            return ERROR_INVALID_INTEGER_VALUE;
        }

        if ($critical_age < MIN_TEMPLATE_DAYS_COUNT || $critical_age > MAX_TEMPLATE_DAYS_COUNT)
        {
            debug_write_log(DEBUG_NOTICE, '[template_validate] Critical age value is out of range.');
            return ERROR_INTEGER_VALUE_OUT_OF_RANGE;
        }
    }

    // Validate 'Frozen time'.
    if (ustrlen($frozen_time) != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[template_validate] Validate frozen time value.');

        if (!is_intvalue($frozen_time))
        {
            debug_write_log(DEBUG_NOTICE, '[template_validate] Invalid frozen time value.');
            return ERROR_INVALID_INTEGER_VALUE;
        }

        if ($frozen_time < MIN_TEMPLATE_DAYS_COUNT || $frozen_time > MAX_TEMPLATE_DAYS_COUNT)
        {
            debug_write_log(DEBUG_NOTICE, '[template_validate] Frozen time value is out of range.');
            return ERROR_INTEGER_VALUE_OUT_OF_RANGE;
        }
    }

    return NO_ERROR;
}

/**
 * Creates new template.
 *
 * @param int $project_id ID of project which new template will belong to.
 * @param string $template_name Template name.
 * @param string $template_prefix Template prefix.
 * @param int $critical_age Critical age.
 * @param int $frozen_time Frozen time.
 * @param string $description Optional description.
 * @param bool $guest_access Ability of guest access to the template records.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - template is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - template with specified name or prefix already exists</li>
 * </ul>
 */
function template_create ($project_id, $template_name, $template_prefix, $critical_age, $frozen_time, $description, $guest_access)
{
    debug_write_log(DEBUG_TRACE, '[template_create]');
    debug_write_log(DEBUG_DUMP,  '[template_create] $project_id      = ' . $project_id);
    debug_write_log(DEBUG_DUMP,  '[template_create] $template_name   = ' . $template_name);
    debug_write_log(DEBUG_DUMP,  '[template_create] $template_prefix = ' . $template_prefix);
    debug_write_log(DEBUG_DUMP,  '[template_create] $critical_age    = ' . $critical_age);
    debug_write_log(DEBUG_DUMP,  '[template_create] $frozen_time     = ' . $frozen_time);
    debug_write_log(DEBUG_DUMP,  '[template_create] $description     = ' . $description);
    debug_write_log(DEBUG_DUMP,  '[template_create] $guest_access    = ' . $guest_access);

    // Check that there is no template with the same name or prefix in the specified project.
    $rs = dal_query('templates/fndk.sql', $project_id, ustrtolower($template_name), ustrtolower($template_prefix));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[template_create] Template already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create a template.
    dal_query('templates/create.sql',
              $project_id,
              $template_name,
              $template_prefix,
              ustrlen($critical_age) == 0 ? NULL : $critical_age,
              ustrlen($frozen_time)  == 0 ? NULL : $frozen_time,
              ustrlen($description)  == 0 ? NULL : $description,
              bool2sql($guest_access));

    return NO_ERROR;
}

/**
 * Clones everything (states, fields, permissions, etc) from one template to another (must be pre-created).
 *
 * @param int $source_id ID of template to be cloned.
 * @param int $dest_id ID of new template.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - template is successfully cloned</li>
 * <li>{@link ERROR_NOT_FOUND} - at least one of specified templates cannot be found</li>
 * </ul>
 */
function template_clone ($source_id, $dest_id)
{
    debug_write_log(DEBUG_TRACE, '[template_clone]');
    debug_write_log(DEBUG_DUMP,  '[template_clone] $source_id = ' . $source_id);
    debug_write_log(DEBUG_DUMP,  '[template_clone] $dest_id   = ' . $dest_id);

    $source = template_find($source_id);
    $dest   = template_find($dest_id);

    if (!$source || !$dest)
    {
        debug_write_log(DEBUG_NOTICE, '[template_clone] Template cannot be found.');
        return ERROR_NOT_FOUND;
    }

    dal_query('templates/gpclone.sql', $source_id, $dest_id, $source['project_id'], $dest['project_id']);
    dal_query('templates/sclone.sql',  $source_id, $dest_id);
    dal_query('templates/gtclone.sql', $source_id, $dest_id, $source['project_id'], $dest['project_id']);
    dal_query('templates/rtclone.sql', $source_id, $dest_id);
    dal_query('templates/fclone.sql',  $source_id, $dest_id);
    dal_query('templates/lvclone.sql', $source_id, $dest_id);
    dal_query('templates/fpclone.sql', $source_id, $dest_id, $source['project_id'], $dest['project_id']);

    template_author_perm_set      ($dest_id, $source['author_perm']);
    template_responsible_perm_set ($dest_id, $source['responsible_perm']);
    template_registered_perm_set  ($dest_id, $source['registered_perm']);

    return NO_ERROR;
}

/**
 * Modifies specified template.
 *
 * @param int $id ID of template to be modified.
 * @param int $project_id ID of project which the template belongs to.
 * @param string $template_name New template name.
 * @param string $template_prefix New template prefix.
 * @param int $critical_age New critical age.
 * @param int $frozen_time New frozen time.
 * @param string $description New description.
 * @param bool $guest_access Ability of guest access to the template records.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - template is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - another template with specified name or prefix already exists</li>
 * </ul>
 */
function template_modify ($id, $project_id, $template_name, $template_prefix, $critical_age, $frozen_time, $description, $guest_access)
{
    debug_write_log(DEBUG_TRACE, '[template_modify]');
    debug_write_log(DEBUG_DUMP,  '[template_modify] $id              = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[template_modify] $project_id      = ' . $project_id);
    debug_write_log(DEBUG_DUMP,  '[template_modify] $template_name   = ' . $template_name);
    debug_write_log(DEBUG_DUMP,  '[template_modify] $template_prefix = ' . $template_prefix);
    debug_write_log(DEBUG_DUMP,  '[template_modify] $critical_age    = ' . $critical_age);
    debug_write_log(DEBUG_DUMP,  '[template_modify] $frozen_time     = ' . $frozen_time);
    debug_write_log(DEBUG_DUMP,  '[template_modify] $description     = ' . $description);
    debug_write_log(DEBUG_DUMP,  '[template_modify] $guest_access    = ' . $guest_access);

    // Check that there is no template with the same name or prefix, besides this one.
    $rs = dal_query('templates/fndku.sql', $id, $project_id, ustrtolower($template_name), ustrtolower($template_prefix));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[template_modify] Template already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the template.
    dal_query('templates/modify.sql',
              $id,
              $template_name,
              $template_prefix,
              ustrlen($critical_age) == 0 ? NULL : $critical_age,
              ustrlen($frozen_time)  == 0 ? NULL : $frozen_time,
              ustrlen($description)  == 0 ? NULL : $description,
              bool2sql($guest_access));

    return NO_ERROR;
}

/**
 * Checks whether template can be deleted.
 *
 * @param int $id ID of template to be deleted.
 * @return bool TRUE if template can be deleted, FALSE otherwise.
 */
function is_template_removable ($id)
{
    debug_write_log(DEBUG_TRACE, '[is_template_removable]');
    debug_write_log(DEBUG_DUMP,  '[is_template_removable] $id = ' . $id);

    $rs = dal_query('templates/rfndc.sql', $id);

    return ($rs->fetch(0) == 0);
}

/**
 * Deletes specified template.
 *
 * @param int $id ID of template to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function template_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[template_delete]');
    debug_write_log(DEBUG_DUMP,  '[template_delete] $id = ' . $id);

    dal_query('subscriptions/sdelallt.sql', $id);

    dal_query('filters/fadelallt.sql', $id);
    dal_query('filters/fdelallt.sql',  $id);

    dal_query('templates/lvdelall.sql', $id);
    dal_query('templates/fpdelall.sql', $id);
    dal_query('templates/fsdelall.sql', $id);
    dal_query('templates/fdelall.sql',  $id);
    dal_query('templates/gtdelall.sql', $id);
    dal_query('templates/rtdelall.sql', $id);
    dal_query('templates/sadelall.sql', $id);
    dal_query('templates/sdelall.sql',  $id);
    dal_query('templates/gpdelall.sql', $id);
    dal_query('templates/delete.sql',   $id);

    return NO_ERROR;
}

/**
 * Locks specified template.
 *
 * @param int $id ID of template to be locked.
 * @return int Always {@link NO_ERROR}.
 */
function template_lock ($id)
{
    debug_write_log(DEBUG_TRACE, '[template_lock]');
    debug_write_log(DEBUG_DUMP,  '[template_lock] $id = ' . $id);

    dal_query('templates/setlock.sql', $id, 1);

    return NO_ERROR;
}

/**
 * Unlocks specified template.
 *
 * @param int $id ID of template to be unlocked.
 * @return int Always {@link NO_ERROR}.
 */
function template_unlock ($id)
{
    debug_write_log(DEBUG_TRACE, '[template_unlock]');
    debug_write_log(DEBUG_DUMP,  '[template_unlock] $id = ' . $id);

    dal_query('templates/setlock.sql', $id, 0);

    return NO_ERROR;
}

/**
 * Sets permissions of system role 'author' for specified template.
 *
 * @param int $id ID of template which permissions should be set for.
 * @param int $perm New permissions set.
 * @return int Always {@link NO_ERROR}.
 */
function template_author_perm_set ($id, $perm)
{
    debug_write_log(DEBUG_TRACE, '[template_author_perm_set]');
    debug_write_log(DEBUG_DUMP,  '[template_author_perm_set] $id   = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[template_author_perm_set] $perm = ' . $perm);

    dal_query('templates/apset.sql', $id, $perm);

    return NO_ERROR;
}

/**
 * Sets permissions of system role 'responsible' for specified template.
 *
 * @param int $id ID of template which permissions should be set for.
 * @param int $perm New permissions set.
 * @return int Always {@link NO_ERROR}.
 */
function template_responsible_perm_set ($id, $perm)
{
    debug_write_log(DEBUG_TRACE, '[template_responsible_perm_set]');
    debug_write_log(DEBUG_DUMP,  '[template_responsible_perm_set] $id   = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[template_responsible_perm_set] $perm = ' . $perm);

    dal_query('templates/rpset.sql', $id, $perm);

    return NO_ERROR;
}

/**
 * Sets permissions of system role 'registered' for specified template.
 *
 * @param int $id ID of template which permissions should be set for.
 * @param int $perm New permissions set.
 * @return int Always {@link NO_ERROR}.
 */
function template_registered_perm_set ($id, $perm)
{
    debug_write_log(DEBUG_TRACE, '[template_registered_perm_set]');
    debug_write_log(DEBUG_DUMP,  '[template_registered_perm_set] $id   = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[template_registered_perm_set] $perm = ' . $perm);

    dal_query('templates/r2pset.sql', $id, $perm);

    return NO_ERROR;
}

/**
 * Exports specified template to XML code (see also {@link project_export}).
 *
 * @param int $id ID of template to be exported.
 * @param bool $just_the_node Whether the function should return the XML code of the template node alone instead of a complete XML schema.
 * @return string Generated XML code for specified template.
 */
function template_export ($id, $just_the_node = FALSE)
{
    debug_write_log(DEBUG_TRACE, '[template_export]');
    debug_write_log(DEBUG_DUMP,  '[template_export] $id            = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[template_export] $just_the_node = ' . $just_the_node);

    // Allocation of permissions to XML code.
    $permissions = array
    (
        PERMIT_CREATE_RECORD         => 'create',
        PERMIT_MODIFY_RECORD         => 'modify',
        PERMIT_POSTPONE_RECORD       => 'postpone',
        PERMIT_RESUME_RECORD         => 'resume',
        PERMIT_REASSIGN_RECORD       => 'reassign',
        PERMIT_ADD_COMMENTS          => 'comment',
        PERMIT_ATTACH_FILES          => 'attach',
        PERMIT_REMOVE_FILES          => 'remove',
        PERMIT_CONFIDENTIAL_COMMENTS => 'secret',
        PERMIT_SEND_REMINDERS        => 'remind',
        PERMIT_DELETE_RECORD         => 'delete',
        PERMIT_ADD_SUBRECORDS        => 'addsubrec',
        PERMIT_REMOVE_SUBRECORDS     => 'remsubrec',
        PERMIT_VIEW_RECORD           => 'view',
    );

    // Find the template.
    $template = template_find($id);

    if (!$template)
    {
        return NULL;
    }

    $groups = array();

    // Generate XML code for general template information.
    $xml = sprintf("  <template name=\"%s\" prefix=\"%s\" description=\"%s\" critical_age=\"%s\" frozen_time=\"%s\" guest_access=\"%s\">\n",
                   ustr2html($template['template_name']),
                   ustr2html($template['template_prefix']),
                   ustr2html($template['description']),
                   $template['critical_age'],
                   $template['frozen_time'],
                   ($template['guest_access'] ? 'yes' : 'no'));

    $xml .= "    <permissions>\n";

    // Add XML code for template "author" permissions.
    if ($template['author_perm'] != 0)
    {
        $xml .= "      <author>\n";

        foreach ($permissions as $flag => $permit)
        {
            $xml .= (($template['author_perm'] & $flag) == 0 ? NULL : "        <permit>{$permit}</permit>\n");
        }

        $xml .= "      </author>\n";
    }

    // Add XML code for template "responsible" permissions.
    if ($template['responsible_perm'] != 0)
    {
        $xml .= "      <responsible>\n";

        foreach ($permissions as $flag => $permit)
        {
            $xml .= (($template['responsible_perm'] & $flag) == 0 ? NULL : "        <permit>{$permit}</permit>\n");
        }

        $xml .= "      </responsible>\n";
    }

    // Add XML code for template "registered" permissions.
    if ($template['registered_perm'] != 0)
    {
        $xml .= "      <registered>\n";

        foreach ($permissions as $flag => $permit)
        {
            $xml .= (($template['registered_perm'] & $flag) == 0 ? NULL : "        <permit>{$permit}</permit>\n");
        }

        $xml .= "      </registered>\n";
    }

    // Enumerate local groups of the same project and all global groups.
    $rs = dal_query('groups/gplist3.sql', $id);

    while (($group = $rs->fetch()))
    {
        // Add XML code for template permissions of each group that has ones.
        if ($group['perms'] != 0)
        {
            // Save ID of processed group for future reference.
            array_push($groups, $group['group_id']);

            // Add XML code for group name and type.
            $xml .= sprintf("      <group name=\"%s\" type=\"%s\">\n",
                              ustr2html($group['group_name']),
                              (is_null($group['project_id']) ? 'global' : 'local'));

            // Add XML code for permissions information.
            foreach ($permissions as $flag => $permit)
            {
                $xml .= (($group['perms'] & $flag) == 0 ? NULL : "        <permit>{$permit}</permit>\n");
            }

            $xml .= "      </group>\n";
        }
    }

    $xml .= "    </permissions>\n";

    // Export all existing states of the template.
    $xml .= state_export($id, $groups);
    $xml .= "  </template>\n";

    if ($just_the_node)
    {
        return $xml;
    }

    // Merge project, groups, and template.
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
         . sprintf("<project name=\"%s\" description=\"%s\">\n",
                   ustr2html($template['project_name']),
                   ustr2html($template['p_description']))
         . groups_export($groups)
         . $xml
         . "</project>\n";

    return $xml;
}

/**
 * Imports templates described as XML code into the specified project.
 *
 * @param int $project_id ID of destination project.
 * @param string $xml Valid XML code.
 * @param string &$error In case of failure - the error message (used as output only).
 * @return bool Whether the import was successful.
 */
function templates_import ($project_id, $xml, &$error)
{
    debug_write_log(DEBUG_TRACE, '[templates_import]');
    debug_write_log(DEBUG_DUMP,  '[templates_import] $project_id = ' . $project_id);

    // Allocation of XML code to permissions.
    $permissions = array
    (
        'create'    => PERMIT_CREATE_RECORD,
        'modify'    => PERMIT_MODIFY_RECORD,
        'postpone'  => PERMIT_POSTPONE_RECORD,
        'resume'    => PERMIT_RESUME_RECORD,
        'reassign'  => PERMIT_REASSIGN_RECORD,
        'comment'   => PERMIT_ADD_COMMENTS,
        'attach'    => PERMIT_ATTACH_FILES,
        'remove'    => PERMIT_REMOVE_FILES,
        'secret'    => PERMIT_CONFIDENTIAL_COMMENTS,
        'remind'    => PERMIT_SEND_REMINDERS,
        'delete'    => PERMIT_DELETE_RECORD,
        'addsubrec' => PERMIT_ADD_SUBRECORDS,
        'remsubrec' => PERMIT_REMOVE_SUBRECORDS,
        'view'      => PERMIT_VIEW_RECORD,
    );

    // Enumerate templates.
    $templates = $xml->xpath('/project/template');

    if ($templates !== FALSE)
    {
        foreach ($templates as $template)
        {
            $rs = dal_query('templates/count.sql');

            if (MAX_TEMPLATES_NUMBER != 0 && $rs->fetch(0) >= MAX_TEMPLATES_NUMBER)
            {
                debug_write_log(DEBUG_NOTICE, 'Maximum amount of templates is already reached.');
                return TRUE;
            }

            $template['name']         = ustrcut($template['name'],         MAX_TEMPLATE_NAME);
            $template['prefix']       = ustrcut($template['prefix'],       MAX_TEMPLATE_PREFIX);
            $template['critical_age'] = ustrcut($template['critical_age'], ustrlen(MAX_TEMPLATE_DAYS_COUNT));
            $template['frozen_time']  = ustrcut($template['frozen_time'],  ustrlen(MAX_TEMPLATE_DAYS_COUNT));
            $template['description']  = ustrcut($template['description'],  MAX_TEMPLATE_DESCRIPTION);

            $guest_access = ($template['guest_access'] == 'yes');

            // Validate template.
            switch (template_validate($template['name'], $template['prefix'], $template['critical_age'], $template['frozen_time']))
            {
                case NO_ERROR:
                    break;  // nop
                case ERROR_INCOMPLETE_FORM:
                    $error = get_html_resource(RES_ALERT_REQUIRED_ARE_EMPTY_ID);
                    return FALSE;
                case ERROR_INVALID_INTEGER_VALUE:
                    $error = get_html_resource(RES_ALERT_INVALID_INTEGER_VALUE_ID);
                    return FALSE;
                case ERROR_INTEGER_VALUE_OUT_OF_RANGE:
                    $error = ustrprocess(get_html_resource(RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID), MIN_TEMPLATE_DAYS_COUNT, MAX_TEMPLATE_DAYS_COUNT);
                    return FALSE;
                default:
                    debug_write_log(DEBUG_WARNING, '[templates_import] Template validation failure.');
                    $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                    return FALSE;
            }

            // Create template.
            switch (template_create($project_id,
                                    $template['name'],
                                    $template['prefix'],
                                    $template['critical_age'],
                                    $template['frozen_time'],
                                    $template['description'],
                                    $guest_access))
            {
                case NO_ERROR:
                    break;
                case ERROR_ALREADY_EXISTS:
                    $error = get_html_resource(RES_ALERT_TEMPLATE_ALREADY_EXISTS_ID);
                    return FALSE;
                default:
                    debug_write_log(DEBUG_WARNING, '[templates_import] Template creation failure.');
                    $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                    return FALSE;
            }

            $rs = dal_query('templates/fndk2.sql', $project_id, ustrtolower($template['name']));

            if ($rs->rows == 0)
            {
                debug_write_log(DEBUG_WARNING, '[templates_import] Created template not found.');
                $error = get_html_resource(RES_ALERT_UNKNOWN_ERROR_ID);
                return FALSE;
            }

            $template_id = $rs->fetch('template_id');

            // Set author permissions.
            $permits = $template->xpath('./permissions/author/permit');

            if ($permits !== FALSE)
            {
                $perms = 0;

                foreach ($permits as $permit)
                {
                    $permit = strval($permit);

                    if (array_key_exists($permit, $permissions))
                    {
                        $perms |= $permissions[$permit];
                    }
                }

                template_author_perm_set($template_id, $perms);
            }

            // Set responsible permissions.
            $permits = $template->xpath('./permissions/responsible/permit');

            if ($permits !== FALSE)
            {
                $perms = 0;

                foreach ($permits as $permit)
                {
                    $permit = strval($permit);

                    if (array_key_exists($permit, $permissions))
                    {
                        $perms |= $permissions[$permit];
                    }
                }

                template_responsible_perm_set($template_id, $perms);
            }

            // Set registered permissions.
            $permits = $template->xpath('./permissions/registered/permit');

            if ($permits !== FALSE)
            {
                $perms = 0;

                foreach ($permits as $permit)
                {
                    $permit = strval($permit);

                    if (array_key_exists($permit, $permissions))
                    {
                        $perms |= $permissions[$permit];
                    }
                }

                template_registered_perm_set($template_id, $perms);
            }

            // Enumerate groups permissions.
            $groups = $template->xpath('./permissions/group');

            if ($groups !== FALSE)
            {
                foreach ($groups as $group)
                {
                    if (isset($group->permit))
                    {
                        // Find the group.
                        $rs = dal_query('groups/fndk.sql',
                                        $group['type'] == 'global' ? 'is null' : '=' . $project_id,
                                        ustrtolower(ustrcut($group['name'], MAX_GROUP_NAME)));

                        if ($rs->rows != 0)
                        {
                            $group_id = $rs->fetch('group_id');

                            // Set group permissions.
                            $perms = 0;

                            foreach ($group->permit as $permit)
                            {
                                $permit = strval($permit);

                                if (array_key_exists($permit, $permissions))
                                {
                                    $perms |= $permissions[$permit];
                                }
                            }

                            group_set_permissions($group_id, $template_id, $perms);
                        }
                    }
                }
            }

            // Import states.
            if (!states_import($template_id, $template, $error))
            {
                return FALSE;
            }
        }
    }

    return TRUE;
}

?>
