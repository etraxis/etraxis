select

    t.project_id,
    s.state_id,
    s.state_name,
    s.state_abbr,
    s.state_type,
    n.state_name as next_state,
    s.responsible

from

    tbl_templates t,
    tbl_states    s
        left outer join tbl_states n on s.next_state_id = n.state_id

where

    s.template_id = t.template_id and
    t.template_id = %1

order by

    s.state_type,
    s.state_name
