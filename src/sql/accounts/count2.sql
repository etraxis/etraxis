select count(*)
from tbl_accounts
where is_ldapuser = 0 and is_disabled = 0
