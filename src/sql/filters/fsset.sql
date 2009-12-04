insert into tbl_filter_activation
(filter_id, account_id)

    select

        filter_id,
        %1 as account_id

    from tbl_fset_filters

    where fset_id = %2
