delete from tbl_filter_sharing
where filter_id in (select filter_id from tbl_filters where account_id = %1)
