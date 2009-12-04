insert into tbl_records

   (state_id,
    subject,
    responsible_id,
    creator_id,
    creation_time,
    change_time,
    postpone_time)

values (%1, '%2', %3, %4, %5, %5, 0)
