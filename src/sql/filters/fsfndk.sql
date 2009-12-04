select fset_id
from tbl_fsets
where account_id = %1 and lower(fset_name) = '%2'
