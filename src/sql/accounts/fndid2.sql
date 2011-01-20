select

    username,
    fullname,
    passwd_expire,
    is_admin,
    is_ldapuser,
    locale,
    text_rows,
    page_rows,
    page_bkms,
    csv_delim,
    csv_encoding,
    csv_line_ends,
    view_id,
    theme_name

from tbl_accounts

where

    account_id   = %1 and
    is_disabled  = 0  and
    token_expire > %2 and
    (locks_count < %3 or lock_time < %4)
