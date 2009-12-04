insert into tbl_reminders

   (account_id,
    reminder_name,
    subject_text,
    state_id,
    group_id,
    group_flag)

values (%1, '%2', '%3', %4, %5, %6)
