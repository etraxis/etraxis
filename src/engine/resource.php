<?php

//------------------------------------------------------------------------------
//
//  eTraxis - Records tracking web-based system
//  Copyright (C) 2004-2011  Artem Rodygin
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
 * This module contains IDs of all UI prompts.
 *
 * @package Engine
 * @subpackage Localization
 */

//------------------------------------------------------------------------------
//  Alerts.
//------------------------------------------------------------------------------

/**
 * Begin of section with alert prompts.
 */
define('RES_SECTION_ALERTS',                        200);

/**#@+
 * Alert prompt ID.
 */
define('RES_ALERT_REQUIRED_ARE_EMPTY_ID',           200);
define('RES_ALERT_DEFAULT_VALUE_OUT_OF_RANGE_ID',   201);
define('RES_ALERT_ACCOUNT_DISABLED_ID',             202);
define('RES_ALERT_ACCOUNT_LOCKED_ID',               203);
define('RES_ALERT_INVALID_USERNAME_ID',             204);
define('RES_ALERT_ACCOUNT_ALREADY_EXISTS_ID',       205);
define('RES_ALERT_INVALID_EMAIL_ID',                206);
define('RES_ALERT_PASSWORDS_DO_NOT_MATCH_ID',       207);
define('RES_ALERT_PASSWORD_TOO_SHORT_ID',           208);
define('RES_ALERT_PROJECT_ALREADY_EXISTS_ID',       209);
define('RES_ALERT_GROUP_ALREADY_EXISTS_ID',         210);
define('RES_ALERT_TEMPLATE_ALREADY_EXISTS_ID',      211);
define('RES_ALERT_STATE_ALREADY_EXISTS_ID',         212);
define('RES_ALERT_FIELD_ALREADY_EXISTS_ID',         213);
define('RES_ALERT_INVALID_INTEGER_VALUE_ID',        214);
define('RES_ALERT_INTEGER_VALUE_OUT_OF_RANGE_ID',   215);
define('RES_ALERT_FIELD_VALUE_OUT_OF_RANGE_ID',     216);
define('RES_ALERT_MIN_MAX_VALUES_ID',               217);
define('RES_ALERT_UPLOAD_INI_SIZE_ID',              218);
define('RES_ALERT_UPLOAD_FORM_SIZE_ID',             219);
define('RES_ALERT_UPLOAD_PARTIAL_ID',               220);
define('RES_ALERT_UPLOAD_NO_FILE_ID',               221);
define('RES_ALERT_UPLOAD_NO_TMP_DIR_ID',            222);
define('RES_ALERT_ATTACHMENT_ALREADY_EXISTS_ID',    223);
define('RES_ALERT_RECORD_NOT_FOUND_ID',             224);
define('RES_ALERT_FILTER_ALREADY_EXISTS_ID',        225);
define('RES_ALERT_INVALID_DATE_VALUE_ID',           226);
define('RES_ALERT_DATE_VALUE_OUT_OF_RANGE_ID',      227);
define('RES_ALERT_INVALID_TIME_VALUE_ID',           228);
define('RES_ALERT_TIME_VALUE_OUT_OF_RANGE_ID',      229);
define('RES_ALERT_SUBSCRIPTION_ALREADY_EXISTS_ID',  230);
define('RES_ALERT_REMINDER_ALREADY_EXISTS_ID',      231);
define('RES_ALERT_REMINDER_IS_SENT_ID',             232);
define('RES_ALERT_VIEW_ALREADY_EXISTS_ID',          233);
define('RES_ALERT_SPECIFY_SHORT_DESCRIPTION_ID',    234);
define('RES_ALERT_UPLOAD_CANT_WRITE_ID',            235);
define('RES_ALERT_UPLOAD_EXTENSION_ID',             236);
define('RES_ALERT_JAVASCRIPT_MUST_BE_ENABLED_ID',   237);
define('RES_ALERT_DO_NOT_REPLY_ID',                 238);
define('RES_ALERT_INVALID_DECIMAL_VALUE_ID',        239);
define('RES_ALERT_DECIMAL_VALUE_OUT_OF_RANGE_ID',   240);
define('RES_ALERT_VIEW_CANNOT_HAVE_MORE_COLUMNS',   241);
define('RES_ALERT_VALUE_FAILS_REGEX_CHECK_ID',      242);
define('RES_ALERT_USER_NOT_AUTHORIZED_ID',          243);
define('RES_ALERT_UNKNOWN_USERNAME_ID',             244);
define('RES_ALERT_UNKNOWN_ERROR_ID',                245);
define('RES_ALERT_XML_PARSER_ERROR_ID',             246);
define('RES_ALERT_SUCCESSFULLY_SAVED_ID',           247);

