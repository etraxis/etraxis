update tbl_fields

set field_name    = '%2',
    is_required   = %3,
    add_separator = %4,
    guest_access  = %5,
    description   = '%6',
    regex_check   = '%7',
    regex_search  = '%8',
    regex_replace = '%9',
    param1        = %10,
    param2        = %11,
    value_id      = %12

where field_id = %1
