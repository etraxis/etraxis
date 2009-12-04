update tbl_filters

set filter_name  = '%2',
    filter_type  = %3,
    filter_flags = %4

where filter_id = %1
