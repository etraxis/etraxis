select

    g.group_id,
    g.group_name,
    g.project_id,
    fp.perms

from

    tbl_groups      g,
    tbl_field_perms fp

where

    fp.field_id = %1         and
    fp.group_id = g.group_id and
    fp.perms    = 2

union

select

    g.group_id,
    g.group_name,
    g.project_id,
    fp.perms

from

    tbl_groups      g,
    tbl_field_perms fp

where

    fp.field_id = %1         and
    fp.group_id = g.group_id and
    fp.perms    = 1          and
    fp.group_id not in

       (select g.group_id
        from tbl_groups g, tbl_field_perms fp
        where fp.field_id = %1 and fp.group_id = g.group_id and fp.perms = 2)

order by

    group_name
