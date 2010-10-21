update tbl_view_columns
set column_order = %3
where column_order = %2 and view_id = %1
