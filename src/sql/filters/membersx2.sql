select distinct

    a.account_id,
    a.username,
    a.fullname,
    a.email,
    1 as is_selected

from

    tbl_accounts    a,
    tbl_templates   t,
    tbl_group_perms gp,
    tbl_membership  ms

where

    ms.group_id   = gp.group_id  and
    ms.account_id = a.account_id and

    a.account_id in

       (select account_id
        from tbl_filter_accounts
        where filter_id = %2 and filter_flag = %3) and

    gp.template_id in

       (select distinct

            t.template_id

        from

            tbl_accounts    a,
            tbl_templates   t,
            tbl_group_perms gp,
            tbl_membership  ms

        where

            t.template_id = gp.template_id and
            ms.group_id   = gp.group_id    and
            ms.account_id = a.account_id   and
            a.account_id  = %1)

union

select distinct

    a.account_id,
    a.username,
    a.fullname,
    a.email,
    0 as is_selected

from

    tbl_accounts    a,
    tbl_templates   t,
    tbl_group_perms gp,
    tbl_membership  ms

where

    ms.group_id   = gp.group_id  and
    ms.account_id = a.account_id and

    a.account_id not in

       (select account_id
        from tbl_filter_accounts
        where filter_id = %2 and filter_flag = %3) and

    gp.template_id in

       (select distinct

            t.template_id

        from

            tbl_accounts    a,
            tbl_templates   t,
            tbl_group_perms gp,
            tbl_membership  ms

        where

            t.template_id = gp.template_id and
            ms.group_id   = gp.group_id    and
            ms.account_id = a.account_id   and
            a.account_id  = %1)

order by

    fullname,
    username
