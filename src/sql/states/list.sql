select

    state_id,
    state_name,
    state_abbr,
    state_type,
    responsible

from tbl_states
where template_id = %1
order by %2
