select

    account_id,
    username,
    fullname,
    passwd,
    is_disabled,
    locks_count,
    lock_time,
    locale

from tbl_accounts
where lower(username) = '%1'
