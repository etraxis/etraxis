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
    text_rows,
    page_rows,
    page_bkms,
    csv_delim,
    csv_encoding,
    csv_line_ends,
    view_id,
    theme_name)

values ('%1', '%2', '%3', '%4', '%5', NULL, 0, 0, %6, %7, %8, 0, 0, %9, %10, %11, %12, 44, 1, 1, NULL, '%13')
