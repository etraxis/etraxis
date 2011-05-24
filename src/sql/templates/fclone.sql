insert into tbl_fields
(template_id, state_id, field_name, removal_time, field_order, field_type, is_required, guest_access, registered_perm, author_perm, responsible_perm, add_separator, show_in_emails, regex_check, regex_search, regex_replace, param1, param2, value_id)

    select distinct

        sd.template_id,
        sd.state_id,
        f.field_name,
        f.removal_time,
        f.field_order,
        f.field_type,
        f.is_required,
        f.guest_access,
        f.registered_perm,
        f.author_perm,
        f.responsible_perm,
        f.add_separator,
        f.show_in_emails,
        f.regex_check,
        f.regex_search,
        f.regex_replace,
        f.param1,
        f.param2,
        f.value_id

    from

        tbl_states ss,
        tbl_states sd,
        tbl_fields f

    where

        ss.template_id = %1         and
        sd.template_id = %2         and
        f.removal_time = 0          and
        ss.state_id    = f.state_id and
        ss.state_name  = sd.state_name

    order by

        sd.state_id,
        f.field_order
