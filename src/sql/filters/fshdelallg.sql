delete from tbl_filter_sharing
where group_id in (select group_id from tbl_groups where project_id = %1)
