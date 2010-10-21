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
require_once('../dbo/importer.php');
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
 * Exports specified template to XML code (see also {@link template_import}).
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
        PERMIT_CHANGE_STATE          => 'state',
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
    $xml_t = sprintf("  <template name=\"%s\" prefix=\"%s\" description=\"%s\" critical_age=\"%s\" frozen_time=\"%s\">\n",
                     ustr2html($template['template_name']),
                     ustr2html($template['template_prefix']),
                     ustr2html($template['description']),
                     $template['critical_age'],
                     $template['frozen_time']);

    $xml_t .= "    <permissions>\n";

    // Add XML code for template "author" permissions.
    if ($template['author_perm'] != 0)
    {
        $xml_t .= "      <author>\n";

        foreach ($permissions as $flag => $permit)
        {
            $xml_t .= (($template['author_perm'] & $flag) == 0 ? NULL : "        <permit>{$permit}</permit>\n");
        }

        $xml_t .= "      </author>\n";
    }

    // Add XML code for template "responsible" permissions.
    if ($template['responsible_perm'] != 0)
    {
        $xml_t .= "      <responsible>\n";

        foreach ($permissions as $flag => $permit)
        {
            $xml_t .= (($template['responsible_perm'] & $flag) == 0 ? NULL : "        <permit>{$permit}</permit>\n");
        }

        $xml_t .= "      </responsible>\n";
    }

    // Add XML code for template "registered" permissions.
    if ($template['registered_perm'] != 0)
    {
        $xml_t .= "      <registered>\n";

        foreach ($permissions as $flag => $permit)
        {
            $xml_t .= (($template['registered_perm'] & $flag) == 0 ? NULL : "        <permit>{$permit}</permit>\n");
        }

        $xml_t .= "      </registered>\n";
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
            $xml_t .= sprintf("      <group name=\"%s\" type=\"%s\">\n",
                              ustr2html($group['group_name']),
                              (is_null($group['project_id']) ? 'global' : 'local'));

            // Add XML code for permissions information.
            foreach ($permissions as $flag => $permit)
            {
                $xml_t .= (($group['perms'] & $flag) == 0 ? NULL : "        <permit>{$permit}</permit>\n");
            }

            $xml_t .= "      </group>\n";
        }
    }

    $xml_t .= "    </permissions>\n";

    // Export all existing states of the template.
    $xml_t .= state_export($id, $groups);
    $xml_t .= "  </template>\n";

    if ($just_the_node)
    {
        return $xml_t;
    }

    $xml_a = $xml_g = NULL;

    $xml_a = accounts_export($groups);
    $xml_g = groups_export($groups);

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

    // Merge accounts XML code, groups XML code, and template XML code.
    $xml .= sprintf("<project name=\"%s\" description=\"%s\">\n{$xml_a}{$xml_g}{$xml_t}</project>\n",
                    ustr2html($template['project_name']),
                    ustr2html($template['p_description']));

    return $xml;
}

/**
 * Imports template specified as XML code (see also {@link template_export}).
 *
 * @param string $xmlfile File with XML code uploaded as described {@link http://www.php.net/features.file-upload here}.
 * @param int &$id ID of newly imported template (used as output only).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - template is successfully imported</li>
 * <li>{@link ERROR_UPLOAD_INI_SIZE} - the uploaded file exceeds the upload_max_filesize directive in 'php.ini'</li>
 * <li>{@link ERROR_UPLOAD_FORM_SIZE} - the uploaded file exceeds the {@link ATTACHMENTS_MAXSIZE} constant in 'engine/config.php'</li>
 * <li>{@link ERROR_UPLOAD_PARTIAL} - the uploaded file was only partially uploaded</li>
 * <li>{@link ERROR_UPLOAD_NO_FILE} - no file was uploaded</li>
 * <li>{@link ERROR_UPLOAD_NO_TMP_DIR} - missing a temporary folder</li>
 * <li>{@link ERROR_UPLOAD_CANT_WRITE} - failed to write file to disk</li>
 * <li>{@link ERROR_UPLOAD_EXTENSION} - file upload stopped by extension</li>
 * <li>{@link ERROR_XML_PARSER} - syntax error in XML code</li>
 * <li>{@link ERROR_NOT_FOUND} - failure on attempt to create new project, group, template, state, or field</li>
 * <li>any error which could be raised by {@link account_validate}</li>
 * <li>any error which could be raised by {@link group_validate}</li>
 * <li>any error which could be raised by {@link project_validate}</li>
 * <li>any error which could be raised by {@link template_validate}</li>
 * <li>any error which could be raised by {@link state_validate}</li>
 * <li>any error which could be raised by {@link field_validate}</li>
 * <li>any error which could be raised by {@link field_validate_number}</li>
 * <li>any error which could be raised by {@link field_validate_string}</li>
 * <li>any error which could be raised by {@link field_validate_multilined}</li>
 * <li>any error which could be raised by {@link field_validate_date}</li>
 * <li>any error which could be raised by {@link field_validate_duration}</li>
 * <li>{@link ERROR_UNKNOWN} - unknown error</li>
 * </ul>
 */
