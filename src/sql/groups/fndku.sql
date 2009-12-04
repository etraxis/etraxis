select group_id
from tbl_groups
where group_id <> %1 and project_id %2 and lower(group_name) = '%3'
