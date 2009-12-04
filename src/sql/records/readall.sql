insert into

    tbl_reads

select

    record_id,
    %1 as account_id,
    %2 as read_time

from

    tbl_records