//------------------------------------------------------------------------------
//  Confirmations.
//------------------------------------------------------------------------------

/**
 * Begin of section with confirmation prompts.
 */
define('RES_SECTION_CONFIRMS',                      300);

/**#@+
 * Confirmation prompt ID.
 */
define('RES_CONFIRM_DELETE_VIEWS_ID',               300);
define('RES_CONFIRM_DELETE_ACCOUNT_ID',             301);
define('RES_CONFIRM_DELETE_PROJECT_ID',             302);
define('RES_CONFIRM_DELETE_GROUP_ID',               303);
define('RES_CONFIRM_DELETE_TEMPLATE_ID',            304);
define('RES_CONFIRM_DELETE_STATE_ID',               305);
define('RES_CONFIRM_DELETE_FIELD_ID',               306);
define('RES_CONFIRM_CHANGE_STATE_ID',               307);
define('RES_CONFIRM_RESUME_RECORD_ID',              308);
define('RES_CONFIRM_ASSIGN_RECORD_ID',              309);
define('RES_CONFIRM_DELETE_FILTERS_ID',             310);
define('RES_CONFIRM_DELETE_SUBSCRIPTIONS_ID',       311);
define('RES_CONFIRM_SEND_REMINDER_ID',              312);
define('RES_CONFIRM_DELETE_REMINDER_ID',            313);
define('RES_CONFIRM_LOGOUT_ID',                     314);
define('RES_CONFIRM_DELETE_RECORD_ID',              315);
/**#@-*/

//------------------------------------------------------------------------------
//  Other prompts.
//------------------------------------------------------------------------------

/**
 * Begin of section with general prompts.
 */
define('RES_SECTION_PROMPTS',                       1000);

/**#@+
 * General prompt ID.
 */
