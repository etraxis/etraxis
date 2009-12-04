delete from tbl_states
where template_id in (select template_id from tbl_templates where project_id = %1)
