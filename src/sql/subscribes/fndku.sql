select subscribe_id
from tbl_subscribes
where subscribe_id <> %1 and account_id = %2 and lower(subscribe_name) = '%3'
