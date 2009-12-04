select distinct record_id
from tbl_record_subscribes
where record_id = %1 and account_id = %2
