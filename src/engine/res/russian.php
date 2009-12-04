<?php

/**
 * Localization
 *
 * This module contains prompts translated in Russian.
 * All the prompts are in KOI8-R encoding.
 *
 * @package Engine
 * @subpackage Localization
 * @author Artem Rodygin
 */

//--------------------------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system.
//  Copyright (C) 2004-2009 by Artem Rodygin
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
//
//--------------------------------------------------------------------------------------------------
//  Author                  Date            Description of modifications
//--------------------------------------------------------------------------------------------------
//  Artem Rodygin           2004-11-17      new-001: Records tracking web-based system should be implemented.
//  Artem Rodygin           2005-07-20      new-009: Records filter.
//  Artem Rodygin           2005-07-28      new-012: Records field 'description' should be renamed with 'subject'.
//  Artem Rodygin           2005-07-30      new-018: The 'History' menuitem is useless and should be removed.
//  Artem Rodygin           2005-07-30      new-006: Records search.
//  Artem Rodygin           2005-08-01      new-013: UI scenarios should be changed.
//  Artem Rodygin           2005-08-02      new-017: Email notifications filter.
//  Artem Rodygin           2005-08-06      new-019: Fields default values.
//  Artem Rodygin           2005-08-09      new-008: Predefined metrics.
//  Artem Rodygin           2005-08-13      new-020: Clone the records.
//  Artem Rodygin           2005-08-18      new-030: UI language should be set for each user separately.
//  Artem Rodygin           2005-08-18      bug-034: When record is being postponed, resumed or assigned the confirmations are not displayed.
//  Artem Rodygin           2005-08-18      new-035: Customizable list size.
//  Artem Rodygin           2005-08-18      new-037: Any template should be locked to be modified without suspending a project.
//  Artem Rodygin           2005-08-25      new-058: Global groups should be implemented.
//  Artem Rodygin           2005-08-29      new-068: System settings in 'config.php' should be accessable through web-interface.
//  Artem Rodygin           2005-09-04      bug-085: Members of global groups cannot view project records if they haven't any permissions in the project.
//  Artem Rodygin           2005-09-05      new-090: Add 'Select all' button to project permissions page.
//  Artem Rodygin           2005-09-06      new-094: Record creator should be displayed in general information of record.
//  Artem Rodygin           2005-09-07      new-100: 'Date' field type should be implemented.
//  Artem Rodygin           2005-09-08      new-101: 'Duration' field type should be implemented.
//  Artem Rodygin           2005-09-12      new-107: Number of displayed records should be present on the list of records.
//  Artem Rodygin           2005-09-13      new-116: Remove user login from the subject of email notifications.
//  Artem Rodygin           2005-09-15      new-122: User should be able to create a filter to display postponed records only.
//  Artem Rodygin           2005-09-17      new-125: Email notifications advanced filter.
//  Artem Rodygin           2005-10-05      new-148: Version info should be centralized.
//  Artem Rodygin           2005-10-05      bug-147: Misprint in russian email notifications - 'СостояИНе'.
//  Artem Rodygin           2005-10-15      new-153: Users should *always* receieve notifications about records which are created by them or assigned on.
//  Artem Rodygin           2005-10-27      new-169: Append 'add comment' URL to email notifications.
//  Artem Rodygin           2005-11-16      new-176: Change eTraxis design.
//  Artem Rodygin           2006-01-20      new-196: It's not clear that record is postponed when one is being viewed.
//  Artem Rodygin           2006-02-01      bug-208: 'Total records' prompt should be changed to 'Total'.
//  Artem Rodygin           2006-02-10      new-197: Postpone should have a timer for autoresume.
//  Artem Rodygin           2006-02-10      new-210: Hard to find out a prompt corresponding to specified resource ID.
//  Artem Rodygin           2006-03-16      new-175: Implement user roles in permissions.
//  Artem Rodygin           2006-05-12      bug-172: Extra long comments are cut when submitted.
//  Artem Rodygin           2006-05-16      new-005: Oracle support.
//  Artem Rodygin           2006-06-19      new-236: Single record subscription.
//  Artem Rodygin           2006-06-25      new-222: Email reminders.
//  Artem Rodygin           2006-06-28      new-272: When reminder is sent a notification should be displayed to user.
//  Artem Rodygin           2006-06-28      new-274: "Crumbs" for creation and modification of filters or subscriptions are not clear.
//  Artem Rodygin           2006-07-24      bug-201: 'Access Forbidden' error with cyrillic named attachments.
//  Artem Rodygin           2006-08-07      bug-300: Cannot login with Active Directory credentials.
//  Artem Rodygin           2006-08-13      new-305: Note with explanation of links to other records should be added where needed.
//  Artem Rodygin           2006-08-20      new-313: Implement HTTP authentication.
//  Artem Rodygin           2006-10-14      new-137: Custom queries.
//  Artem Rodygin           2006-10-17      new-361: Extended custom queries.
//  Artem Rodygin           2006-11-04      new-364: Default fields values.
//  Artem Rodygin           2006-11-05      new-365: Filters sharing.
//  Artem Rodygin           2006-11-06      new-372: KOI8-R should be used for Russian.
//  Artem Rodygin           2006-11-07      new-366: Export to CSV.
//  Artem Rodygin           2006-11-12      new-368: User should be able to subscribe other persons.
//  Artem Rodygin           2006-11-15      bug-381: Attachments of some types are not opened in valid applications.
//  Artem Rodygin           2006-11-15      new-374: Carbon copies in subscriptions.
//  Artem Rodygin           2006-11-18      bug-388: "Configuration" page does not display path where binary attachments are stored.
//  Artem Rodygin           2006-11-18      bug-389: Motorola LDAP server returns "Insufficient rights" error.
//  Artem Rodygin           2006-11-20      new-377: Custom views.
//  Artem Rodygin           2006-12-04      new-405: Default filter for new user.
//  Artem Rodygin           2006-12-10      new-432: Maintenance notice banner.
//  Artem Rodygin           2006-12-14      new-446: Add processing of new upload errors.
//  Artem Rodygin           2006-12-17      new-457: Default filter for new user.
//  Artem Rodygin           2006-12-20      new-459: 'Filters' and 'Subscriptions' pages should contain ability to clear current selection.
//  Artem Rodygin           2006-12-22      new-462: Postpone date should be displayed as separate field.
//  Artem Rodygin           2006-12-23      new-463: Date field names should be extended with date format explanation.
//  Artem Rodygin           2006-12-27      bug-470: State permissions must not be used when record is being created.
//  Artem Rodygin           2006-12-27      new-472: User must have ability to log out.
//  Artem Rodygin           2006-12-28      new-474: Rename field permissions to make them more clear.
//  Artem Rodygin           2006-12-30      new-475: Turning subscriptions on and off is not clear.
//  Artem Rodygin           2007-01-15      new-483: JavaScript ability notice.
//  Artem Rodygin           2007-04-03      new-512: Banner about 'no reply on autogenerated message' for notifications.
//  Artem Rodygin           2007-04-03      bug-522: Ambiguous Russian prompt 'чч.мм.гггг' (should be 'дд.мм.гггг').
//  Artem Rodygin           2007-04-03      new-499: Records dump to text file.
//  Artem Rodygin           2007-07-02      new-533: Links between records.
//  Artem Rodygin           2007-07-14      new-545: Chart legend is required.
//  Artem Rodygin           2007-07-16      new-546: Confidential comments.
//  Artem Rodygin           2007-08-06      new-551: Rework dependencies into "parent-child" relations.
//  Artem Rodygin           2007-08-27      [rhonda] Hide author from 'Innovation' templates.
//  Artem Rodygin           2007-09-09      new-563: Custom separators inside fields set.
//  Artem Rodygin           2007-09-12      new-576: [SF1788286] Export to CSV
//  Artem Rodygin           2007-09-13      new-566: Choose encoding for record dump and export of records list.
//  Artem Rodygin           2007-10-02      new-513: Apply current filter set to search results.
//  Artem Rodygin           2007-10-29      new-564: Filters set.
//  Artem Rodygin           2007-11-04      new-562: Ability to show only last values of any state.
//  Artem Rodygin           2007-11-05      new-571: View should show all records of current filters set.
//  Artem Rodygin           2007-11-11      bug-624: dbx_error(): Too many tables; MySQL can only use 61 tables in a join
//  Artem Rodygin           2007-11-13      new-599: Separated "Age" in custom views.
//  Artem Rodygin           2007-11-13      new-622: Rename 'children' into 'subrecords'.
//  Artem rodygin           2007-11-14      new-548: Custom links in text fields.
//  Artem Rodygin           2007-11-19      new-623: Default state in states list.
//  Yury Udovichenko        2007-11-20      new-536: Ability to hide postpone records from the list.
//  Artem Rodygin           2007-11-29      new-637: Subject of notifications should contain subject of records.
//  Artem Rodygin           2007-11-29      new-617: Add 'no view' and 'no filter set' to related comboboxes.
//  Artem Rodygin           2007-12-27      new-659: Set default language
//  Artem Rodygin           2007-12-30      bug-660: [SF1860788] PHP4 html_entity_decode() is not working
//  Artem Rodygin           2008-01-16      new-666: Buttons "Previous" & "Next" on record view page.
//  Artem Rodygin           2008-01-28      new-531: LDAP Guest users
//  Artem Rodygin           2008-01-31      new-601: [SF1814666] Export and Import Templates
//  Artem Rodygin           2008-02-27      new-676: [SF1898731] Delete Issues from Workflow
//  Artem Rodygin           2008-02-28      new-294: PostgreSQL support.
//  Artem Rodygin           2008-03-02      bug-681: Update configuration page with new options.
//  Artem Rodygin           2008-04-30      bug-699: Views // Names of custom columns are duplicated in the list of available columns, when there are two fields of different types with the same name.
//  Artem Rodygin           2008-05-01      new-715: Show creation time in the list of records.
//  Artem Rodygin           2008-07-02      new-729: [SF2008579] Mark all records as read
//  Artem Rodygin           2008-11-09      new-749: Guest access for unauthorized users.
//  Artem Rodygin           2009-01-08      new-774: 'Anyone' system role permissions.
//  Artem Rodygin           2009-04-12      bug-806: German translation causes two ambiguous "zuruck" buttons.
//  Artem Rodygin           2009-04-24      new-817: Field permissions dialog refactoring.
//  Artem Rodygin           2009-04-26      new-818: Change buttons layout on viewing record page.
//--------------------------------------------------------------------------------------------------

