update tbl_accounts

set locks_count = locks_count + 1,
    lock_time   = %2

where account_id = %1
