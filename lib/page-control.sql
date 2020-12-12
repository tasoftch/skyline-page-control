create table if not exists SKY_PC_CONDITION
(
    id         int auto_increment
        primary key,
    date_from  datetime      null,
    date_until datetime      null,
    ip1        varchar(15)   null,
    ip2        varchar(15)   null,
    ip3        varchar(15)   null,
    ip4        varchar(15)   null,
    options    int default 0 null
);

create table if not exists SKY_PC_DAY_NAME
(
    id     int auto_increment
        primary key,
    name   varchar(20) not null,
    short  varchar(10) not null,
    sys_id int         not null
);

INSERT INTO SKY_PC_DAY_NAME (id, name, short, sys_id) VALUES (1, 'Montag', 'Mo', 0);
INSERT INTO SKY_PC_DAY_NAME (id, name, short, sys_id) VALUES (2, 'Dienstag', 'Di', 1);
INSERT INTO SKY_PC_DAY_NAME (id, name, short, sys_id) VALUES (3, 'Mittwoch', 'Mi', 2);
INSERT INTO SKY_PC_DAY_NAME (id, name, short, sys_id) VALUES (4, 'Donnerstag', 'Do', 3);
INSERT INTO SKY_PC_DAY_NAME (id, name, short, sys_id) VALUES (5, 'Freitag', 'Fr', 4);
INSERT INTO SKY_PC_DAY_NAME (id, name, short, sys_id) VALUES (6, 'Samstag', 'Sa', 5);
INSERT INTO SKY_PC_DAY_NAME (id, name, short, sys_id) VALUES (7, 'Sonntag', 'So', 6);

create table if not exists SKY_PC_ITERATION
(
    id               int auto_increment
        primary key,
    name             varchar(100)  not null,
    description      text          null,
    options          int default 0 not null,
    visibility_group int default 1 not null
);

create table if not exists SKY_PC_ITERATION_CELL
(
    id         int auto_increment
        primary key,
    header     int           not null,
    value      text          null,
    row        int           not null,
    visibility int default 1 not null
);

create table if not exists SKY_PC_ITERATION_HEADER
(
    id                 int auto_increment
        primary key,
    name               varchar(100)  not null,
    description        text          null,
    col                int default 1 null,
    hasNull            int default 0 not null,
    value_type         int           not null,
    controlPlaceholder varchar(100)  null
);

create table if not exists SKY_PC_ITERATION_HEADER_Q
(
    iteration int not null,
    header    int not null
);

create table if not exists SKY_PC_ITERATION_Q
(
    iteration int not null,
    page      int not null
);

create table if not exists SKY_PC_MONTH_NAME
(
    id     int auto_increment
        primary key,
    name   varchar(20) not null,
    short  varchar(10) not null,
    sys_id int         not null
);

INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (1, 'Januar', 'Jan', 1);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (2, 'Februar', 'Feb', 2);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (3, 'März', 'Mär', 3);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (4, 'April', 'Apr', 4);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (5, 'Mai', 'Mai', 5);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (6, 'Juni', 'Jun', 6);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (7, 'Juli', 'Jul', 7);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (8, 'August', 'Aug', 8);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (9, 'September', 'Sep', 9);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (10, 'Oktober', 'Okt', 10);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (11, 'November', 'Nov', 11);
INSERT INTO SKY_PC_MONTH_NAME (id, name, short, sys_id) VALUES (12, 'Dezember', 'Dez', 12);

create table if not exists SKY_PC_OPTION_LIST
(
    id          int auto_increment
        primary key,
    name        varchar(100) not null,
    description text         null
);

INSERT INTO SKY_PC_OPTION_LIST (id, name, description) VALUES (1, 'Wahrheitswerte', 'Liste mit Wahr oder Falsch');
INSERT INTO SKY_PC_OPTION_LIST (id, name, description) VALUES (2, 'Tage', 'Liste mit den Tagen');
INSERT INTO SKY_PC_OPTION_LIST (id, name, description) VALUES (3, 'Monate', 'Liste mit den Monaten');

create table if not exists SKY_PC_OPTION_LIST_ITEM
(
    id          int auto_increment
        primary key,
    option_list int          not null,
    sys_id      varchar(100) not null,
    label       varchar(100) not null,
    description text         null
);

INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (1, 1, '1', 'WAHR', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (2, 1, '0', 'FALSCH', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (3, 2, '1', 'Montag', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (4, 2, '2', 'Dienstag', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (5, 2, '3', 'Mittwoch', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (6, 2, '4', 'Donnerstag', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (7, 2, '5', 'Freitag', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (8, 2, '6', 'Samstag', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (9, 2, '7', 'Sonntag', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (10, 3, '1', 'Januar', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (11, 3, '2', 'Februar', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (12, 3, '3', 'März', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (13, 3, '4', 'April', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (14, 3, '5', 'Mai', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (15, 3, '6', 'Juni', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (16, 3, '7', 'Juli', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (17, 3, '8', 'August', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (18, 3, '9', 'September', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (19, 3, '10', 'Oktober', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (20, 3, '11', 'November', null);
INSERT INTO SKY_PC_OPTION_LIST_ITEM (id, option_list, sys_id, label, description) VALUES (21, 3, '12', 'Dezember', null);

create table if not exists SKY_PC_PAGE
(
    id               int auto_increment
        primary key,
    name             varchar(100)                not null,
    title            varchar(100)                null,
    description      text                        null,
    main_template    varchar(100) default 'main' null,
    content_template varchar(100)                not null,
    constraint SKY_PC_PAGE_name_uindex
        unique (name)
);

create table if not exists SKY_PC_PLACEHOLDER
(
    id          int auto_increment
        primary key,
    name        varchar(100)  not null,
    label       varchar(100)  null,
    description text          null,
    options     int default 0 not null,
    value       text          null,
    placeholder varchar(100)  null,
    valueType   int           not null,
    d_condition int           null
);

create table if not exists SKY_PC_PLACEHOLDER_Q
(
    placeholder int not null,
    page        int not null
);

create table if not exists SKY_PC_VALUE_TYPE
(
    id                int auto_increment
        primary key,
    name              varchar(100)  not null,
    description       text          null,
    options           int default 0 not null,
    valueClass        varchar(100)  not null,
    controlClass      varchar(100)  not null,
    controlOptionList int           null
);

INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (1, 'Zeichenkette', 'Aneinanderreihung von 0 bis 100 Zeichen', 0, 'StringVal', 'Skyline\\HTML\\Form\\Control\\Text\\TextFieldControl', null);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (2, 'Zahl', 'Zahlenwerte, mit oder ohne Komma', 0, 'Number', 'Skyline\\HTML\\Form\\Control\\Text\\TextFieldControl', null);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (3, 'Wahrheitswert', 'Wahr oder Falsch, 1 oder 0', 0, 'Boolean', 'Skyline\\HTML\\Form\\Control\\Option\\PopUpControl', 1);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (4, 'Datum', 'Datumwert', 0, 'Date', 'Skyline\\HTML\\Form\\Control\\Text\\TextFieldControl', null);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (5, 'Zeit', 'Zeitangabe relativ', 1, 'Time', 'Skyline\\HTML\\Form\\Control\\Text\\TextFieldControl', null);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (6, 'Datumzeit', 'Datum mit Zeitangabe', 1, 'DateTime', 'Skyline\\HTML\\Form\\Control\\Text\\TextFieldControl', null);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (7, 'Wochentag', 'Wochentag', 3, 'Weekday', 'Skyline\\HTML\\Form\\Control\\Option\\PopUpControl', 2);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (9, 'Monat', 'Monatname', 0, 'Month', 'Skyline\\HTML\\Form\\Control\\Option\\PopUpControl', 3);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (10, 'Email', 'Als Emailadresse formatierte Zeichenkette', 0, 'Email', 'Skyline\\HTML\\Form\\Control\\Text\\TextFieldControl', null);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (11, 'Text', 'Aneinanderreihung von 0 oder mehr Zeichen', 0, 'Text', 'Skyline\\HTML\\Form\\Control\\Text\\TextAreaControl', null);
INSERT INTO SKY_PC_VALUE_TYPE (id, name, description, options, valueClass, controlClass, controlOptionList) VALUES (12, 'HTML', 'Hyper Text Markup Language Formulierung, die direkt ausgegeben wird.', 0, 'HTML', 'Skyline\\HTML\\Form\\Control\\Text\\HTMLEditorControl', null);