delete from tbl_field_perms
where field_id in (select f.field_id from tbl_fields f, tbl_states s where f.state_id = s.state_id and s.template_id = %1)
