delete from tbl_filter_fields
where filter_id in (select filter_id from tbl_filters where account_id = %1)