define('RES_LOCALE_ID',                             1000);
define('RES_LOGIN_ID',                              1001);
define('RES_OK_ID',                                 1002);
define('RES_CANCEL_ID',                             1003);
define('RES_SAVE_ID',                               1004);
define('RES_BACK_ID',                               1005);
define('RES_NEXT_ID',                               1006);
define('RES_CREATE_ID',                             1007);
define('RES_MODIFY_ID',                             1008);
define('RES_DELETE_ID',                             1009);
define('RES_RECORDS_ID',                            1010);
define('RES_ACCOUNTS_ID',                           1011);
define('RES_PROJECTS_ID',                           1012);
define('RES_CHANGE_PASSWORD_ID',                    1013);
define('RES_FIELDS_OF_STATE_X_ID',                  1014);
define('RES_NONE_ID',                               1015);
define('RES_TOTAL_ID',                              1016);
define('RES_THEME_ID',                              1017);
define('RES_ACCOUNT_INFO_ID',                       1018);
define('RES_USERNAME_ID',                           1019);
define('RES_FULLNAME_ID',                           1020);
define('RES_EMAIL_ID',                              1021);
define('RES_DEFAULT_ID',                            1022);
define('RES_ADMINISTRATOR_ID',                      1023);
define('RES_USER_ID',                               1024);
define('RES_DESCRIPTION_ID',                        1025);
define('RES_PASSWORD_ID',                           1026);
define('RES_PASSWORD_CONFIRM_ID',                   1027);
define('RES_DISABLED_ID',                           1028);
define('RES_LOCKED_ID',                             1029);
define('RES_NEW_ACCOUNT_ID',                        1030);
define('RES_ACCOUNT_X_ID',                          1031);
define('RES_PROJECT_INFO_ID',                       1032);
define('RES_PROJECT_NAME_ID',                       1033);
define('RES_START_TIME_ID',                         1034);
define('RES_SUSPENDED_ID',                          1035);
define('RES_NEW_PROJECT_ID',                        1036);
define('RES_PROJECT_X_ID',                          1037);
define('RES_GROUPS_ID',                             1038);
define('RES_GROUP_INFO_ID',                         1039);
define('RES_GROUP_NAME_ID',                         1040);
define('RES_NEW_GROUP_ID',                          1041);
define('RES_GROUP_X_ID',                            1042);
define('RES_MEMBERSHIP_ID',                         1043);
define('RES_OTHERS_ID',                             1044);
define('RES_MEMBERS_ID',                            1045);
define('RES_TEMPLATES_ID',                          1046);
define('RES_TEMPLATE_INFO_ID',                      1047);
define('RES_TEMPLATE_NAME_ID',                      1048);
define('RES_TEMPLATE_PREFIX_ID',                    1049);
define('RES_NEW_TEMPLATE_ID',                       1050);
define('RES_TEMPLATE_X_ID',                         1051);
define('RES_STATES_ID',                             1052);
define('RES_STATE_INFO_ID',                         1053);
define('RES_STATE_NAME_ID',                         1054);
define('RES_STATE_ABBR_ID',                         1055);
define('RES_STATE_TYPE_ID',                         1056);
define('RES_INITIAL_ID',                            1057);
define('RES_INTERMEDIATE_ID',                       1058);
define('RES_FINAL_ID',                              1059);
define('RES_RESPONSIBLE_ID',                        1060);
define('RES_REMAIN_ID',                             1061);
define('RES_ASSIGN_ID',                             1062);
define('RES_REMOVE_ID',                             1063);
define('RES_NEW_STATE_ID',                          1064);
define('RES_STATE_X_ID',                            1065);
define('RES_CREATE_INTERMEDIATE_ID',                1066);
define('RES_CREATE_FINAL_ID',                       1067);
define('RES_TRANSITIONS_ID',                        1068);
define('RES_PERMISSIONS_ID',                        1069);
define('RES_SET_INITIAL_ID',                        1070);
define('RES_ALLOWED_ID',                            1071);
define('RES_FIELDS_ID',                             1072);
define('RES_FIELD_INFO_ID',                         1073);
define('RES_ORDER_ID',                              1074);
define('RES_FIELD_NAME_ID',                         1075);
define('RES_FIELD_TYPE_ID',                         1076);
define('RES_NUMBER_ID',                             1077);
define('RES_STRING_ID',                             1078);
define('RES_MULTILINED_TEXT_ID',                    1079);
define('RES_REQUIRED_ID',                           1080);
define('RES_YES_ID',                                1081);
define('RES_NO_ID',                                 1082);
define('RES_MIN_VALUE_ID',                          1083);
define('RES_MAX_VALUE_ID',                          1084);
define('RES_MAX_LENGTH_ID',                         1085);
define('RES_REQUIRED2_ID',                          1086);
define('RES_NEW_FIELD_ID',                          1087);
define('RES_FIELD_X_ID',                            1088);
define('RES_READ_ONLY_ID',                          1089);
define('RES_READ_AND_WRITE_ID',                     1090);
define('RES_GENERAL_INFO_ID',                       1091);
define('RES_ID_ID',                                 1092);
define('RES_PROJECT_ID',                            1093);
define('RES_TEMPLATE_ID',                           1094);
define('RES_STATE_ID',                              1095);
define('RES_AGE_ID',                                1096);
define('RES_NEW_RECORD_ID',                         1097);
define('RES_RECORD_X_ID',                           1098);
define('RES_MY_RECORDS_ID',                         1099);
define('RES_HISTORY_ID',                            1100);
define('RES_POSTPONE_ID',                           1101);
define('RES_RESUME_ID',                             1102);
define('RES_ASSIGN2_ID',                            1103);
define('RES_CHANGE_STATE_ID',                       1104);
define('RES_TIMESTAMP_ID',                          1105);
define('RES_ORIGINATOR_ID',                         1106);
define('RES_EVENT_RECORD_CREATED_ID',               1107);
define('RES_EVENT_RECORD_ASSIGNED_ID',              1108);
define('RES_EVENT_RECORD_MODIFIED_ID',              1109);
define('RES_EVENT_RECORD_STATE_CHANGED_ID',         1110);
define('RES_EVENT_RECORD_POSTPONED_ID',             1111);
define('RES_EVENT_RECORD_RESUMED_ID',               1112);
define('RES_EVENT_FILE_ATTACHED_ID',                1113);
define('RES_EVENT_FILE_REMOVED_ID',                 1114);
define('RES_PERMIT_CREATE_RECORD_ID',               1115);
define('RES_PERMIT_MODIFY_RECORD_ID',               1116);
define('RES_PERMIT_POSTPONE_RECORD_ID',             1117);
define('RES_PERMIT_RESUME_RECORD_ID',               1118);
define('RES_PERMIT_REASSIGN_RECORD_ID',             1119);
define('RES_UNUSED_1120_ID',                        1120);
define('RES_PERMIT_ATTACH_FILES_ID',                1121);
define('RES_PERMIT_REMOVE_FILES_ID',                1122);
define('RES_LANGUAGE_ID',                           1123);
define('RES_ADD_COMMENT_ID',                        1124);
define('RES_EVENT_COMMENT_ADDED_ID',                1125);
define('RES_PERMIT_ADD_COMMENTS_ID',                1126);
define('RES_COMMENT_ID',                            1127);
define('RES_ATTACH_FILE_ID',                        1128);
define('RES_REMOVE_FILE_ID',                        1129);
define('RES_ATTACHMENT_ID',                         1130);
define('RES_ATTACHMENT_NAME_ID',                    1131);
define('RES_ATTACHMENT_FILE_ID',                    1132);
define('RES_ATTACHMENTS_ID',                        1133);
define('RES_NO_FIELDS_ID',                          1134);
define('RES_CRITICAL_AGE_ID',                       1135);
define('RES_FROZEN_TIME_ID',                        1136);
define('RES_CHANGES_ID',                            1137);
define('RES_OLD_VALUE_ID',                          1138);
define('RES_NEW_VALUE_ID',                          1139);
define('RES_CHECKBOX_ID',                           1140);
define('RES_RECORD_ID',                             1141);
define('RES_LIST_ID',                               1142);
define('RES_LIST_ITEMS_ID',                         1143);
define('RES_KB_ID',                                 1144);
define('RES_FILTERS_ID',                            1145);
define('RES_FILTER_NAME_ID',                        1146);
define('RES_ALL_PROJECTS_ID',                       1147);
define('RES_ALL_TEMPLATES_ID',                      1148);
define('RES_ALL_STATES_ID',                         1149);
define('RES_VIEW_RECORD_ID',                        1150);
define('RES_SHOW_CREATED_BY_ONLY_ID',               1151);
define('RES_SHOW_ASSIGNED_TO_ONLY_ID',              1152);
define('RES_SHOW_UNCLOSED_ONLY_ID',                 1153);
define('RES_SUBJECT_ID',                            1154);
define('RES_SEARCH_ID',                             1155);
define('RES_SEARCH_PARAMETERS_ID',                  1156);  // reserved
define('RES_SEARCH_RESULTS_ID',                     1157);
define('RES_TEXT_TO_BE_SEARCHED_ID',                1158);  // reserved
define('RES_SEARCH_IN_FIELDS_ID',                   1159);  // reserved
define('RES_SEARCH_IN_COMMENTS_ID',                 1160);  // reserved
define('RES_STATUS_ID',                             1161);
define('RES_ACTIVE_ID',                             1162);
define('RES_SUBSCRIPTIONS_ID',                      1163);
define('RES_NOTIFY_RECORD_CREATED_ID',              1164);
define('RES_NOTIFY_RECORD_ASSIGNED_ID',             1165);
define('RES_NOTIFY_RECORD_MODIFIED_ID',             1166);
define('RES_NOTIFY_RECORD_STATE_CHANGED_ID',        1167);
define('RES_NOTIFY_RECORD_POSTPONED_ID',            1168);
define('RES_NOTIFY_RECORD_RESUMED_ID',              1169);
define('RES_NOTIFY_COMMENT_ADDED_ID',               1170);
define('RES_NOTIFY_FILE_ATTACHED_ID',               1171);
define('RES_NOTIFY_FILE_REMOVED_ID',                1172);
define('RES_REQUIRED3_ID',                          1173);
define('RES_POSTPONED_ID',                          1174);
define('RES_DUEDATE_ID',                            1175);
define('RES_DEFAULT_VALUE_ID',                      1176);
define('RES_ON_ID',                                 1177);
define('RES_OFF_ID',                                1178);
define('RES_METRICS_ID',                            1179);
define('RES_OPENED_RECORDS_ID',                     1180);
define('RES_CREATION_VS_CLOSURE_ID',                1181);
define('RES_WEEK_ID',                               1182);
define('RES_NUMBER2_ID',                            1183);
define('RES_CLONE_ID',                              1184);
define('RES_EVENT_RECORD_CLONED_ID',                1185);
define('RES_LOGOUT_ID',                             1186);
define('RES_NOTIFY_RECORD_CLONED_ID',               1187);
define('RES_SETTINGS_ID',                           1188);
define('RES_ROWS_PER_PAGE_ID',                      1189);
define('RES_BOOKMARKS_PER_PAGE_ID',                 1190);
define('RES_LOCK_ID',                               1191);
define('RES_UNLOCK_ID',                             1192);
define('RES_GROUP_TYPE_ID',                         1193);
define('RES_GLOBAL_ID',                             1194);
define('RES_LOCAL_ID',                              1195);
define('RES_CONFIGURATION_ID',                      1196);
define('RES_LOCALROOT_ID',                          1197);
define('RES_WEBROOT_ID',                            1198);
define('RES_SECURITY_ID',                           1199);
define('RES_MIN_PASSWORD_LENGTH_ID',                1200);
define('RES_LOCKS_COUNT_ID',                        1201);
define('RES_LOCKS_TIMEOUT_ID',                      1202);
define('RES_DATABASE_ID',                           1203);
define('RES_DATABASE_TYPE_ID',                      1204);
define('RES_ORACLE_ID',                             1205);
define('RES_MYSQL_ID',                              1206);
define('RES_MSSQL_ID',                              1207);
define('RES_DATABASE_SERVER_ID',                    1208);
define('RES_DATABASE_NAME_ID',                      1209);
define('RES_DATABASE_USER_ID',                      1210);
define('RES_ACTIVE_DIRECTORY_ID',                   1211);
define('RES_LDAP_SERVER_ID',                        1212);
define('RES_PORT_NUMBER_ID',                        1213);
define('RES_SEARCH_ACCOUNT_ID',                     1214);
define('RES_BASE_DN_ID',                            1215);
define('RES_ADMINISTRATORS_ID',                     1216);
define('RES_EMAIL_NOTIFICATIONS_ID',                1217);
define('RES_MAX_SIZE_ID',                           1218);
define('RES_DEBUG_ID',                              1219);
define('RES_DEBUG_MODE_ID',                         1220);
define('RES_DEBUG_MODE_TRACE_ID',                   1221);
define('RES_DEBUG_MODE_FULL_ID',                    1222);
define('RES_DEBUG_LOGS_ID',                         1223);
define('RES_ENABLED2_ID',                           1224);
define('RES_DISABLED2_ID',                          1225);
define('RES_DECIMAL_ID',                            1226);
define('RES_PERMIT_VIEW_RECORDS_ONLY_ID',           1227);
define('RES_SELECT_ALL_ID',                         1228);
define('RES_AUTHOR_ID',                             1229);
define('RES_DATE_ID',                               1230);
define('RES_DURATION_ID',                           1231);
define('RES_SHOW_POSTPONED_ONLY_ID',                1232);
define('RES_SUBSCRIPTION_NAME_ID',                  1233);
define('RES_EVENTS_ID',                             1234);
define('RES_VERSION_X_ID',                          1235);
define('RES_ROLE_ID',                               1236);
define('RES_SUBSCRIBE_ID',                          1237);
define('RES_UNSUBSCRIBE_ID',                        1238);
define('RES_REMINDERS_ID',                          1239);
define('RES_REMINDER_NAME_ID',                      1240);
define('RES_REMINDER_SUBJECT_ID',                   1241);
define('RES_REMINDER_RECIPIENTS_ID',                1242);
define('RES_NEW_REMINDER_ID',                       1243);
define('RES_REMINDER_X_ID',                         1244);
define('RES_PERMIT_SEND_REMINDERS_ID',              1245);
define('RES_SEND_ID',                               1246);
define('RES_NEW_FILTER_ID',                         1247);
define('RES_FILTER_X_ID',                           1248);
define('RES_NEW_SUBSCRIPTION_ID',                   1249);
define('RES_SUBSCRIPTION_X_ID',                     1250);
define('RES_ROWS_MULTILINED_TEXT_ID',               1251);
define('RES_LINK_TO_ANOTHER_RECORD_ID',             1252);
define('RES_SHOW_MOVED_TO_STATES_ONLY_ID',          1253);
define('RES_SHARE_WITH_ID',                         1254);
define('RES_EXPORT_ID',                             1255);
define('RES_SUBSCRIBE_OTHERS_ID',                   1256);
define('RES_SUBSCRIBED_ID',                         1257);
define('RES_SUBJECT_SUBSCRIBED_ID',                 1258);
define('RES_SUBJECT_UNSUBSCRIBED_ID',               1259);
define('RES_CARBON_COPY_ID',                        1260);
define('RES_STORAGE_ID',                            1261);
define('RES_LDAP_ATTRIBUTE_ID',                     1262);
define('RES_VIEWS_ID',                              1263);
define('RES_CLOSE_ID',                              1264);
define('RES_VIEW_NAME_ID',                          1265);
define('RES_NEW_VIEW_ID',                           1266);
define('RES_VIEW_X_ID',                             1267);
define('RES_NO_VIEW_ID',                            1268);
define('RES_SET_ID',                                1269);
define('RES_COLUMNS_ID',                            1270);
define('RES_WARNING_ID',                            1271);
define('RES_INFORMATION_ID',                        1272);
define('RES_ERROR_ID',                              1273);
define('RES_QUESTION_ID',                           1274);
define('RES_ALIGNMENT_ID',                          1275);  // reserved
define('RES_LEFT_ID',                               1276);  // reserved
define('RES_CENTER_ID',                             1277);  // reserved
define('RES_RIGHT_ID',                              1278);  // reserved
define('RES_BANNER_ID',                             1279);
define('RES_ALL_ASSIGNED_TO_ME_ID',                 1280);
define('RES_ALL_CREATED_BY_ME_ID',                  1281);
define('RES_UNUSED_1282_ID',                        1282);
define('RES_YYYY_MM_DD_ID',                         1283);
define('RES_DUMP_ID',                               1284);
define('RES_SUBRECORDS_ID',                         1285);
define('RES_CREATE_SUBRECORD_ID',                   1286);
define('RES_ATTACH_SUBRECORD_ID',                   1287);
define('RES_REMOVE_SUBRECORD_ID',                   1288);
define('RES_UNUSED_1289_ID',                        1289);
define('RES_EVENT_SUBRECORD_ADDED_ID',              1290);
define('RES_EVENT_SUBRECORD_REMOVED_ID',            1291);
define('RES_PERMIT_ADD_SUBRECORDS_ID',              1292);
define('RES_PERMIT_REMOVE_SUBRECORDS_ID',           1293);
define('RES_NOTIFY_SUBRECORD_ADDED_ID',             1294);
define('RES_NOTIFY_SUBRECORD_REMOVED_ID',           1295);
define('RES_CREATED_RECORDS_ID',                    1296);
define('RES_CLOSED_RECORDS_ID',                     1297);
define('RES_CONFIDENTIAL_ID',                       1298);
define('RES_ADD_CONFIDENTIAL_COMMENT_ID',           1299);
define('RES_PERMIT_CONFIDENTIAL_COMMENTS_ID',       1300);
define('RES_EVENT_CONFIDENTIAL_COMMENT_ADDED_ID',   1301);
define('RES_PARENT_ID_ID',                          1302);
define('RES_DEPENDENCY_ID',                         1303);
define('RES_UNUSED_1304_ID',                        1304);
define('RES_ADD_SEPARATOR_ID',                      1305);
define('RES_CSV_DELIMITER_ID',                      1306);
define('RES_CSV_ENCODING_ID',                       1307);
define('RES_CSV_LINE_ENDINGS_ID',                   1308);
define('RES_UNUSED_1309_ID',                        1309);
define('RES_ENABLE_FILTERS_ID',                     1310);
define('RES_DISABLE_FILTERS_ID',                    1311);
define('RES_UNUSED_1312_ID',                        1312);
define('RES_UNUSED_1313_ID',                        1313);
define('RES_UNUSED_1314_ID',                        1314);
define('RES_UNUSED_1315_ID',                        1315);
define('RES_UNUSED_1316_ID',                        1316);
define('RES_UNUSED_1317_ID',                        1317);
define('RES_UNUSED_1318_ID',                        1318);
define('RES_EXPAND_ALL_ID',                         1319);  // reserved
define('RES_COLLAPSE_ALL_ID',                       1320);  // reserved
define('RES_UNUSED_1321_ID',                        1321);
define('RES_LAST_EVENT_ID',                         1322);
define('RES_REGEX_CHECK_ID',                        1323);
define('RES_REGEX_SEARCH_ID',                       1324);
define('RES_REGEX_REPLACE_ID',                      1325);
define('RES_NEXT_STATE_BY_DEFAULT_ID',              1326);
define('RES_POSTPONE_STATUS_ID',                    1327);
define('RES_SHOW_ALL_ID',                           1328);
define('RES_SHOW_ACTIVE_ONLY_ID',                   1329);
define('RES_EVENT_ID',                              1330);
define('RES_UNUSED_1331_ID',                        1331);
define('RES_GUEST_ACCESS_ID',                       1332);
define('RES_NONE2_ID',                              1333);
define('RES_GLOBAL_GROUPS_ID',                      1334);
define('RES_GUEST_ID',                              1335);
define('RES_IMPORT_ID',                             1336);
define('RES_PERMIT_DELETE_RECORD_ID',               1337);
define('RES_UNUSED_1338_ID',                        1338);
define('RES_DEFAULT_LANGUAGE_ID',                   1339);
define('RES_PASSWORD_EXPIRATION_ID',                1340);
define('RES_SESSION_EXPIRATION_ID',                 1341);
define('RES_LDAP_ENUMERATION_ID',                   1342);
define('RES_POSTGRESQL_ID',                         1343);
define('RES_LIST_INDEXES_ID',                       1344);
define('RES_LIST_VALUES_ID',                        1345);
define('RES_CREATED_ID',                            1346);
define('RES_MARK_AS_READ_ID',                       1347);
define('RES_REGISTERED_ID',                         1348);
define('RES_TLS_ID',                                1349);
define('RES_COMPRESSION_ID',                        1350);
define('RES_LAST_STATE_ID',                         1351);
define('RES_COMMENTS_ID',                           1352);
define('RES_SIZE_ID',                               1353);
define('RES_APPEARANCE_ID',                         1354);
define('RES_CSV_ID',                                1355);
define('RES_ENABLE_ID',                             1356);
define('RES_DISABLE_ID',                            1357);
define('RES_PREVIEW_ID',                            1358);
define('RES_OWNER_ID',                              1359);
define('RES_ANYONE_ID',                             1360);
define('RES_MARK_AS_UNREAD_ID',                     1361);
define('RES_PARENT_RECORDS_ID',                     1362);
/**#@-*/

?>
