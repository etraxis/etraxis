select

    g.group_id,
    g.group_name,
    g.project_id,
    gp.perms

from

    tbl_groups      g,
    tbl_group_perms gp

where

    gp.group_id    = g.group_id and
    gp.template_id = %1
