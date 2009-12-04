select

    p.project_id,
    p.project_name,
    p.is_suspended,
    g.group_name,
    g.description,
    0 as is_global

from

    tbl_projects p
        right outer join tbl_groups g on
            p.project_id = g.project_id

where

    g.group_id = %1
