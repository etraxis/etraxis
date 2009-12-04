select template_id
from tbl_templates
where template_id <> %1 and project_id = %2 and (lower(template_name) = '%3' or lower(template_prefix) = '%4')
