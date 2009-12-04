select state_id
from tbl_states
where state_id <> %1 and template_id = %2 and (lower(state_name) = '%3' or lower(state_abbr) = '%4')
