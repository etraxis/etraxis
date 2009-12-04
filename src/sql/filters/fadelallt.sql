delete from tbl_filter_accounts

where

   (filter_id in (select filter_id from tbl_filters where filter_type = 3 and filter_param = %1)) or
   (filter_id in (select f.filter_id from tbl_filters f, tbl_states s where f.filter_type = 4 and f.filter_param = s.state_id and s.template_id = %1))
