delete from tbl_filter_trans
where filter_id in (select filter_id from tbl_filters where account_id = %1)
