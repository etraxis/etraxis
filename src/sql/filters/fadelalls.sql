delete from tbl_filter_accounts
where filter_id in (select filter_id from tbl_filters where filter_type = 4 and filter_param = %1)
