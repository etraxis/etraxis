<?php

/**
 * Projects
 *
 * This module provides API to work with eTraxis projects.
 * See also {@link http://www.etraxis.org/docs-schema.php#tbl_projects tbl_projects} database table.
 *
 * @package DBO
 * @subpackage Projects
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
//  Artem Rodygin           2005-02-18      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-24      new-009: Records filter.
//  Artem Rodygin           2005-08-11      new-008: Predefined metrics.
//  Artem Rodygin           2005-08-19      bug-039: PHP Warning: file_get_contents: failed to open stream: No such file or directory
//  Artem Rodygin           2005-08-23      bug-049: Removable project will not be removed in some cases.
//  Artem Rodygin           2005-08-23      new-053: All the calls of DAL API functions should be moved to DBO API.
//  Artem Rodygin           2005-08-24      bug-054: User can gain access to restricted projects.
//  Artem Rodygin           2005-08-27      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-09-01      bug-079: String database columns are not enough to store UTF-8 values.
//  Artem Rodygin           2005-09-27      new-141: Source code review.
//  Artem Rodygin           2005-10-22      bug-166: Some filters & subscriptions should be removed when a project, template, or state has been deleted.
//  Artem Rodygin           2005-10-22      bug-163: Some filters are malfunctional.
//  Artem Rodygin           2006-03-26      bug-226: PHP Warning: odbc_exec(): SQL error: Ambiguous column name 'description'.
//  Artem Rodygin           2006-04-21      bug-240: Unexpected message "Project with entered name already exists".
//  Artem Rodygin           2006-09-26      new-318: Group permissions should be template-wide.
//  Artem Rodygin           2007-01-05      new-491: [SF1647212] Group-wide transition permission.
//  Artem Rodygin           2007-11-27      new-633: The 'dbx' extension should not be used.
//  Artem Rodygin           2008-07-31      bug-735: PHP Warning: odbc_exec(): SQL error: DELETE statement conflicted with COLUMN REFERENCE constraint 'fk_group_perms_template_id'.
//  Artem Rodygin           2008-11-10      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-06-12      new-824: PHP 4 is discontinued.
//  Artem Rodygin           2009-06-17      bug-825: Database gets empty strings instead of NULL values.
//  Artem Rodygin           2009-09-09      new-826: Native unicode support for Microsoft SQL Server.
//--------------------------------------------------------------------------------------------------

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
/**#@-*/

//--------------------------------------------------------------------------------------------------
//  Definitions.
//--------------------------------------------------------------------------------------------------

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

//--------------------------------------------------------------------------------------------------
//  Functions.
//--------------------------------------------------------------------------------------------------

/**
 * Finds in database and returns the information about specified project.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_id Project ID}.
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
function project_list (&$sort, &$page)
{
    debug_write_log(DEBUG_TRACE, '[project_list]');

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
 * @param string $project_name {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_name Project name}.
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
 * @param string $project_name {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_name Project name}.
 * @param string $description Optional {@link http://www.etraxis.org/docs-schema.php#tbl_projects_description description}.
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - account is successfully created</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - project with specified {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_name project name} already exists</li>
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
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_id ID} of project to be modified.
 * @param string $project_name New {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_name project name}.
 * @param string $description New {@link http://www.etraxis.org/docs-schema.php#tbl_projects_description description}.
 * @param bool $is_suspended Whether the project should be suspended (see '{@link http://www.etraxis.org/docs-schema.php#tbl_projects_is_suspended is_suspended}' DBO field).
 * @return int Error code:
 * <ul>
 * <li>{@link NO_ERROR} - project is successfully modified</li>
 * <li>{@link ERROR_ALREADY_EXISTS} - another project with specified {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_name project name} already exists</li>
 * </ul>
 */
function project_modify ($id, $project_name, $description, $is_suspended)
{
    debug_write_log(DEBUG_TRACE, '[project_modify]');
    debug_write_log(DEBUG_DUMP,  '[project_modify] $id           = ' . $id);
    debug_write_log(DEBUG_DUMP,  '[project_modify] $project_name = ' . $project_name);
    debug_write_log(DEBUG_DUMP,  '[project_modify] $description  = ' . $description);
    debug_write_log(DEBUG_DUMP,  '[project_modify] $is_suspended = ' . $is_suspended);

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
              ustrlen($description) == 0 ? NULL : $description,
              bool2sql($is_suspended));

    return NO_ERROR;
}

/**
 * Checks whether project can be deleted.
 *
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_id ID} of project to be deleted.
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
 * @param int $id {@link http://www.etraxis.org/docs-schema.php#tbl_projects_project_id ID} of project to be deleted.
 * @return int Always {@link NO_ERROR}.
 */
function project_delete ($id)
{
    debug_write_log(DEBUG_TRACE, '[project_delete]');
    debug_write_log(DEBUG_DUMP,  '[project_delete] $id = ' . $id);

    dal_query('subscribes/sdelallp.sql', $id);

    dal_query('filters/fadelallp.sql', $id);
    dal_query('filters/fdelallp.sql',  $id);

    dal_query('projects/lvdelall.sql', $id);
    dal_query('projects/fpdelall.sql', $id);
    dal_query('projects/fdelall.sql',  $id);
    dal_query('projects/gpdelall.sql', $id);
    dal_query('projects/gtdelall.sql', $id);
    dal_query('projects/rtdelall.sql', $id);
    dal_query('projects/sdelall.sql',  $id);
    dal_query('projects/tdelall.sql',  $id);
    dal_query('projects/msdelall.sql', $id);
    dal_query('projects/gdelall.sql',  $id);
    dal_query('projects/delete.sql',   $id);

    return NO_ERROR;
}

?>