function template_import ($xmlfile, &$id)
{
    debug_write_log(DEBUG_TRACE, '[template_import]');
    debug_write_log(DEBUG_DUMP,  '[template_import] $xmlfile["name"]     = ' . $xmlfile['name']);
    debug_write_log(DEBUG_DUMP,  '[template_import] $xmlfile["type"]     = ' . $xmlfile['type']);
    debug_write_log(DEBUG_DUMP,  '[template_import] $xmlfile["size"]     = ' . $xmlfile['size']);
    debug_write_log(DEBUG_DUMP,  '[template_import] $xmlfile["tmp_name"] = ' . $xmlfile['tmp_name']);
    debug_write_log(DEBUG_DUMP,  '[template_import] $xmlfile["error"]    = ' . $xmlfile['error']);

    // Check for possible upload errors, provided by PHP.
    switch ($xmlfile['error'])
    {
        case UPLOAD_ERR_OK:
            break;  // nop
        case UPLOAD_ERR_INI_SIZE:
            return ERROR_UPLOAD_INI_SIZE;
        case UPLOAD_ERR_FORM_SIZE:
            return ERROR_UPLOAD_FORM_SIZE;
        case UPLOAD_ERR_PARTIAL:
            return ERROR_UPLOAD_PARTIAL;
        case UPLOAD_ERR_NO_FILE:
            return ERROR_UPLOAD_NO_FILE;
        case UPLOAD_ERR_NO_TMP_DIR:
            return ERROR_UPLOAD_NO_TMP_DIR;
        case UPLOAD_ERR_CANT_WRITE:
            return ERROR_UPLOAD_CANT_WRITE;
        case UPLOAD_ERR_EXTENSION:
            return ERROR_UPLOAD_EXTENSION;
        default:
            return ERROR_UNKNOWN;
    }

    // Check for file size.
    if ($xmlfile['size'] > ATTACHMENTS_MAXSIZE * 1024)
    {
        debug_write_log(DEBUG_WARNING, '[template_import] File is too large.');
        return ERROR_UPLOAD_FORM_SIZE;
    }

    // Check whether the file was uploaded via HTTP POST (security issue).
    if (!is_uploaded_file($xmlfile['tmp_name']))
    {
        debug_write_log(DEBUG_WARNING, '[template_import] Function "is_uploaded_file" warns that file named by "' . $xmlfile['tmp_name'] . '" was not uploaded via HTTP POST.');
        return NO_ERROR;
    }

    // Read and parse XML code from uploaded file.
    $data = file_get_contents($xmlfile['tmp_name']);

    if (!$data)
    {
        debug_write_log(DEBUG_ERROR, '[template_import] File cannot be read.');
        return ERROR_UNKNOWN;
    }

    $importer = new CImporter();
    $importer->import($data);
    $id = $importer->template_id;

    debug_write_log(DEBUG_NOTICE, '[template_import] $importer->error = ' . $importer->error);

    return $importer->error;
}

?>
