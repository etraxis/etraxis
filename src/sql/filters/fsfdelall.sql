delete from tbl_fset_filters
where fset_id in (select fset_id from tbl_fsets where fset_id = %1 and account_id = %2)
