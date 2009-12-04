delete from tbl_filter_states
where state_id in (select state_id from tbl_states where template_id = %1)
