update tbl_accounts

set auth_token   = '%2',
    token_expire = %3

where account_id = %1
