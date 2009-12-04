update tbl_accounts

set passwd        = '%2',
    passwd_expire = %3

where account_id = %1
