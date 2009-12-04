insert into

    tbl_field_perms

select

    %2 as field_id,
    group_id,
    %3 as perms

from

    tbl_groups

where

    project_id = %1
