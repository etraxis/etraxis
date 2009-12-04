delete from tbl_filters

where

   (filter_type = 3 and filter_param = %1) or
   (filter_type = 4 and filter_param in (select state_id from tbl_states where template_id = %1))
