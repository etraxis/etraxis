update tbl_fields

set field_name    = '%2',
    is_required   = %3,
    add_separator = %4,
    guest_access  = %5,
    regex_check   = '%6',
    regex_search  = '%7',
    regex_replace = '%8',
    param1        = %9,
    param2        = %10,
    value_id      = %11

where field_id = %1
