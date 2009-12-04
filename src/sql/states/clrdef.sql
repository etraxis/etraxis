update tbl_states
set next_state_id = NULL
where next_state_id = %1
