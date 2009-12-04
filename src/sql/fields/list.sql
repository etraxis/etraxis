select

    field_id,
    field_name,
    field_order,
    field_type,
    is_required,
    add_separator,
    regex_check,
    regex_search,
    regex_replace,
    param1,
    param2,
    value_id

from tbl_fields
where state_id = %1
order by %2
