select count(*)
from tbl_events
where (originator_id = %1) or (event_type = 2 and event_param = %1)
