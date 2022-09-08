update tbl_accounts
set google2fa_secret = null
where account_id = %1
