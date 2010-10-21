select subscribe_id
from tbl_subscribes
where account_id = %1 and lower(subscribe_name) = '%2'
