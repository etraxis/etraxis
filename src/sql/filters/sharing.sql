select

    g.group_id,
    g.group_name,
    g.description,
    0 as is_global,
    1 as is_selected

from

    tbl_groups         g,
    tbl_filter_sharing fsh

where

    fsh.group_id  = g.group_id and
    fsh.filter_id = %1         and
    g.project_id  = %2

union

select

    group_id,
    group_name,
    description,
    0 as is_global,
    0 as is_selected

from

    tbl_groups

where

    project_id = %2 and
    group_id not in

       (select

            g.group_id

        from

            tbl_groups         g,
            tbl_filter_sharing fsh

        where

            fsh.group_id  = g.group_id and
            fsh.filter_id = %1         and
            g.project_id  = %2)

union

select

    g.group_id,
    g.group_name,
    g.description,
    1 as is_global,
    1 as is_selected

from

    tbl_groups         g,
    tbl_filter_sharing fsh

where

    fsh.group_id  = g.group_id and
    fsh.filter_id = %1         and
    g.project_id is null

union

select

    group_id,
    group_name,
    description,
    1 as is_global,
    0 as is_selected

from

    tbl_groups

where

    project_id is null and
    group_id not in

       (select

            g.group_id

        from

            tbl_groups         g,
            tbl_filter_sharing fsh

        where

            fsh.group_id  = g.group_id and
            fsh.filter_id = %1         and
            g.project_id is null)

order by is_global, group_name
