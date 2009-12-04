insert into tbl_def_columns
(account_id, state_name, field_name, column_type, column_order)

    select

        %1 as account_id,
        state_name,
        field_name,
        column_type,
        column_order

    from tbl_view_columns
    where view_id = %2
    order by column_order
