select

    p.project_id,
    p.project_name,
    p.is_suspended,
    t.template_id,
    t.template_name,
    t.is_locked,
    s.state_name,
    s.state_abbr,
    s.state_type,
    s.next_state_id,
    ns.state_name as next_state_name,
    s.responsible

from

    tbl_projects  p,
    tbl_templates t,
    tbl_states    s
        left outer join tbl_states ns on
            s.next_state_id = ns.state_id

where

    p.project_id  = t.project_id  and
    t.template_id = s.template_id and
    s.state_id    = %1
