select template_id
from tbl_templates
where project_id = %1 and lower(template_name) = '%2'
