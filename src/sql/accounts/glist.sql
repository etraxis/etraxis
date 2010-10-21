select

    g.group_id,
    g.group_name

from

    tbl_groups     g,
    tbl_membership ms

where

    ms.account_id = %1         and
    ms.group_id   = g.group_id and
    g.project_id is null

order by group_name asc
