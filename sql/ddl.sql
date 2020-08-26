# Data definition commands
create table staffs
(
    eid            int primary key,
    sin            int         not null unique,
    fName          varchar(25) not null,
    lName          varchar(40) not null,
    phoneExtension varchar(25) not null
);
alter table staffs
    add unique (sin);

create table dentalWorkers
(
    eid int primary key references staffs(eid)
);

create table dentists
(
    eid int primary key references staffs(eid)
);

create table dentalAssistants references staffs(eid)
(
    eid int primary key
);

create table receptionists
(
    eid int primary key references staffs(eid)
);

create table assists
(
    eidDentist         int,
    eidDentalAssistant int,
    foreign key (eidDentist) references dentists (eid),
    foreign key (eidDentalAssistant) references dentalAssistants (eid)
);

create table clinics
(
    cname   varchar(30),
    address varchar(35),
    primary key (cname, address)
);

create table worksIn
(
    eid     int,
    cname   varchar(30),
    address varchar(35),
    primary key (eid, address, cname),
    foreign key (eid) references staffs (eid),
    foreign key (cname, address) references clinics (cname, address)
);

create table patients
(
    pid         int primary key,
    hin         varchar(10) not null unique,
    fName       varchar(25) not null,
    lName       varchar(40) not null,
    address     varchar(35) not null,
    phoneNumber varchar(12) not null
);
alter table patients
    add unique (hin);

create table appointments
(
    pid             int,
    dateAndTime     datetime,
    status          varchar(20) not null check ( status in ('scheduled', 'cancelled', 'missed', 'completed') ),
    eidDentist      int,
    # foreign key it may be null since may not know who dentist is yet - combined from the With-A relationship
    eidReceptionist int         not null, # foreign key to combine Schedules
    # foreign key for the In-A appointments
    address         varchar(35) not null,
    cname           varchar(30) not null,
    primary key (pid, dateAndTime),
    foreign key (pid) references patients (pid),
    foreign key (eidDentist) references dentists (eid),
    foreign key (eidReceptionist) references receptionists (eid),
    foreign key (cname, address) references clinics (cname, address)
);
delimiter $$
create trigger after_insert_appt
    before insert
    on appointments
    for each row
begin
    if ((new.eidDentist is not null) and new.eidDentist not in (
        select dentists.eid
        from staffs,
             dentists,
             worksIn
        where staffs.eid = dentists.eid
          and staffs.eid = worksIn.eid
          and worksIn.cname = new.cname
          and worksIn.address = new.address
    )) or new.eidReceptionist not in (
        select receptionists.eid
        from staffs,
             receptionists,
             worksIn
        where staffs.eid = receptionists.eid
          and staffs.eid = worksIn.eid
          and worksIn.cname = new.cname
          and worksIn.address = new.address
    ) then
        signal sqlstate '45000' set message_text = 'Cannot insert. Incorrect dentist/receptionist eid.';
    end if;
end$$
delimiter ;
delimiter $$
create trigger after_update_appt
    before update
    on appointments
    for each row
begin
    if ((new.eidDentist is not null) and new.eidDentist not in (
        select dentists.eid
        from staffs,
             dentists,
             worksIn
        where staffs.eid = dentists.eid
          and staffs.eid = worksIn.eid
          and worksIn.cname = new.cname
          and worksIn.address = new.address
    )) or new.eidReceptionist not in (
        select receptionists.eid
        from staffs,
             receptionists,
             worksIn
        where staffs.eid = receptionists.eid
          and staffs.eid = worksIn.eid
          and worksIn.cname = new.cname
          and worksIn.address = new.address
    ) then
        signal sqlstate '45000' set message_text = 'Cannot update. Incorrect dentist/receptionist eid.';
    end if;
end$$
delimiter ;

create table treatments
(
    name        varchar(35),
    toothNumber dec(2, 0) check (toothNumber >= 0 and toothNumber <= 32 ), # since 0 to 32
    cost        dec(6, 2) not null,
    primary key (name, toothNumber)
);

create table executedBy
(
    pid             int,
    dateAndTime     datetime,
    treatmentName   varchar(35),
    toothNumber     dec(2, 0),
    eidDentalWorker int not null,
    primary key (pid, dateAndTime, treatmentName, toothNumber),
    foreign key (pid, dateAndTime) references appointments (pid, dateAndTime),
    foreign key (treatmentName, toothNumber) references treatments (name, toothNumber),
    foreign key (eidDentalWorker) references dentalWorkers (EID)
);
delimiter $$
create trigger after_insert_billedIn
    after insert
    on billedIn
    for each row
