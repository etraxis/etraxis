select

    g.group_id,
    g.group_name,
    0 as is_global

from

    tbl_templates       t,
    tbl_states          s,
    tbl_groups          g,
    tbl_state_assignees sa

where

    g.project_id  = t.project_id  and
    t.template_id = s.template_id and
    s.state_id    = sa.state_id   and
    sa.group_id   = g.group_id    and
    sa.state_id   = %1

union

select

    g.group_id,
    g.group_name,
    1 as is_global

from

    tbl_groups          g,
    tbl_state_assignees sa

where

    g.project_id is null     and
    sa.group_id = g.group_id and
    sa.state_id = %1

order by

    is_global,
    group_name
