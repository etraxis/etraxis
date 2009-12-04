select filter_id
from tbl_filters
where filter_id <> %1 and account_id = %2 and lower(filter_name) = '%3'
