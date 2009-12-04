update tbl_fields
set field_order = %3
where field_order = %2 and state_id = %1 and removal_time = 0
