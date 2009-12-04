select

    view_id,
    view_name

from tbl_views
where account_id = %1
order by view_name
