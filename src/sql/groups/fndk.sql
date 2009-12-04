select group_id
from tbl_groups
where project_id %1 and lower(group_name) = '%2'
