update tbl_accounts

set page_rows     = %2,
    page_bkms     = %3,
    theme_name    = '%4'

where account_id = %1
