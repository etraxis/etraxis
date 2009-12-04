insert into tbl_fset_filters
(fset_id, filter_id)

    select

        %2 as fset_id,
        f.filter_id

    from

        tbl_filters           f,
        tbl_filter_activation fa

    where

        f.filter_id  = fa.filter_id  and
        f.account_id = fa.account_id and
        f.account_id = %1

    union

    select distinct

        %2 as fset_id,
        f.filter_id

    from

        tbl_membership        ms,
        tbl_filters           f,
        tbl_filter_sharing    fsh,
        tbl_filter_activation fa

    where

        f.filter_id   = fsh.filter_id and
        ms.group_id   = fsh.group_id  and
        ms.account_id = fa.account_id and
        f.filter_id   = fa.filter_id  and
        fa.account_id = %1            and
        f.account_id  <> %1
