insert into tbl_accounts

   (username,
    fullname,
    email,
    passwd,
    description,
    auth_token,
    token_expire,
    passwd_expire,
    is_admin,
    is_disabled,
    is_ldapuser,
    locks_count,
    lock_time,
    locale,
    page_rows,
    page_bkms,
    csv_delim,
    csv_encoding,
    csv_line_ends,
    view_id)

values ('%1', '%2', '%3', '%4', '%5', NULL, 0, 0, %6, %7, %8, 0, 0, %9, %10, %11, 44, 1, 1, NULL)
