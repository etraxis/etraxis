delete from tbl_state_assignees
where state_id in (select state_id from tbl_states where template_id = %1)
