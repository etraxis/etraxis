select

    account_id,
    username,
    fullname,
    email,
    description,
    is_admin,
    is_disabled,
    locks_count,
    lock_time

from tbl_accounts
where is_ldapuser = 0
order by %1
