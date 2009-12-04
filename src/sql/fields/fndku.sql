select field_id
from tbl_fields
where field_id <> %1 and state_id = %2 and lower(field_name) = '%3'
