select distinct

    p.project_id,
    p.project_name,
    p.start_time,
    p.description,
    p.is_suspended

from

    tbl_accounts    a,
    tbl_projects    p,
    tbl_templates   t,
    tbl_group_perms gp,
    tbl_membership  ms

where

    p.project_id  = t.project_id   and
    t.template_id = gp.template_id and
    ms.group_id   = gp.group_id    and
    ms.account_id = a.account_id   and
    a.account_id  = %1

union

select distinct

    p.project_id,
    p.project_name,
    p.start_time,
    p.description,
    p.is_suspended

from

    tbl_projects  p,
    tbl_templates t

where

    p.project_id   = t.project_id and
    t.guest_access = 1

order by %2
