select view_id
from tbl_views
where account_id = %1 and lower(view_name) = '%2'
