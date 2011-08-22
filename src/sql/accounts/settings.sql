update tbl_accounts

set text_rows     = %2,
    page_rows     = %3,
    page_bkms     = %4,
    auto_refresh  = %5,
    theme_name    = '%6'

where account_id = %1
