select

    username,
    fullname,
    email,
    passwd,
    description,
    is_admin,
    is_disabled,
    is_ldapuser,
    locks_count,
    lock_time,
    locale

from tbl_accounts
where account_id = %1
