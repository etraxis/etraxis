select fset_id
from tbl_fsets
where fset_id <> %1 and account_id = %2 and lower(fset_name) = '%3'
