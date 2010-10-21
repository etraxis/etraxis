select

    filter_id,
    filter_name,
    filter_type,
    filter_flags,
    filter_param

from tbl_filters
where filter_id = %1 and account_id = %2
