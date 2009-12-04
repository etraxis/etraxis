update tbl_states
set state_type = 2
where template_id = %1 and state_type = 1
