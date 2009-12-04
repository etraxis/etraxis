delete from tbl_view_columns
where view_id in (select view_id from tbl_views where account_id = %1)