begin
    if (new.toothNumber, new.treatmentName) not in (
        select executedBy.toothNumber, executedBy.treatmentName
        from executedBy
        where executedBy.pid = new.pid
          and executedBy.dateAndTime = new.dateAndTime
    ) then
         signal sqlstate '45000' set message_text = 'Cannot insert. Treatment info does not match the one in the bill.';
    end if;
end$$
delimiter ;
delimiter $$
create trigger after_update_billedIn
    before update
    on billedIn
    for each row
begin
    if (new.toothNumber, new.treatmentName) not in (
        select executedBy.toothNumber, executedBy.treatmentName
        from executedBy
        where executedBy.pid = new.pid
          and executedBy.dateAndTime = new.dateAndTime
    ) then
         signal sqlstate '45000' set message_text = 'Cannot update. Treatment info does not match the one in the bill.';
    end if;
end$$
delimiter ;


create table bills
(
    pid             int,
    dateAndTime     datetime,
    status          varchar(25) not null,
    eidReceptionist int         not null,
    primary key (pid, dateAndTime),
    foreign key (pid, dateAndTime) references appointments (pid, dateAndTime),
    foreign key (eidReceptionist) references receptionists (eid)
);

# not sure if this is ncessary after all, discuss the redundancy
create table billedIn
(
    pid           int,
    dateAndTime   datetime,
    toothNumber   dec(2, 0),
    treatmentName varchar(35),
    primary key (pid, dateAndTime, toothNumber, treatmentName),
    foreign key (pid, dateAndTime) references bills (pid, dateAndTime),
    foreign key (treatmentName, toothNumber) references treatments (name, toothNumber)
);
delimiter $$
create trigger after_insert_billedIn
    after insert
    on billedIn
    for each row
begin
    if (new.toothNumber, new.treatmentName) not in (
        select executedBy.toothNumber, executedBy.treatmentName
        from executedBy
        where executedBy.pid = new.pid
          and executedBy.dateAndTime = new.dateAndTime
    ) then
         signal sqlstate '45000' set message_text = 'Cannot insert. Treatment info does not match the one in the bill.';
    end if;
end$$
delimiter ;
delimiter $$
create trigger after_update_billedIn
    before update
    on billedIn
    for each row
begin
    if (new.toothNumber, new.treatmentName) not in (
        select executedBy.toothNumber, executedBy.treatmentName
        from executedBy
        where executedBy.pid = new.pid
          and executedBy.dateAndTime = new.dateAndTime
    ) then
         signal sqlstate '45000' set message_text = 'Cannot update. Treatment info does not match the one in the bill.';
    end if;
end$$
delimiter ;

# for the DBA only
create table admin
(
    username varchar(20),
    password varchar(20)
);
insert into admin
values ('admin', 'admin123');

# Drop tales to do reset
drop table billedIn;
drop table bills;
drop table executedBy;
drop table treatments;
drop table appointments;
drop table patients;
drop table worksIn;
drop table clinics;
drop table assists;
drop table receptionists;
drop table dentists;
drop table dentalAssistants;
drop table dentalWorkers;
drop table staffs;
# Show tables
show tables;
# Queries
# Details of dentist: From staff + which clincs does he work at
# SOLUTION PART A
select staffs.*, worksIn.address, worksIn.cname
from staffs,
     dentists,
     worksIn
where staffs.eid = dentists.eid
  and staffs.eid = worksIn.eid
order by staffs.lName;

# Inputted week will always be the sunday, ensured via the ui
# SOLUTION FOR B
select pid, dateAndTime, eidDentist, eidReceptionist, cname, status
from appointments
where eidDentist = 27
  and DATEDIFF(appointments.dateAndTime, '2020-03-15 09:00:00') < 7
  and DATEDIFF(appointments.dateAndTime, '2020-03-15 09:00:00') >= 0
order by dateAndTime;

# Details of all appointments at a given clinic on a specific date
# SOLUTION FOR C)
select patients.fName as patientFName,
       patients.lName as patientLName,
       appointments.dateAndTime,
       S1.fName       as dentistFName,
       S1.lName       as dentistLName,
       S2.fName       as recepFName,
       S2.lName       as recepLName
from appointments,
     patients,
     dentists,
     receptionists,
     staffs S1,
     staffs S2
