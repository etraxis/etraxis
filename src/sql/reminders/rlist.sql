select

    r.record_id,
    p.project_name,
    t.template_prefix,
    s.state_abbr,
    r.subject,
    a.fullname as fullname

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s,
    tbl_records   r
        left outer join tbl_accounts a on
            r.creator_id = a.account_id

where

    p.project_id  = t.project_id  and
    t.template_id = s.template_id and
    s.state_id    = r.state_id    and
    r.state_id    = %1

order by r.record_id
