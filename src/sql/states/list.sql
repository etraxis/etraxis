select

    s.state_id,
    s.state_name,
    s.state_abbr,
    s.state_type,
    s.responsible,
    ns.state_name as next_state

from tbl_states s left outer join tbl_states ns on s.next_state_id = ns.state_id
where s.template_id = %1
order by %2
