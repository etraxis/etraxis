select

    field_id,
    field_name,
    field_order,
    field_type,
    is_required,
    guest_access,
    registered_perm,
    author_perm,
    responsible_perm,
    add_separator,
    show_in_emails,
    description,
    regex_check,
    regex_search,
    regex_replace,
    param1,
    param2,
    value_id

from tbl_fields
where state_id = %1 and removal_time = 0
order by %2
