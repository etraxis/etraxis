select view_id
from tbl_views
where view_id <> %1 and account_id = %2 and lower(view_name) = '%3'
