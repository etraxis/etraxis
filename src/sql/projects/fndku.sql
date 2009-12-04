select project_id
from tbl_projects
where project_id <> %1 and lower(project_name) = '%2'
