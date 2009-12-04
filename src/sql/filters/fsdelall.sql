delete from tbl_filter_states
where filter_id in (select filter_id from tbl_filters where filter_id = %1 and account_id = %2)
