update tbl_def_columns
set column_order = %3
where column_order = %2 and account_id = %1
