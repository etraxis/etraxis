delete from tbl_filter_states
where filter_id in (select filter_id from tbl_filters where account_id = %1)
