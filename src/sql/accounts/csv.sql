update tbl_accounts

set csv_delim     = %2,
    csv_encoding  = %3,
    csv_line_ends = %4

where account_id = %1
