select

    filter_id,
    filter_name,
    filter_type,
    filter_flags,
    filter_param,
    NULL as username,
    NULL as fullname,
    0    as shared

from

    tbl_filters

where

    account_id = %1 and
    filter_id not in

       (select

            f.filter_id

        from

            tbl_filters           f,
            tbl_filter_activation fa

        where

            f.filter_id  = fa.filter_id  and
            f.account_id = fa.account_id and
            f.account_id = %1)

union

select distinct

    f.filter_id,
    f.filter_name,
    f.filter_type,
    f.filter_flags,
    f.filter_param,
    a.username,
    a.fullname,
    1 as shared

from

    tbl_accounts          a,
    tbl_membership        ms,
    tbl_filters           f,
    tbl_filter_sharing    fsh

where

    f.filter_id   = fsh.filter_id and
    f.account_id  = a.account_id  and
    ms.group_id   = fsh.group_id  and
    ms.account_id = %1            and
    f.account_id  <> %1           and
    f.filter_id not in

       (select distinct

            f.filter_id

        from

            tbl_accounts          a,
            tbl_membership        ms,
            tbl_filters           f,
            tbl_filter_sharing    fsh,
            tbl_filter_activation fa

        where

            f.filter_id   = fsh.filter_id and
            f.account_id  = a.account_id  and
            ms.group_id   = fsh.group_id  and
            ms.account_id = fa.account_id and
            f.filter_id   = fa.filter_id  and
            fa.account_id = %1            and
            f.account_id  <> %1)

order by

    shared,
    fullname,
    username,
    filter_name
