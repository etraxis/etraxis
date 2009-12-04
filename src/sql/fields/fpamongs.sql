select

    g.group_id,
    g.group_name,
    g.project_id

from

    tbl_groups      g,
    tbl_field_perms fp

where

    (g.project_id = %1 or g.project_id is null) and
    fp.field_id   = %2 and
    fp.perms      = %3 and
    fp.group_id   = g.group_id

order by

    g.group_name
