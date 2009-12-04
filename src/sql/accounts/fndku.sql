select account_id
from tbl_accounts
where account_id <> %1 and lower(username) = '%2'
