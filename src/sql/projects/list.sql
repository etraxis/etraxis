select

    p.project_id,
    p.project_name,
    p.start_time,
    p.description,
    p.is_suspended

from tbl_projects p
order by %1
