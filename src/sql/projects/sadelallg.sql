delete from tbl_state_assignees
where group_id in (select group_id from tbl_groups where project_id = %1)
