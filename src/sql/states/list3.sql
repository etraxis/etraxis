select

    state_id,
    state_name,
    state_type

from tbl_states
where template_id = %1
order by state_name
