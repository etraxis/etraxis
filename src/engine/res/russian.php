<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2004-2010  Artem Rodygin
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//------------------------------------------------------------------------------

/**
 * Localization
 *
 * This module contains prompts translated in Russian.
 *
 * @package Engine
 * @subpackage Localization
 * @author Artem Rodygin
 */

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
    'русский',
    'Вход',
    'ОК',
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
    'Дизайн',
    'Информация о пользователе',
    'Имя пользователя',
    /* 1020 */
    'Полное имя',
    'Эл.почта',
    'по умолчанию',
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
    'Допущенные',
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
    'Мои записи',
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
    'Результаты поиска',
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
    'П/соб',
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
    'Глобальные группы',
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
    'TLS',
    /* 1350 */
    'Сжатие',
    'П/сост',
    'Комментарии',
    'Размер',
    'Внешний вид',
    'CSV',
    'Активировать',
    'Деактивировать',
    'Предварительный просмотр',
    'Владелец',
    /* 1360 */
    'Кто угодно.',
    'Отметить как непрочитанные',
    'Родительские записи',
);

?>
