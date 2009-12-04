delete from tbl_field_perms
where field_id in (select field_id from tbl_fields where state_id = %1)
