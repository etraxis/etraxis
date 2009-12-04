select

    fset_id,
    fset_name

from tbl_fsets
where account_id = %1
order by fset_name
