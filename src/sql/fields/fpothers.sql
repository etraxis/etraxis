select

    group_id,
    group_name,
    project_id

from tbl_groups

where

    (project_id = %1 or project_id is null) and
    group_id not in (select g.group_id
                     from tbl_groups g, tbl_field_perms fp
                     where fp.field_id = %2 and fp.group_id = g.group_id and fp.perms = %3)

order by

    group_name
