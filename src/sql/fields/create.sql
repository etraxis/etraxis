insert into tbl_fields

   (template_id,
    state_id,
    field_name,
    removal_time,
    field_order,
    field_type,
    is_required,
    add_separator,
    show_in_emails,
    guest_access,
    registered_perm,
    author_perm,
    responsible_perm,
    description,
    regex_check,
    regex_search,
    regex_replace,
    param1,
    param2,
    value_id)

values (%1, %2, '%3', 0, %4, %5, %6, %7, %8, %9, 0, 0, 0, '%10', '%11', '%12', '%13', %14, %15, %16)
