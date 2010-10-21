select

    group_id,
    group_name

from

    tbl_groups

where

    project_id is null and
    group_id not in

       (select

            g.group_id

        from

            tbl_groups     g,
            tbl_membership ms

        where

            ms.account_id = %1         and
            ms.group_id   = g.group_id and
            g.project_id is null)

order by group_name asc
