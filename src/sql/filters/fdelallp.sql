delete from tbl_filters

where

   (filter_type = 2 and filter_param = %1) or
   (filter_type = 3 and filter_param in (select template_id from tbl_templates where project_id = %1)) or
   (filter_type = 4 and filter_param in (select s.state_id from tbl_states s, tbl_templates t where s.template_id = t.template_id and t.project_id = %1))
