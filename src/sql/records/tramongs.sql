select distinct

    s.state_id,
    s.state_name

from

    tbl_projects    p,
    tbl_templates   t,
    tbl_group_perms gp,
    tbl_membership  ms,
    tbl_states      s,
    tbl_group_trans gt,
    tbl_records     r

where

    p.project_id     = t.project_id   and
    t.template_id    = gp.template_id and
    ms.group_id      = gp.group_id    and
    ms.account_id    = %2             and
    gt.state_id_from = r.state_id     and
    gt.state_id_to   = s.state_id     and
    gt.group_id      = gp.group_id    and
    r.record_id      = %1 %3

union

select distinct

    s.state_id,
    s.state_name

from

    tbl_states     s,
    tbl_role_trans rt,
    tbl_records    r

where

    rt.state_id_from = r.state_id and
    rt.state_id_to   = s.state_id and
    rt.role          = -1         and
    r.record_id      = %1         and
    r.creator_id     = %2 %3

union

select distinct

    s.state_id,
    s.state_name

from

    tbl_states     s,
    tbl_role_trans rt,
    tbl_records    r

where

    rt.state_id_from = r.state_id and
    rt.state_id_to   = s.state_id and
    rt.role          = -2         and
    r.record_id      = %1         and
    r.responsible_id = %2 %3

union

select distinct

    s.state_id,
    s.state_name

from

    tbl_states     s,
    tbl_role_trans rt,
    tbl_records    r

where

    rt.state_id_from = r.state_id and
    rt.state_id_to   = s.state_id and
    rt.role          = -3         and
    r.record_id      = %1 %3

order by

    state_name
