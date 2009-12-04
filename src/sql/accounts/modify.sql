update tbl_accounts

set username    = '%2',
    fullname    = '%3',
    email       = '%4',
    description = '%5',
    is_admin    = %6,
    is_disabled = %7,
    locks_count = %8

where account_id = %1
