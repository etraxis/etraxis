select count(*)
from tbl_fields
where state_id = %1 and removal_time = 0
