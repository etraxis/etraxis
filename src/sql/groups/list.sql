select

    project_id,
    group_id,
    group_name,
    description,
    0 as is_global

from tbl_groups

where project_id = %1

union

select

    project_id,
    group_id,
    group_name,
    description,
    1 as is_global

from tbl_groups

where project_id is null

order by %2
