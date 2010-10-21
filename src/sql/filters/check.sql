select count(*)
from tbl_filter_activation
where filter_id = %1 and account_id = %2
