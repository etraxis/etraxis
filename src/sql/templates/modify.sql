update tbl_templates

set template_name   = '%2',
    template_prefix = '%3',
    critical_age    =  %4,
    frozen_time     =  %5,
    description     = '%6',
    guest_access    =  %7

where template_id = %1