where appointments.cname = 'The Ivey Clinic'
  and appointments.address = '2298 Ivey street'
  and date_format(appointments.dateAndTime, '%y-%m-%d') = '20-03-21'
  and appointments.pid = patients.pid
  and appointments.eidDentist is not null
  and appointments.eidDentist = dentists.eid
  and dentists.eid = S1.eid
  and appointments.eidReceptionist = receptionists.eid
  and receptionists.eid = S2.eid
union
select patients.fName as patientFName,
       patients.lName as patientLName,
       appointments.dateAndTime,
       'TBD'          as dentistFName,
       'TBD'          as dentistLName,
       S2.fName       as recepFName,
       S2.lName       as recepLName
from appointments,
     patients,
     receptionists,
     staffs S2
where appointments.cname = 'The Ivey Clinic'
  and appointments.address = '2298 Ivey street'
  and date_format(appointments.dateAndTime, '%y-%m-%d') = '20-03-21'
  and appointments.pid = patients.pid
  and appointments.eidDentist is null
  and appointments.eidReceptionist = receptionists.eid
  and receptionists.eid = S2.eid;

# All the appointments of a given patient
# SOLUTION PART D
select appointments.*
from appointments
where 0 = appointments.pid;
# this is input from the webpage

# Number of missed appointments for each patient
# SOLUTION E
select patients.pid, patients.fName, patients.lName, count(*), 'missed appointment(s)' as missed
from appointments,
     patients
where appointments.pid = patients.pid
  and appointments.status = 'missed'
group by patients.pid;

# list of all treatments made during appointment
# SOLUTION F
select patients.fName,
       patients.lName,
       executedBy.dateAndTime,
       executedBy.treatmentName,
       executedBy.toothNumber,
       staffs.fName as staffFName,
       staffs.lName as staffLName
from executedBy,
     patients,
     staffs
where executedBy.pid = 46
  and executedBy.dateAndTime = '2020-03-06 15:00:00'
  and patients.pid = 46
  and executedBy.eidDentalWorker = staffs.eid;

# SOLUTION G
# details of all bills
select bills.*, appointments.*
from bills,
     appointments
where bills.pid = appointments.pid
  and bills.dateAndTime = appointments.dateAndTime;
# details of all bills that remain unpaid
select patients.fName,
       patients.lName,
       bills.pid,
       bills.dateAndTime,
       bills.status,
       staffs.lName,
       staffs.fName,
       treatments.*,
       appointments.cname,
       appointments.address
from bills,
     billedIn,
     treatments,
     patients,
     staffs,
     appointments
where bills.pid = patients.pid
  and appointments.pid = bills.pid
  and appointments.dateAndTime = bills.dateAndTime
  and bills.eidReceptionist = staffs.eid
  and bills.pid = billedIn.pid
  and bills.dateAndTime = billedIn.dateAndTime
  and billedIn.treatmentName = treatments.name
  and billedIn.toothNumber = treatments.toothNumber
  and bills.status = 'unpaid';
# cost of each unpaid bill
select bills.pid, bills.dateAndTime, sum(treatments.cost)
from bills,
     billedIn,
     treatments
where bills.pid = billedIn.pid
  and bills.dateAndTime = billedIn.dateAndTime
  and billedIn.treatmentName = treatments.name
  and billedIn.toothNumber = treatments.toothNumber
  and bills.status = 'unpaid'
group by bills.pid, bills.dateAndTime;

select patients.fName       as PatientFirstName,
       patients.lName       as PatientLastName,
       bills.pid            as Pid,
       bills.dateAndTime    as dateAndTime,
       bills.status         as BillStatus,
       staffs.lName         as ReceptionistFirstName,
       staffs.fName         as ReceptionistLastName,
       appointments.cname   as ClinicName,
       appointments.address as ClinicAddress
from bills,
     patients,
     staffs,
     appointments
where bills.pid = patients.pid
  and appointments.pid = bills.pid
  and appointments.dateAndTime = bills.dateAndTime
  and bills.eidReceptionist = staffs.eid
  and bills.status = 'unpaid'
order by bills.dateAndTime, bills.pid;

select treatments.name, treatments.toothNumber, treatments.cost
from billedIn,
     treatments
where 8 = billedIn.pid
  and '2020-03-02 09:00:00' = billedIn.dateAndTime
  and billedIn.treatmentName = treatments.name
  and billedIn.toothNumber = treatments.toothNumber;

select sum(treatments.cost) as Total
from billedIn,
     treatments
where 8 = billedIn.pid
  and '2020-03-02 09:00:00' = billedIn.dateAndTime
  and billedIn.treatmentName = treatments.name
  and billedIn.toothNumber = treatments.toothNumber;
