select event_param
from tbl_events
where record_id = %1 and event_type = 2
order by event_time desc
