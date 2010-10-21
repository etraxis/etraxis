select

    column_id,
    state_name,
    field_name,
    column_type,
    column_order

from tbl_view_columns
where view_id = %1
order by column_order