$resource_russian = array
(
    RES_SECTION_ALERTS =>
    /* 200 */
    'Все поля, помеченные как обязательные, должны быть заполнены.',
    'Значение по умолчанию должно лежать в диапазоне от %1 до %2.',
    'Учетная запись отключена.',
    'Учетная запись заблокирована.',
    'Неверное имя пользователя.',
    'Учетная запись с таким именем пользователя уже существует.',
    'Неверный адрес эл.почты.',
    'Пароли не совпадают.',
    'Пароль должен содержать как минимум %1 символов.',
    'Проект с таким названием уже существует.',
    /* 210 */
    'Группа с таким названием уже существует.',
    'Шаблон с таким названием или префиксом уже существует.',
    'Состояние с таким названием или сокращением уже существует.',
    'Атрибут с таким названием уже существует.',
    'Некорректное целое число.',
    'Целое число должно лежать в диапазоне от %1 до %2.',
    'Значение поля "%1" должно лежать в диапазоне от %2 до %3.',
    'Максимальное значение должно быть больше минимального.',
    'Размер загружаемого файла больше указанного в директиве "upload_max_filesize" файла "php.ini".',
    'Размер загружаемого файла не должен быть больше, чем %1 Кбайт.',
    /* 220 */
    'Файл был загружен только частично.',
    'Файл не был загружен.',
    'Не найдена временная директория.',
    'Приложение с таким именем уже существует.',
    'Запись не найдена.',
    'Фильтр с таким названием уже существует.',
    'Некорректное значение даты.',
    'Значение даты должно лежать в диапазоне от %1 до %2.',
    'Некорректное значение времени.',
    'Значение времени должно лежать в диапазоне от %1 до %2.',
    /* 230 */
    'Подписка с таким названием уже существует.',
    'Напоминание с таким названием уже существует.',
    'Напоминание успешно послано.',
    'Представление с таким названием уже существует.',
    'Столбец с таким названием уже существует.',
    'Ошибка записи файла.',
    'Загрузка файла прервана библиотекой.',
    'JavaScript должен быть включен.',
    'Это автоматически сгенерированное сообщение, пожалуйста, не используйте его для ответа.',
    'Указанная подзапись уже существует.',
    /* 240 */
    'Набор фильтров с таким названием уже существует.',
    'Представление не может иметь более %1 столбцов.',
    'Значение поля "%1" не соответствует заданному формату.',
    'Пользователь не авторизован.',
    'Неизвестный пользователь или неверный пароль.',
    'Неизвестный тип авторизации.',
    'Неизвестная ошибка.',
    'Ошибка XML-парсера.',
    'Ошибка соединения с базой данных.',

    RES_SECTION_CONFIRMS =>
    /* 300 */
    'Вы уверены, что хотите удалить все выделенные представления?',
    'Вы уверены, что хотите удалить эту учетную запись?',
    'Вы уверены, что хотите удалить этот проект?',
    'Вы уверены, что хотите удалить эту группу?',
    'Вы уверены, что хотите удалить этот шаблон?',
    'Вы уверены, что хотите удалить это состояние?',
    'Вы уверены, что хотите удалить этот атрибут?',
    'Вы уверены, что хотите отложить эту запись?',
    'Вы уверены, что хотите возобновить эту запись?',
    'Вы уверены, что хотите назначить эту запись?',
    /* 310 */
    'Вы уверены, что хотите удалить все выделенные фильтры?',
    'Вы уверены, что хотите удалить все выделенные подписки?',
    'Вы уверены, что хотите послать это напоминание?',
    'Вы уверены, что хотите удалить это напоминание?',
    'Вы уверены, что хотите удалить этот столбец?',
    'Вы уверены, что хотите выйти?',
    'Вы уверены, что хотите удалить все выделенные наборы фильтров?',
    'Вы уверены, что хотите удалить эту запись?',

    RES_SECTION_PROMPTS =>
    /* 1000 */
    'Русский',
    'Вход',
    '  ОК  ',
    'Отмена',
    'Сохранить',
    'Назад',
    'Дальше',
    'Создать',
    'Изменить',
    'Удалить',
    /* 1010 */
    'Записи',
    'Пользователи',
    'Проекты',
    'Поменять пароль',
    'Атрибуты состояния "%1"',
    'нет',
    'Всего:',
    'запомнить пароль',
    'Информация о пользователе',
    'Имя пользователя',
    /* 1020 */
    'Полное имя',
    'Эл.почта',
    'Права',
    'администратор',
    'пользователь',
    'Описание',
    'Пароль',
    'Подтверждение',
    'отключен',
    'заблокирован',
    /* 1030 */
    'Новый пользователь',
    'Учетная запись "%1"',
    'Информация о проекте',
    'Название проекта',
    'Начало проекта',
    '"заморожен"',
    'Новый проект',
    'Проект "%1"',
    'Группы',
    'Информация о группе',
    /* 1040 */
    'Название группы',
    'Новая группа',
    'Группа "%1"',
    'Членство',
    'Прочие',
    'Члены',
    'Шаблоны',
    'Информация о шаблоне',
    'Название шаблона',
    'Префикс',
    /* 1050 */
    'Новый шаблон',
    'Шаблон "%1"',
    'Состояния',
    'Информация о состоянии',
    'Название состояния',
    'Сокращение',
    'Тип состояния',
    'начальное',
    'промежуточное',
    'финальное',
    /* 1060 */
    'Ответственный',
    'оставить без изменений',
    'назначить',
    'убрать',
    'Новое состояние',
    'Состояние "%1"',
    'Создать промежуточное',
    'Создать финальное',
    'Переходы',
    'Права',
    /* 1070 */
    'Сделать начальным',
    'Разрешенные',
    'Атрибуты',
    'Информация об атрибуте',
    'Очередность',
    'Название атрибута',
    'Тип атрибута',
    'число',
    'строка',
    'многострочный текст',
    /* 1080 */
    'Обязательный',
    'да',
    'нет',
    'Мин.значение',
    'Макс.значение',
    'Макс.длина',
    'обязательный',
    'Новый атрибут (шаг %1/%2)',
    'Атрибут "%1"',
    'только чтение',
    /* 1090 */
    'чтение и запись',
    'Общая информация',
    'ID',
    'Проект',
    'Шаблон',
    'Состояние',
    'Возраст',
    'Новая запись (шаг %1/%2)',
    'Запись "%1"',
    'Перейти',
    /* 1100 */
    'История',
    'Отложить',
    'Возобновить',
    'Назначить',
    'Сменить состояние',
    'Время',
    'Инициатор',
    'Запись создана в состоянии "%1".',
    'Запись назначена на %1.',
    'Запись изменена.',
    /* 1110 */
    'Состояние изменено на "%1".',
    'Запись отложена до %1.',
    'Запись возобновлена.',
    'Файл "%1" прикреплен.',
    'Файл "%1" удален.',
    'право создавать записи',
    'право изменять записи',
    'право откладывать записи',
    'право возобновлять записи',
    'право переназначать назначенные записи',
    /* 1120 */
    'право изменять состояние назначенных записей',
    'право прикреплять файлы',
    'право удалять файлы',
    'Язык',
    'Добавить комментарий',
    'Добавлен комментарий.',
    'право добавлять комментарии',
    'Комментарий',
    'Прикрепить файл',
    'Удалить файл',
    /* 1130 */
    'Приложение',
    'Имя приложения',
    'Файл приложения',
    'Приложения',
    'Нет атрибутов.',
    'Критичный возраст',
    'Время "заморозки"',
    'Изменения',
    'Старое значение',
    'Новое значение',
    /* 1140 */
    '"флаг"',
    'запись',
    'список',
    'Элементы списка',
    '%1 Кб',
    'Фильтры',
    'Название фильтра',
    'Все проекты',
    'Все шаблоны',
    'Все состояния',
    /* 1150 */
    'Просмотр записи',
    'Показывать только созданные ...',
    'Показывать только назначенные на ...',
    'показывать только незакрытые',
    'Тема',
    'Поиск',
    'Параметры поиска',
    'Результаты поиска (отфильтрованные)',
    'Искомый текст',
    'искать в атрибутах',
    /* 1160 */
    'искать в комментариях',
    'Статус',
    'активный',
    'Подписка',
    'уведомлять когда создается запись',
    'уведомлять когда назначается запись',
    'уведомлять когда изменяется запись',
    'уведомлять когда изменяется состояние записи',
    'уведомлять когда откладывается запись',
    'уведомлять когда возобновляется запись',
    /* 1170 */
    'уведомлять когда добавляется комментарий',
    'уведомлять когда прикрепляется файл',
    'уведомлять когда удаляется файл',
    'обязательное',
    'Отложена',
    'До даты',
    'Значение по умолчанию',
    'вкл.',
    'выкл.',
    'Метрики',
    /* 1180 */
    'Открытые записи',
    'Создание и закрытие',
    'неделя',
    'количество',
    'Клонировать',
    'Запись склонирована из "%1".',
    'Выход',
    'уведомлять когда клонируется запись',
    'Настройки',
    'Записей на страницу',
    /* 1190 */
    'Закладок на страницу',
    'Заблокировать',
    'Разблокировать',
    'Тип группы',
    'глобальная',
    'локальная',
    'Конфигурация',
    'Локальный корневой путь',
    'Корневой URL',
    'Безопасность',
    /* 1200 */
    'Минимальная длина пароля',
    'Максимальное кол-во попыток входа',
    'Время блокировки (мин.)',
    'База данных',
    'Тип базы данных',
    'Oracle',
    'MySQL',
    'Microsoft SQL Server',
    'Имя сервера',
    'Имя базы данных',
    /* 1210 */
    'Пользователь базы данных',
    'Active Directory',
    'Имя LDAP-сервера',
    'Номер порта',
    'Поисковая учетная запись',
    'Базовая директория',
    'Администраторы',
    'Email-уведомления',
    'Максимальный размер',
    'Отладка',
    /* 1220 */
    'Режим отладки',
    'включен (без приватных данных)',
    'включен (все данные)',
    'Журналы отладки',
    'Включено',
    'Отключено',
    '%1 мин.',
    'право только просматривать записи',
    'Выбрать все',
    'Автор',
    /* 1230 */
    'дата',
    'продолжительность',
    'показывать только отложенные',
    'Название подписки',
    'События',
    'Версия %1',
    'роль',
    'Подписаться',
    'Отписаться',
    'Напоминания',
    /* 1240 */
    'Название напоминания',
    'Тема напоминания',
    'Получатели напоминания',
    'Новое напоминание (шаг %1/%2)',
    'Напоминание "%1"',
    'право посылать напоминания',
    'Послать',
    'Новый фильтр',
    'Фильтр "%1"',
    'Новая подписка',
    /* 1250 */
    'Подписка "%1"',
    'Ваш LDAP-пароль',
    'Вы можете вставить ссылку на другую запись, указав "rec#" и ее номер (например "rec#305").',
    'Показывать только побывавшие в состояниях ...',
    'Сделать доступным для ...',
    'Экспорт',
    'Подписать других...',
    'Подписанные',
    '%1 подписал Вас на запись.',
    '%1 отписался.',
    /* 1260 */
    'Копия',
    'Накопитель',
    'LDAP-атрибут',
    'Представления',
    'Информация о представлении',
    'Название представления',
    'Новое представление (шаг %1/%2)',
    'Представление "%1"',
    'Без представления',
    'Установить',
    /* 1270 */
    'Столбцы',
    'Информация о столбце',
    'Заголовок столбца',
    'Новый столбец',
    'Столбец "%1"',
    'Выравнивание',
    'влево',
    'по центру',
    'вправо',
    'Сервис будет недоступен с %1 до %2 (%3)',
    /* 1280 */
    'Все назначенные на меня',
    'Все созданные мной',
    'Убрать выделение',
    'дд.мм.гггг',
    'Сброс в файл',
    'Подзаписи',
    'Создать подзапись',
    'Добавить подзапись',
    'Убрать подзапись',
    'ID подзаписи',
    /* 1290 */
    'Добавлна подзапись "%1".',
    'Удалена подзапись "%1".',
    'право добавлять подзаписи',
    'право удалять подзаписи',
    'уведомлять когда добавляется подзапись',
    'уведомлять когда удаляется подзапись',
    'созданные записи',
    'закрытые записи',
    'Конфиденциальный',
    'Добавить конфиденциальный комментарий',
    /* 1300 */
    'право добавлять/читать конфиденциальные комментарии',
    'Добавлен конфиденциальный комментарий.',
    'ID родителя',
    'зависимость',
    'скрыт',
    'Добавить разделитель',
    'CSV-разделитель',
    'Кодировка CSV',
    'Концы строк CSV',
    'Результаты поиска (нефильтрованные)',
    /* 1310 */
    'Включить фильтры',
    'Выключить фильтры',
    'Текущий набор фильтров',
    'Сохранить набор фильтров',
    'Наборы фильтров',
    'Название набора',
    'Набор фильтров "%1"',
    'Текущее представление',
    'Сохранить представление',
    'Развернуть все',
    /* 1320 */
    'Свернуть все',
    'Восстановить по умолчанию',
    'П/С',
    'PCRE для проверки значений',
    'PCRE-поиск для модификации значений',
    'PCRE-замена для модификации значений',
    'Следующее состояние по умолчанию',
    'Состояние отложенности',
    'показывать все',
    'показывать только активные',
    /* 1330 */
    'Событие',
    'Без набора фильтров',
    'Гостевой доступ',
    'Нет.',
    '',
    'Гость',
    'Импорт',
    'право удалять записи',
    'Тип авторизации',
    'Язык по умолчанию',
    /* 1340 */
    'Истечение срока пароля (дн.)',
    'Истечение срока сессии (мин.)',
    'LDAP-перечисление',
    'PostgreSQL',
    'список индексов',
    'список значений',
    'Создан',
    'Отметить как прочитанные',
    'Зарегистрированный',
);

?>
