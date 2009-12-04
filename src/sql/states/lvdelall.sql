delete from tbl_list_values
where field_id in (select field_id from tbl_fields where state_id = %1)
