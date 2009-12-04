delete from tbl_fset_filters
where fset_id in (select fset_id from tbl_fsets where account_id = %1)
