update tbl_projects

set project_name = '%2',
    description  = '%3'

where project_id = %1
