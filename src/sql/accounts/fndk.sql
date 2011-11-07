select

    account_id,
    username,
    fullname,
    passwd,
    passwd_expire,
    is_disabled,
    locks_count,
    lock_time,
    locale

from tbl_accounts
where lower(username) = '%1'
