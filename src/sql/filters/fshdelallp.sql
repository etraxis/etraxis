delete from tbl_filter_sharing

where

   (filter_id in (select filter_id from tbl_filters where filter_type = 2 and filter_param = %1)) or
   (filter_id in (select f.filter_id from tbl_filters f, tbl_templates t where f.filter_type = 3 and f.filter_param = t.template_id and t.project_id = %1)) or
   (filter_id in (select f.filter_id from tbl_filters f, tbl_states s, tbl_templates t where f.filter_type = 4 and f.filter_param = s.state_id and s.template_id = t.template_id and t.project_id = %1))
