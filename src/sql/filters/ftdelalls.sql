delete from tbl_filter_trans
where state_id in (select s.state_id from tbl_states s, tbl_templates t where s.template_id = t.template_id and t.project_id = %1)
