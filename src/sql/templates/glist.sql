select

    group_id,
    project_id,
    group_name,
    description

from tbl_groups
where group_id in (%1)
order by project_id, group_name
