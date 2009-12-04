select distinct

    p.project_id,
    p.project_name

from

    tbl_accounts    a,
    tbl_projects    p,
    tbl_group_perms gp,
    tbl_membership  ms,
    tbl_templates   t,
    tbl_states      s

where

    p.project_id  = t.project_id   and
    t.template_id = s.template_id  and
    t.template_id = gp.template_id and
    ms.group_id   = gp.group_id    and
    ms.account_id = a.account_id   and

    p.is_suspended = 0  and
    t.is_locked    = 0  and
    s.state_type   = 1  and
    a.account_id   = %1 and

    gp.perms % 2 = 1

union

select distinct

    p.project_id,
    p.project_name

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s

where

    p.project_id  = t.project_id  and
    t.template_id = s.template_id and

    p.is_suspended = 0 and
    t.is_locked    = 0 and
    s.state_type   = 1 and

    t.registered_perm % 2 = 1

order by project_name
