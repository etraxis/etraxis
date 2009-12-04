update tbl_projects

set project_name = '%2',
    description  = '%3',
    is_suspended = %4

where project_id = %1
