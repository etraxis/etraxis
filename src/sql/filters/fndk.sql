select filter_id
from tbl_filters
where account_id = %1 and lower(filter_name) = '%2'
