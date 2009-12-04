select distinct

    a.email

from

    tbl_accounts    a,
    tbl_membership  ms,
    tbl_group_perms gp,
    tbl_states      s,
    tbl_records     r

where

    ms.group_id    = gp.group_id   and
    ms.account_id  = a.account_id  and
    gp.template_id = s.template_id and
    s.state_id     = r.state_id    and
    r.record_id    = %1            and
    (gp.perms & %2) <> 0

union

select distinct

    a.email

from

    tbl_accounts  a,
    tbl_templates t,
    tbl_states    s,
    tbl_records   r

where

    t.template_id = s.template_id and
    s.state_id    = r.state_id    and
    r.record_id   = %1            and
    r.creator_id  = a.account_id  and
    (t.author_perm & %2) <> 0

union

select distinct

    a.email

from

    tbl_accounts  a,
    tbl_templates t,
    tbl_states    s,
    tbl_records   r

where

    t.template_id    = s.template_id and
    s.state_id       = r.state_id    and
    r.record_id      = %1            and
    r.responsible_id = a.account_id  and
    (t.responsible_perm & %2) <> 0
