select distinct account_id as event_param
from tbl_record_subscribes
where record_id = %1
