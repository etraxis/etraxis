delete from tbl_membership
where group_id in (select group_id from tbl_groups where project_id = %1)
