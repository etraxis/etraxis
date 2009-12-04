insert into tbl_states
(template_id, state_name, state_abbr, state_type, next_state_id, responsible)

    select %2, state_name, state_abbr, state_type, next_state_id, responsible
    from tbl_states
    where template_id = %1
    order by state_id
