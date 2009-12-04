select account_id
from tbl_accounts
where account_id = %1 and auth_token = '%2' and token_expire > %3
