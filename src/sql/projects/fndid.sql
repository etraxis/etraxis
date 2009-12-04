select

    project_id,
    project_name,
    start_time,
    description,
    is_suspended

from tbl_projects
where project_id = %1
