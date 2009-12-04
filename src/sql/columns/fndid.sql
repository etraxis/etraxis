select

    state_name,
    field_name,
    column_type,
    column_order

from tbl_def_columns
where column_id = %1 and account_id = %2
