update tbl_accounts

set page_rows     = %2,
    page_bkms     = %3,
    csv_delim     = %4,
    csv_encoding  = %5,
    csv_line_ends = %6,
    theme_name    = '%7'

where account_id = %1
