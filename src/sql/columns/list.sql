select

    column_id,
    state_name,
    field_name,
    column_type,
    column_order

from tbl_def_columns
where account_id = %1
order by column_order
