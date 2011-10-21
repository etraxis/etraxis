update tbl_accounts

set timezone      = %2,
    text_rows     = %3,
    page_rows     = %4,
    page_bkms     = %5,
    auto_refresh  = %6,
    theme_name    = '%7'

where account_id = %1
