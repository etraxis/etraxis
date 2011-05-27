update tbl_fields

set field_name     = '%2',
    is_required    = %3,
    add_separator  = %4,
    guest_access   = %5,
    show_in_emails = %6,
    description    = '%7',
    regex_check    = '%8',
    regex_search   = '%9',
    regex_replace  = '%10',
    param1         = %11,
    param2         = %12,
    value_id       = %13

where field_id = %1
