update tbl_states

set state_name    = '%2',
    state_abbr    = '%3',
    next_state_id = %4,
    responsible   = %5

where state_id = %1
