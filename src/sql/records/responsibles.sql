select distinct

    a.account_id,
    a.username,
    a.fullname

from

    tbl_accounts    a,
    tbl_templates   t,
    tbl_group_perms gp,
    tbl_membership  ms,
    tbl_states      s,
    tbl_group_trans gt

where

    t.template_id  = gp.template_id and
    ms.group_id    = gp.group_id    and
    ms.account_id  = a.account_id   and
    gt.group_id    = gp.group_id    and
    gt.state_id_to = s.state_id     and

    t.project_id     = %1 and
    gt.state_id_from = %2 and
    a.is_disabled    = 0

union

select distinct

    a.account_id,
    a.username,
    a.fullname

from

    tbl_accounts   a,
    tbl_states     s,
    tbl_role_trans rt

where

    rt.state_id_to   = s.state_id and
    rt.state_id_from = %2         and
    a.account_id     = %3         and
    rt.role          = -1

order by

    fullname,
    username
