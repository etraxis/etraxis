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
 * Projects
 *
 * This module provides API to work with eTraxis projects.
 * See also {@link http://code.google.com/p/etraxis/wiki/DatabaseSchema#tbl_projects tbl_projects} database table.
 *
 * @package DBO
 * @subpackage Projects
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/templates.php');
require_once('../dbo/accounts.php');
require_once('../dbo/groups.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Definitions.
//------------------------------------------------------------------------------

/**#@+
 * Data restriction.
 */
define('MAX_PROJECT_NAME',        25);
define('MAX_PROJECT_DESCRIPTION', 100);
/**#@-*/

/**#@+
 * Metrics type.
 */
define('METRICS_OPENED_RECORDS',      0);
define('METRICS_CREATION_VS_CLOSURE', 1);
/**#@-*/

//------------------------------------------------------------------------------
//  Functions.
//------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified project.
 *
 * @param int $id Project ID.
 * @return array Array with data if project is found in database, FALSE otherwise.
 */
function project_find ($id)
{
    debug_write_log(DEBUG_TRACE, '[project_find]');
    debug_write_log(DEBUG_DUMP,  '[project_find] $id = ' . $id);

    if (get_user_level() == USER_LEVEL_ADMIN)
    {
        $rs = dal_query('projects/fndid.sql', $id);
    }
    else
    {
        $rs = dal_query('projects/fndid2.sql', $_SESSION[VAR_USERID], $id);
    }

    return ($rs->rows == 0 ? FALSE : $rs->fetch());
}

/**
 * Returns {@link CRecordset DAL recordset} which contains all existing projects and sorted in
 * accordance with current sort mode.
 *
 * @param int &$sort Sort mode (used as output only). The function retrieves current sort mode from
 * client cookie ({@link COOKIE_PROJECTS_SORT}) and updates it, if it's out of valid range.
 * @param int &$page Number of current page tab (used as output only). The function retrieves current
 * page from client cookie ({@link COOKIE_PROJECTS_PAGE}) and updates it, if it's out of valid range.
 * @return CRecordset Recordset with list of projects.
 */
function projects_list (&$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[projects_list]');

    $sort_modes = array
    (
        1 => 'project_name asc',
        2 => 'start_time asc, project_name asc',
        3 => 'description asc, project_name asc',
        4 => 'project_name desc',
        5 => 'start_time desc, project_name desc',
        6 => 'description desc, project_name desc',
    );

    $sort = try_request('sort', try_cookie(COOKIE_PROJECTS_SORT));
    $sort = ustr2int($sort, 1, count($sort_modes));

    $page = try_request('page', try_cookie(COOKIE_PROJECTS_PAGE));
    $page = ustr2int($page, 1, MAXINT);

    save_cookie(COOKIE_PROJECTS_SORT, $sort);
    save_cookie(COOKIE_PROJECTS_PAGE, $page);

    if (get_user_level() == USER_LEVEL_ADMIN)
    {
        $rs = dal_query('projects/list.sql', $sort_modes[$sort]);
    }
    else
    {
        $rs = dal_query('projects/list2.sql', $_SESSION[VAR_USERID], $sort_modes[$sort]);
    }

    return $rs;
}

/**
 * Validates project information before creation or modification.
 *
 * @param string $project_name Project name.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - data are valid</li>
 * <li>{@link ERROR_INCOMPLETE_FORM} - at least one of required field is empty</li>
 * </ul>
 */
function project_validate ($project_name)
{
    debug_write_log(DEBUG_TRACE, '[project_validate]');
    debug_write_log(DEBUG_DUMP,  '[project_validate] $project_name = ' . $project_name);

    if (ustrlen($project_name) == 0)
    {
        debug_write_log(DEBUG_NOTICE, '[project_validate] At least one required field is empty.');
        return ERROR_INCOMPLETE_FORM;
    }

    return NO_ERROR;
}

/**
 * Creates new project.
 *
 * @param string $project_name Project name.
 * @param string $description Optional description.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - account is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - project with specified project name already exists</li>
 * </ul>
 */
function project_create ($project_name, $description)
{
    debug_write_log(DEBUG_TRACE, '[project_create]');
    debug_write_log(DEBUG_DUMP,  '[project_create] $project_name = ' . $project_name);
    debug_write_log(DEBUG_DUMP,  '[project_create] $description  = ' . $description);

    // Check that there is no project with the same project name.
    $rs = dal_query('projects/fndk.sql', ustrtolower($project_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[project_create] Project already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Create an project.
    dal_query('projects/create.sql',
              $project_name,
              time(),
              ustrlen($description) == 0 ? NULL : $description);

    return NO_ERROR;
}

/**
 * Modifies specified project.
 *
 * @param int $id ID of project to be modified.
 * @param string $project_name New project name.
 * @param string $description New description.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - project is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - another project with specified project name already exists</li>
 * </ul>
 */
function project_modify ($id, $project_name, $description)
{
    debug_write_log(DEBUG_TRACE, '[project_modify]');
    debug_write_log(DEBUG_DUMP,  '[project_modify] $id           = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[project_modify] $project_name = ' . $project_name);
    debug_write_log(DEBUG_DUMP,  '[project_modify] $description  = ' . $description);

    // Check that there is no project with the same project name, besides this one.
    $rs = dal_query('projects/fndku.sql', $id, ustrtolower($project_name));

    if ($rs->rows != 0)
    {
        debug_write_log(DEBUG_NOTICE, '[project_modify] Project already exists.');
        return ERROR_ALREADY_EXISTS;
    }

    // Modify the project.
    dal_query('projects/modify.sql',
              $id,
              $project_name,
              ustrlen($description) == 0 ? NULL : $description);

    return NO_ERROR;
}

/**
 * Checks whether project can be deleted.
 *
 * @param int $id ID of project to be deleted.
 * @return bool TRUE if project can be deleted, FALSE otherwise.
 */
function is_project_removable ($id)
{
    debug_write_log(DEBUG_TRACE, '[is_project_removable]');
    debug_write_log(DEBUG_DUMP,  '[is_project_removable] $id = ' . $id);

    $rs = dal_query('projects/rfndc.sql', $id);

    return ($rs->fetch(0) == 0);
}

/**
 * Deletes specified project.
 *
 * @param int $id ID of project to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function project_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[project_delete]');
    debug_write_log(DEBUG_DUMP,  '[project_delete] $id = ' . $id);

    dal_query('subscriptions/sdelallp.sql', $id);

    dal_query('filters/fshdelallg.sql', $id);
    dal_query('filters/fshdelallp.sql', $id);
    dal_query('filters/fa2delallp.sql', $id);
    dal_query('filters/fadelallp.sql',  $id);
    dal_query('filters/fsdelalls.sql',  $id);
    dal_query('filters/fsdelallp.sql',  $id);
    dal_query('filters/ftdelalls.sql',  $id);
    dal_query('filters/ftdelallp.sql',  $id);
    dal_query('filters/ffdelallf.sql',  $id);
    dal_query('filters/ffdelallp.sql',  $id);
    dal_query('filters/vdelallp.sql',   $id);
    dal_query('filters/fdelallp.sql',   $id);

    dal_query('projects/lvdelall.sql',  $id);
    dal_query('projects/fpdelall.sql',  $id);
    dal_query('projects/fdelall.sql',   $id);
    dal_query('projects/gpdelall.sql',  $id);
    dal_query('projects/gtdelall.sql',  $id);
    dal_query('projects/rtdelall.sql',  $id);
    dal_query('projects/rdelalls.sql',  $id);
    dal_query('projects/rdelallg.sql',  $id);
    dal_query('projects/sadelalls.sql', $id);
    dal_query('projects/sadelallg.sql', $id);
    dal_query('projects/sdelall.sql',   $id);
    dal_query('projects/tdelall.sql',   $id);
    dal_query('projects/msdelall.sql',  $id);
    dal_query('projects/gdelall.sql',   $id);
    dal_query('projects/delete.sql',    $id);

    return NO_ERROR;
}

/**
 * Exports specified project to XML code (see also {@link template_import}).
 *
 * @param int $id Project ID of project to be exported.
 * @return string Generated XML code for specified project.
 */
function project_export ($id)
{
    debug_write_log(DEBUG_TRACE, '[project_export]');
    debug_write_log(DEBUG_DUMP,  '[project_export] $id = ' . $id);

    // Find the project.
    $project = project_find($id);

    if (!$project)
    {
        return NULL;
    }

    // Generate XML code for groups.
    $sort = $page = NULL;
    $rs_g = groups_list($id, $sort, $page);
    $groups = array();

    while (($group = $rs_g->fetch()))
    {
        array_push($groups, $group['group_id']);
    }

    $xml_a = accounts_export($groups);
    $xml_g = groups_export($groups);

    // Generate XML code for templates.
    $rs_t = templates_list($id, $sort, $page);
    $xml_t = NULL;

    while (($template = $rs_t->fetch()))
    {
        $xml_t .= template_export($template["template_id"], true);
    }

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

    // Merge accounts XML code, groups XML code, and template XML code.
    $xml .= sprintf("<project name=\"%s\" description=\"%s\">\n{$xml_a}{$xml_g}{$xml_t}</project>\n",
                    ustr2html($project['project_name']),
                    ustr2html($project['description']));

    return $xml;
}

/**
 * Generates XML code for context menu on project's pages for specified project.
 *
 * @param string $template_url URL for using in template links.
 * @param string $state_url URL for using in state links.
 * @param string $field_url URL for using in field links.
 * @param int $project_id ID of project which context menu should be generated.
 * @param int $template_id ID of template to be expanded (NULL to keep all collapsed).
 * @param int $state_id ID of state to be expanded (NULL to keep all collapsed).
 * @return string Generated XML code.
 */
function gen_context_menu ($template_url, $state_url, $field_url, $project_id, $template_id = NULL, $state_id = NULL)
{
    debug_write_log(DEBUG_TRACE, '[gen_context_menu]');
    debug_write_log(DEBUG_DUMP,  '[gen_context_menu] $template_url = ' . $template_url);
    debug_write_log(DEBUG_DUMP,  '[gen_context_menu] $state_url    = ' . $state_url);
    debug_write_log(DEBUG_DUMP,  '[gen_context_menu] $field_url    = ' . $field_url);
    debug_write_log(DEBUG_DUMP,  '[gen_context_menu] $project_id   = ' . $project_id);
    debug_write_log(DEBUG_DUMP,  '[gen_context_menu] $template_id  = ' . $template_id);
    debug_write_log(DEBUG_DUMP,  '[gen_context_menu] $state_id     = ' . $state_id);

    $xml = NULL;

    if (get_user_level() == USER_LEVEL_ADMIN)
    {
        $templates = dal_query('templates/list.sql', $project_id, 'template_name asc');

        if ($templates->rows != 0)
        {
            $xml = '<contextmenu>';

            while (($template = $templates->fetch()))
            {
                $xml .= ($template['template_id'] == $template_id
                            ? '<submenu url="' . $template_url . $template['template_id'] . '" text="' . ustr2html($template['template_name']) . '" expanded="true">'
                            : '<submenu url="' . $template_url . $template['template_id'] . '" text="' . ustr2html($template['template_name']) . '">');

                $states = dal_query('states/list.sql', $template['template_id'], 'state_name asc');

                if ($states->rows == 0)
                {
                    $xml .= '<menuitem>'
                          . get_html_resource(RES_NONE_ID)
                          . '</menuitem>';
                }
                else
                {
                    while (($state = $states->fetch()))
                    {
                        $xml .= ($state['state_id'] == $state_id
                                    ? '<submenu url="' . $state_url . $state['state_id'] . '" text="' . ustr2html($state['state_name']) . '" expanded="true">'
                                    : '<submenu url="' . $state_url . $state['state_id'] . '" text="' . ustr2html($state['state_name']) . '">');

                        $fields = dal_query('fields/list.sql', $state['state_id'], 'field_order asc');

                        if ($fields->rows == 0)
                        {
                            $xml .= '<menuitem>'
                                  . get_html_resource(RES_NONE_ID)
                                  . '</menuitem>';
                        }
                        else
                        {
                            while (($field = $fields->fetch()))
                            {
                                $xml .= '<menuitem url="' . $field_url . $field['field_id'] . '">'
                                      . ustr2html($field['field_name'])
                                      . '</menuitem>';
                            }
                        }

                        $xml .= '</submenu>';
                        }
                    }

                $xml .= '</submenu>';
            }

            $xml .= '</contextmenu>';
        }
    }

    return $xml;
}

?>
