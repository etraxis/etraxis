select distinct

    r.record_id,
    r.creation_time,
    r.closure_time,
    r.postpone_time,
    t.template_prefix,
    t.critical_age,
    r.subject,
    s.state_abbr,
    a.fullname,
    ch.is_dependency

from

    tbl_children  ch,
    tbl_templates t,
    tbl_states    s,
    tbl_records   r
        left outer join tbl_accounts a on r.responsible_id = a.account_id

where

    t.template_id = s.template_id and
    s.state_id    = r.state_id    and
    r.record_id   = ch.child_id   and
    ch.parent_id  = %1

order by r.record_id
