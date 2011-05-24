<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2008-2011  Artem Rodygin
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
 * @package DBO
 * @ignore
 */

/**#@+
 * Dependency.
 */
require_once('../engine/engine.php');
require_once('../dbo/accounts.php');
require_once('../dbo/groups.php');
require_once('../dbo/projects.php');
require_once('../dbo/templates.php');
require_once('../dbo/states.php');
require_once('../dbo/fields.php');
require_once('../dbo/events.php');
/**#@-*/

//------------------------------------------------------------------------------
//  Importer.
//------------------------------------------------------------------------------

/**
 * For internal use only.
 *
 * @package DBO
 * @ignore
 */
class CImporter
{
    private $parser;
    public  $error;
    private $level;
    private $lasttag;

    private $group_id;
    private $project_id;
    public  $template_id;
    private $state_id;
    private $field_id;
    private $params;
    private $next_states;
    private $transitions;

    function CImporter ()
    {
        debug_write_log(DEBUG_TRACE, '[CImporter::CImporter]');

        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'start_element_handler', 'end_element_handler');
        xml_set_character_data_handler($this->parser, 'cdata_handler');
    }

    function import ($data)
    {
        debug_write_log(DEBUG_TRACE, '[CImporter::import]');

        $this->error   = NO_ERROR;
        $this->level   = 0;
        $this->lasttag = array('0:ROOT');

        $this->group_id    = NULL;
        $this->project_id  = NULL;
        $this->template_id = NULL;
        $this->state_id    = NULL;
        $this->field_id    = NULL;

        $this->params      = array();
        $this->next_states = array();
        $this->transitions = array();

        if (!xml_parse($this->parser, $data, TRUE))
        {
            $this->error = ERROR_XML_PARSER;

            debug_write_log(DEBUG_WARNING, sprintf("XML error: %s at line %d",
                                                   xml_error_string(xml_get_error_code($this->parser)),
                                                   xml_get_current_line_number($this->parser)));
        }
    }

    function start_element_handler ($parser, $name, $attrs)
    {
        debug_write_log(DEBUG_TRACE, '[CImporter::start_element_handler] ' . $name);

        if (is_resource($parser) && $this->error != NO_ERROR)
        {
            return;
        }

        $tagsmap = array
        (
            '0:ROOT'        => array('PROJECT'),
            '1:PROJECT'     => array('ACCOUNTS','GROUPS','TEMPLATE'),
            '2:ACCOUNTS'    => array('ACCOUNT'),
            '2:GROUPS'      => array('GROUP'),
            '3:GROUP'       => array('ACCOUNT'),
            '2:TEMPLATE'    => array('PERMISSIONS','STATES'),
            '3:PERMISSIONS' => array('AUTHOR','RESPONSIBLE','REGISTERED','GROUP'),
            '4:AUTHOR'      => array('PERMIT'),
            '4:RESPONSIBLE' => array('PERMIT'),
            '4:REGISTERED'  => array('PERMIT'),
            '4:GROUP'       => array('PERMIT'),
            '3:STATES'      => array('STATE'),
            '4:STATE'       => array('TRANSITIONS','FIELDS'),
            '5:TRANSITIONS' => array('AUTHOR','RESPONSIBLE','REGISTERED','GROUP'),
            '6:AUTHOR'      => array('STATE'),
            '6:RESPONSIBLE' => array('STATE'),
            '6:REGISTERED'  => array('STATE'),
            '6:GROUP'       => array('STATE'),
            '5:FIELDS'      => array('FIELD'),
            '6:FIELD'       => array('DEFAULT','LIST','PERMISSIONS','DESCRIPTION'),
            '7:LIST'        => array('ITEM'),
            '7:PERMISSIONS' => array('AUTHOR','RESPONSIBLE','REGISTERED','GROUP'),
        );

        $lasttag = end($this->lasttag);

        if (!array_key_exists($lasttag, $tagsmap) || !in_array($name, $tagsmap[$lasttag]))
        {
            $this->error = ERROR_XML_PARSER;
            return;
        }

        $this->level += 1;

        if ($this->level == 1)
        {
            // create a project
            if ($name == 'PROJECT')
            {
                $project_name = (array_key_exists('NAME',        $attrs) ? $attrs['NAME']        : NULL);
                $description  = (array_key_exists('DESCRIPTION', $attrs) ? $attrs['DESCRIPTION'] : NULL);

                $this->error = project_validate($project_name);

                if ($this->error == NO_ERROR)
                {
                    project_create($project_name, $description);

                    $rs = dal_query('projects/fndk.sql', ustrtolower($project_name));

                    if ($rs->rows == 0)
                    {
                        debug_write_log(DEBUG_NOTICE, '[CImporter::start_element_handler] Project cannot be found.');
                        $this->error = ERROR_NOT_FOUND;
                    }
                    else
                    {
                        $this->project_id = $rs->fetch('project_id');
                    }
                }
            }
        }
        elseif ($this->level == 2)
        {
            // create a template
            if ($name == 'TEMPLATE')
            {
                $template_name   = (array_key_exists('NAME',         $attrs) ? $attrs['NAME']         : NULL);
                $template_prefix = (array_key_exists('PREFIX',       $attrs) ? $attrs['PREFIX']       : NULL);
                $critical_age    = (array_key_exists('CRITICAL_AGE', $attrs) ? $attrs['CRITICAL_AGE'] : NULL);
                $frozen_time     = (array_key_exists('FROZEN_TIME',  $attrs) ? $attrs['FROZEN_TIME']  : NULL);
                $description     = (array_key_exists('DESCRIPTION',  $attrs) ? $attrs['DESCRIPTION']  : NULL);
                $guest_access    = (array_key_exists('GUEST_ACCESS', $attrs) ? ustrtolower($attrs['GUEST_ACCESS']) : 'no');

                $this->error = template_validate($template_name, $template_prefix, $critical_age, $frozen_time);

                if ($this->error == NO_ERROR)
                {
                    template_create($this->project_id,
                                    $template_name,
                                    $template_prefix,
                                    $critical_age,
                                    $frozen_time,
                                    $description,
                                    ($guest_access == 'yes'));

                    $rs = dal_query('templates/fndk.sql',
                                    $this->project_id,
                                    ustrtolower($template_name),
                                    ustrtolower($template_prefix));

                    if ($rs->rows == 0)
                    {
                        debug_write_log(DEBUG_NOTICE, '[CImporter::start_element_handler] Template cannot be found.');
                        $this->error = ERROR_NOT_FOUND;
                    }
                    else
                    {
                        $this->template_id = $rs->fetch('template_id');
                    }
                }
            }
        }
        elseif ($this->level == 3)
        {
            // create an account
            if ($name == 'ACCOUNT')
            {
                $username    = (array_key_exists('USERNAME',    $attrs) ? $attrs['USERNAME']              : NULL);
                $fullname    = (array_key_exists('FULLNAME',    $attrs) ? $attrs['FULLNAME']              : NULL);
                $email       = (array_key_exists('EMAIL',       $attrs) ? $attrs['EMAIL']                 : NULL);
                $description = (array_key_exists('DESCRIPTION', $attrs) ? $attrs['DESCRIPTION']           : NULL);
                $type        = (array_key_exists('TYPE',        $attrs) ? ustrtolower($attrs['TYPE'])     : 'local');
                $admin       = (array_key_exists('ADMIN',       $attrs) ? ustrtolower($attrs['ADMIN'])    : 'no');
                $disabled    = (array_key_exists('DISABLED',    $attrs) ? ustrtolower($attrs['DISABLED']) : 'no');
                $locale      = (array_key_exists('LOCALE',      $attrs) ? $attrs['LOCALE']                : NULL);
                $locale_id   = LANG_DEFAULT;

                $this->error = account_validate($username, $fullname, $email,
                                                str_pad(NULL, MIN_PASSWORD_LENGTH, '*'),
                                                str_pad(NULL, MIN_PASSWORD_LENGTH, '*'));

                global $locale_info;
                $supported_locales = array_keys($locale_info);

                foreach ($supported_locales as $lang)
                {
                    if (ustrtolower(get_html_resource(RES_LOCALE_ID, $lang)) == ustrtolower($locale))
                    {
                        $locale_id = $lang;
                    }
                }

                if ($this->error == NO_ERROR)
                {
                    account_create($username, $fullname, $email, NULL, $description,
                                   ($admin == 'yes'),
                                   ($disabled == 'yes'),
                                   $locale_id,
                                   ($type == 'ldap'));
                }
            }
            // create a group
            elseif ($name == 'GROUP')
            {
                $group_name  = (array_key_exists('NAME',        $attrs) ? $attrs['NAME'] : NULL);
                $group_type  = (array_key_exists('TYPE',        $attrs) ? ustrtolower($attrs['TYPE']) : 'local');
                $description = (array_key_exists('DESCRIPTION', $attrs) ? $attrs['DESCRIPTION'] : NULL);

                $this->error = group_validate($group_name);

                if ($this->error == NO_ERROR)
                {
                    group_create(($group_type == 'local' ? $this->project_id : 0), $group_name, $description);

                    $rs = dal_query('groups/fndk.sql',
                                    ($group_type == 'local' ? '=' . $this->project_id : 'is null'),
                                    ustrtolower($group_name));

                    if ($rs->rows == 0)
                    {
                        debug_write_log(DEBUG_NOTICE, '[CImporter::start_element_handler] Group cannot be found.');
                        $this->error = ERROR_NOT_FOUND;
                    }
                    else
                    {
                        $this->group_id = $rs->fetch('group_id');
                    }
                }
            }
        }
        elseif ($this->level == 4)
        {
            // add account to a group
            if ($name == 'ACCOUNT')
            {
                array_push($this->params, (array_key_exists('TYPE', $attrs) ? ustrtolower($attrs['TYPE']) : 'local'));
            }
            // template permissions for author
            elseif ($name == 'AUTHOR')
            {
                array_push($this->params, 0);
            }
            // template permissions for responsible
            elseif ($name == 'RESPONSIBLE')
            {
                array_push($this->params, 0);
            }
            // template permissions for registered
            elseif ($name == 'REGISTERED')
            {
                array_push($this->params, 0);
            }
            // template permissions for group
            elseif ($name == 'GROUP')
            {
                array_push($this->params, 0);

                $group_name = (array_key_exists('NAME', $attrs) ? $attrs['NAME'] : NULL);
                $group_type = (array_key_exists('TYPE', $attrs) ? ustrtolower($attrs['TYPE']) : 'local');

                $rs = dal_query('groups/fndk.sql',
                                ($group_type == 'local' ? '=' . $this->project_id : 'is null'),
                                ustrtolower($group_name));

                $this->group_id = ($rs->rows == 0 ? NULL : $rs->fetch('group_id'));
            }
            // create a state
            elseif ($name == 'STATE')
            {
                $state_name = (array_key_exists('NAME', $attrs) ? $attrs['NAME'] : NULL);
                $state_abbr = (array_key_exists('ABBR', $attrs) ? $attrs['ABBR'] : NULL);
                $state_type = (array_key_exists('TYPE', $attrs) ? ustrtolower($attrs['TYPE']) : NULL);
                $next_state = (array_key_exists('NEXT', $attrs) ? $attrs['NEXT'] : NULL);

                switch (array_key_exists('TYPE', $attrs) ? ustrtolower($attrs['TYPE']) : 'intermed')
                {
                    case 'initial':
                        $state_type = STATE_TYPE_INITIAL;
                        break;
                    case 'intermed':
                        $state_type = STATE_TYPE_INTERMEDIATE;
                        break;
                    case 'final':
                        $state_type = STATE_TYPE_FINAL;
                        break;
                    default:
                        $state_type = STATE_TYPE_INTERMEDIATE;
                }

                switch (array_key_exists('RESPONSIBLE', $attrs) ? ustrtolower($attrs['RESPONSIBLE']) : 'remove')
                {
                    case 'remain':
                        $responsible = STATE_RESPONSIBLE_REMAIN;
                        break;
                    case 'assign':
                        $responsible = STATE_RESPONSIBLE_ASSIGN;
                        break;
                    case 'remove':
                        $responsible = STATE_RESPONSIBLE_REMOVE;
                        break;
                    default:
                        $responsible = STATE_RESPONSIBLE_REMOVE;
                }

                $this->error = state_validate($state_name, $state_abbr);

                if ($this->error == NO_ERROR)
                {
                    state_create($this->template_id,
                                 $state_name,
                                 $state_abbr,
                                 ($state_type == STATE_TYPE_FINAL ? STATE_TYPE_FINAL : STATE_TYPE_INTERMEDIATE),
                                 NULL,
                                 $responsible);

                    $rs = dal_query('states/fndk.sql',
                                    $this->template_id,
                                    ustrtolower($state_name),
                                    ustrtolower($state_abbr));

                    if ($rs->rows == 0)
                    {
                        debug_write_log(DEBUG_NOTICE, '[CImporter::start_element_handler] State cannot be found.');
                        $this->error = ERROR_NOT_FOUND;
                    }
                    else
                    {
                        $this->state_id = $rs->fetch('state_id');

                        if ($state_type == STATE_TYPE_INITIAL)
                        {
                            state_set_initial($this->template_id, $this->state_id);
                        }

                        if (!is_null($next_state))
                        {
                            array_push($this->next_states, array($this->state_id, $next_state));
                        }
                    }
                }
            }
        }
        elseif ($this->level == 6)
        {
            // state transitions for author
            if ($name == 'AUTHOR')
            {
                array_push($this->params, STATE_ROLE_AUTHOR);
            }
            // state transitions for responsible
            elseif ($name == 'RESPONSIBLE')
            {
                array_push($this->params, STATE_ROLE_RESPONSIBLE);
            }
            // state transitions for registered
            elseif ($name == 'REGISTERED')
            {
                array_push($this->params, STATE_ROLE_REGISTERED);
            }
            // state transitions for group
            elseif ($name == 'GROUP')
            {
                $group_name = (array_key_exists('NAME', $attrs) ? $attrs['NAME'] : NULL);
                $group_type = (array_key_exists('TYPE', $attrs) ? ustrtolower($attrs['TYPE']) : 'local');

                $rs = dal_query('groups/fndk.sql',
                                ($group_type == 'local' ? '=' . $this->project_id : 'is null'),
                                ustrtolower($group_name));

                array_push($this->params, ($rs->rows == 0 ? NULL : $rs->fetch('group_id')));
            }
            // create a field
            elseif ($name == 'FIELD')
            {
                $field_name = (array_key_exists('NAME', $attrs) ? $attrs['NAME'] : NULL);

                switch (array_key_exists('TYPE', $attrs) ? ustrtolower($attrs['TYPE']) : 'string')
                {
                    case 'number':
                        $field_type = FIELD_TYPE_NUMBER;
                        break;
                    case 'float':
                        $field_type = FIELD_TYPE_FLOAT;
                        break;
                    case 'string':
                        $field_type = FIELD_TYPE_STRING;
                        break;
                    case 'multi':
                        $field_type = FIELD_TYPE_MULTILINED;
                        break;
                    case 'check':
                        $field_type = FIELD_TYPE_CHECKBOX;
                        break;
                    case 'list':
                        $field_type = FIELD_TYPE_LIST;
                        break;
                    case 'record':
                        $field_type = FIELD_TYPE_RECORD;
                        break;
                    case 'date':
                        $field_type = FIELD_TYPE_DATE;
                        break;
                    case 'duration':
                        $field_type = FIELD_TYPE_DURATION;
                        break;
                    default:
                        $field_type = FIELD_TYPE_STRING;
                }

                switch (array_key_exists('REQUIRED', $attrs) ? ustrtolower($attrs['REQUIRED']) : 'no')
                {
                    case 'yes':
                        $is_required = TRUE;
                        break;
                    case 'no':
                        $is_required = FALSE;
                        break;
                    default:
                        $is_required = FALSE;
                }

                switch (array_key_exists('SEPARATOR', $attrs) ? ustrtolower($attrs['SEPARATOR']) : 'none')
                {
                    case 'none':
                        $add_separator = FALSE;
                        break;
                    case 'add':
                        $add_separator = TRUE;
                        break;
                    default:
                        $add_separator = FALSE;
                }

                switch (array_key_exists('GUEST_ACCESS', $attrs) ? ustrtolower($attrs['GUEST_ACCESS']) : 'no')
                {
                    case 'yes':
                        $guest_access = TRUE;
                        break;
                    case 'no':
                        $guest_access = FALSE;
                        break;
                    default:
                        $guest_access = FALSE;
                }

                $regex_check   = NULL;
                $regex_search  = NULL;
                $regex_replace = NULL;
                $param1        = NULL;
                $param2        = NULL;
                $default       = NULL;

                switch ($field_type)
                {
                    case FIELD_TYPE_NUMBER:

                        $param1  = (array_key_exists('MINIMUM', $attrs) ? $attrs['MINIMUM'] : NULL);
                        $param2  = (array_key_exists('MAXIMUM', $attrs) ? $attrs['MAXIMUM'] : NULL);
                        $default = (array_key_exists('DEFAULT', $attrs) ? (ustrlen($attrs['DEFAULT']) == 0 ? NULL : $attrs['DEFAULT']) : NULL);

                        $this->error = field_validate_number($field_name, $param1, $param2, $default);

                        break;

                    case FIELD_TYPE_FLOAT:

                        $param1  = (array_key_exists('MINIMUM', $attrs) ? $attrs['MINIMUM'] : NULL);
                        $param2  = (array_key_exists('MAXIMUM', $attrs) ? $attrs['MAXIMUM'] : NULL);
                        $default = (array_key_exists('DEFAULT', $attrs) ? (ustrlen($attrs['DEFAULT']) == 0 ? NULL : $attrs['DEFAULT']) : NULL);

                        $this->error = field_validate_float($field_name, $param1, $param2, $default);

                        $param1  = value_find_float($param1);
                        $param2  = value_find_float($param2);
                        $default = (is_null($default) ? NULL : value_find_float($default));

                        break;

                    case FIELD_TYPE_STRING:

                        $regex_check   = (array_key_exists('REGEX_CHECK',   $attrs) ? $attrs['REGEX_CHECK']   : NULL);
                        $regex_search  = (array_key_exists('REGEX_SEARCH',  $attrs) ? $attrs['REGEX_SEARCH']  : NULL);
                        $regex_replace = (array_key_exists('REGEX_REPLACE', $attrs) ? $attrs['REGEX_REPLACE'] : NULL);

                        $param1  = (array_key_exists('LENGTH', $attrs) ? $attrs['LENGTH'] : NULL);
                        $default = value_find_string(array_key_exists('DEFAULT', $attrs) ? (ustrlen($attrs['DEFAULT']) == 0 ? NULL : $attrs['DEFAULT']) : NULL);

                        $this->error = field_validate_string($field_name, $param1);

                        break;

                    case FIELD_TYPE_MULTILINED:

                        $regex_check   = (array_key_exists('REGEX_CHECK',   $attrs) ? $attrs['REGEX_CHECK']   : NULL);
                        $regex_search  = (array_key_exists('REGEX_SEARCH',  $attrs) ? $attrs['REGEX_SEARCH']  : NULL);
                        $regex_replace = (array_key_exists('REGEX_REPLACE', $attrs) ? $attrs['REGEX_REPLACE'] : NULL);

                        $param1  = (array_key_exists('LENGTH', $attrs) ? $attrs['LENGTH'] : NULL);
                        $default = NULL;

                        $this->error = field_validate_multilined($field_name, $param1);

                        break;

                    case FIELD_TYPE_CHECKBOX:

                        $default = bool2sql((array_key_exists('DEFAULT', $attrs) ? ustrtolower($attrs['DEFAULT']) : 'off') != 'off');
                        $this->error = field_validate($field_name);

                        break;

                    case FIELD_TYPE_LIST:

                        $default = (array_key_exists('DEFAULT', $attrs) ? (is_intvalue($attrs['DEFAULT']) ? $attrs['DEFAULT'] : NULL) : NULL);
                        $this->error = field_validate($field_name);

                        break;

                    case FIELD_TYPE_RECORD:

                        $this->error = field_validate($field_name);

                        break;

                    case FIELD_TYPE_DATE:

                        $temp = $_SESSION[VAR_LOCALE];
                        $_SESSION[VAR_LOCALE] = LANG_ENGLISH_US;

                        $param1  = (array_key_exists('MINIMUM', $attrs) ? $attrs['MINIMUM'] : NULL);
                        $param2  = (array_key_exists('MAXIMUM', $attrs) ? $attrs['MAXIMUM'] : NULL);
                        $default = (array_key_exists('DEFAULT', $attrs) ? (ustrlen($attrs['DEFAULT']) == 0 ? NULL : $attrs['DEFAULT']) : NULL);

                        $this->error = field_validate_date($field_name, $param1, $param2, $default);

                        $default = (is_null($default) ? NULL : ustr2int($default, MIN_FIELD_DATE, MAX_FIELD_DATE));

                        $_SESSION[VAR_LOCALE] = $temp;

                        break;

                    case FIELD_TYPE_DURATION:

                        $param1  = (array_key_exists('MINIMUM', $attrs) ? $attrs['MINIMUM'] : NULL);
                        $param2  = (array_key_exists('MAXIMUM', $attrs) ? $attrs['MAXIMUM'] : NULL);
                        $default = (array_key_exists('DEFAULT', $attrs) ? (ustrlen($attrs['DEFAULT']) == 0 ? NULL : $attrs['DEFAULT']) : NULL);

                        $this->error = field_validate_duration($field_name, $param1, $param2, $default);

                        $param1  = ustr2time($param1);
                        $param2  = ustr2time($param2);
                        $default = (is_null($default) ? NULL : ustr2time($default));

                        break;

                    default: ;  // nop
                }

                if ($this->error == NO_ERROR)
                {
                    field_create($this->template_id,
                                 $this->state_id,
                                 $field_name,
                                 $field_type,
                                 $is_required,
                                 $add_separator,
                                 $guest_access,
                                 NULL,
                                 $regex_check,
                                 $regex_search,
                                 $regex_replace,
                                 $param1,
                                 $param2,
                                 $default);

                    $rs = dal_query('fields/fndk.sql',
                                    $this->state_id,
                                    ustrtolower($field_name));

                    if ($rs->rows == 0)
                    {
                        debug_write_log(DEBUG_NOTICE, '[CImporter::start_element_handler] Field cannot be found.');
                        $this->error = ERROR_NOT_FOUND;
                    }
                    else
                    {
                        $this->field_id = $rs->fetch('field_id');
                        array_push($this->params, $field_type);
                        dal_query('fields/fpdelall.sql', $this->field_id);
                    }
                }
            }
        }
        elseif ($this->level == 7)
        {
            if ($name == 'STATE')
            {
                array_push($this->params, '');
            }
        }
        elseif ($this->level == 8)
        {
            // add new item to the list
            if ($name == 'ITEM')
            {
                array_push($this->params, (array_key_exists('VALUE', $attrs) ? $attrs['VALUE'] : NULL));
                array_push($this->params, '');
            }
            // field permissions for group
            elseif ($name == 'GROUP')
            {
                $group_name = (array_key_exists('NAME', $attrs) ? $attrs['NAME'] : NULL);
                $group_type = (array_key_exists('TYPE', $attrs) ? ustrtolower($attrs['TYPE']) : 'local');

                $rs = dal_query('groups/fndk.sql',
                                ($group_type == 'local' ? '=' . $this->project_id : 'is null'),
                                ustrtolower($group_name));

                $this->group_id = ($rs->rows == 0 ? NULL : $rs->fetch('group_id'));
            }
        }

        array_push($this->lasttag, "{$this->level}:{$name}");
    }

    function end_element_handler ($parser, $name)
    {
        debug_write_log(DEBUG_TRACE, '[CImporter::end_element_handler]   ' . $name);

        if (is_resource($parser) && $this->error != NO_ERROR)
        {
            return;
        }

        array_pop($this->lasttag);

        if ($this->level == 1)
        {
            // creation of new project is completed
            if ($name == 'PROJECT')
            {
                $this->project_id = NULL;
            }
        }
        elseif ($this->level == 3)
        {
            // creation of new group is completed
            if ($name == 'GROUP')
            {
                $this->group_id = NULL;
            }
            // creation of all states is completed
            elseif ($name == 'STATES')
            {
                // update next states
                while (count($this->next_states) > 0)
                {
                    list($state_id, $next_state) = array_pop($this->next_states);

                    $rs = dal_query('states/fndk.sql',
                                    $this->template_id,
                                    ustrtolower($next_state),
                                    NULL);

                    if ($rs->rows != 0)
                    {
                        dal_query('states/setnext.sql', $state_id, $rs->fetch('state_id'));
                    }
                }

                // set states transitions
                while (count($this->transitions) > 0)
                {
                    list($group, $state_from, $state_to) = array_pop($this->transitions);

                    $rs = dal_query('states/fndk.sql',
                                    $this->template_id,
                                    ustrtolower($state_to),
                                    NULL);

                    if ($rs->rows != 0)
                    {
                        dal_query(($group < 0 ? 'states/rtadd.sql' : 'states/gtadd.sql'), $state_from, $rs->fetch('state_id'), $group);
                    }
                }
            }
        }
        elseif ($this->level == 4)
        {
            // template permissions for author
            if ($name == 'AUTHOR')
            {
                $param = array_pop($this->params);
                $param &= ~PERMIT_VIEW_RECORD;
                template_author_perm_set($this->template_id, $param);
            }
            // template permissions for responsible
            elseif ($name == 'RESPONSIBLE')
            {
                $param = array_pop($this->params);
                $param &= ~PERMIT_VIEW_RECORD;
                template_responsible_perm_set($this->template_id, $param);
            }
            // template permissions for registered
            elseif ($name == 'REGISTERED')
            {
                $param = array_pop($this->params);
                template_registered_perm_set($this->template_id, $param);
            }
            // template permissions for group
            elseif ($name == 'GROUP')
            {
                $param = array_pop($this->params);
                group_set_permissions($this->group_id, $this->template_id, $param);
                $this->group_id = NULL;
            }
            // creation of new state is completed
            elseif ($name == 'STATE')
            {
                $this->state_id = NULL;
            }
        }
        elseif ($this->level == 6)
        {
            // state transitions for author
            if ($name == 'AUTHOR')
            {
                array_pop($this->params);
            }
            // state transitions for responsible
            elseif ($name == 'RESPONSIBLE')
            {
                array_pop($this->params);
            }
            // state transitions for registered
            elseif ($name == 'REGISTERED')
            {
                array_pop($this->params);
            }
            // state transitions for group
            elseif ($name == 'GROUP')
            {
                array_pop($this->params);
            }
            // creation of new field is completed
            elseif ($name == 'FIELD')
            {
                $this->field_id = NULL;
                array_pop($this->params);
            }
        }
        elseif ($this->level == 7)
        {
            if ($name == 'STATE')
            {
                $stateName = array_pop($this->params);
                array_push($this->transitions, array(end($this->params), $this->state_id, $stateName));
            }
        }
        elseif ($this->level == 8)
        {
            // field permissions for group
            if ($name == 'GROUP')
            {
                $this->group_id = NULL;
            }
            // field list values
            elseif ($name == 'ITEM')
            {
                $itemName = array_pop($this->params);
                $value    = array_pop($this->params);

                if (is_intvalue($value))
                {
                    dal_query('values/lvcreate.sql',
                              $this->field_id,
                              $value,
                              $itemName);
                }
            }
        }

        $this->level -= 1;
    }

    function cdata_handler ($parser, $cdata)
    {
        debug_write_log(DEBUG_TRACE, '[CImporter::cdata_handler]');

        if (is_resource($parser) && $this->error != NO_ERROR)
        {
            return;
        }

        switch (end($this->lasttag))
        {
            // add account to a group
            case '4:ACCOUNT':

                $type = array_pop($this->params);
                $account = account_find_username($cdata . ($type == 'local' ? ACCOUNT_SUFFIX : NULL));

                if ($account)
                {
                    group_membership_add($this->group_id, $account['account_id']);
                }

                break;

            // accumulate template permissions
            case '5:PERMIT':

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

                if (array_key_exists($cdata, $permissions))
                {
                    $param = array_pop($this->params);
                    $param |= $permissions[$cdata];
                    array_push($this->params, $param);
                }

                break;

            // concat state transitions
            case '7:STATE':

                $concat = array_pop($this->params);
                $concat .= $cdata;

                array_push($this->params, $concat);

                break;

            // multilined field default value
            case '7:DEFAULT':

                if (end($this->params) == FIELD_TYPE_MULTILINED)
                {
                    dal_query('fields/setdefault.sql',
                              $this->field_id,
                              value_find_multilined(trim($cdata)));
                }

                break;

            // field description
            case '7:DESCRIPTION':

                if (strlen(trim($cdata)) != 0)
                {
                    dal_query('fields/setdescription.sql',
                              $this->field_id,
                              trim($cdata));
                }

                break;

            // concat new list item
            case '8:ITEM':

                $concat = array_pop($this->params);
                $concat .= $cdata;

                array_push($this->params, $concat);

                break;

            // field permissions for author
            case '8:AUTHOR':

                switch (ustrtolower($cdata))
                {
                    case 'read':
                        field_author_permission_set($this->field_id, FIELD_ALLOW_TO_READ);
                        break;
                    case 'write':
                        field_author_permission_set($this->field_id, FIELD_ALLOW_TO_WRITE);
                        break;
                }

                break;

            // field permissions for responsible
            case '8:RESPONSIBLE':

                switch (ustrtolower($cdata))
                {
                    case 'read':
                        field_responsible_permission_set($this->field_id, FIELD_ALLOW_TO_READ);
                        break;
                    case 'write':
                        field_responsible_permission_set($this->field_id, FIELD_ALLOW_TO_WRITE);
                        break;
                }

                break;

            // field permissions for registered
            case '8:REGISTERED':

                switch (ustrtolower($cdata))
                {
                    case 'read':
                        field_registered_permission_set($this->field_id, FIELD_ALLOW_TO_READ);
                        break;
                    case 'write':
                        field_registered_permission_set($this->field_id, FIELD_ALLOW_TO_WRITE);
                        break;
                }

                break;

            // field permissions for group
            case '8:GROUP':

                switch (ustrtolower($cdata))
                {
                    case 'write':
                        field_permission_add($this->field_id, $this->group_id, FIELD_ALLOW_TO_WRITE);
                    case 'read':
                        field_permission_add($this->field_id, $this->group_id, FIELD_ALLOW_TO_READ);
                }

                break;
        }
    }
}

?>
