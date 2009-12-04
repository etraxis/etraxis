select distinct

    a.account_id,
    a.email,
    a.locale

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s,
    tbl_records   r
        left outer join tbl_accounts a on
            r.responsible_id = a.account_id

where

    p.project_id  = t.project_id  and
    t.template_id = s.template_id and
    s.state_id    = r.state_id    and
    r.state_id    = %1
