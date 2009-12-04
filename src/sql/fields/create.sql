insert into tbl_fields

   (state_id,
    field_name,
    removal_time,
    field_order,
    field_type,
    is_required,
    add_separator,
    guest_access,
    registered_perm,
    author_perm,
    responsible_perm,
    regex_check,
    regex_search,
    regex_replace,
    param1,
    param2,
    value_id)

values (%1, '%2', 0, %3, %4, %5, %6, %7, 0, 0, 0, '%8', '%9', '%10', %11, %12, %13)
