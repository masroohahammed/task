<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';

// Dashboard
$route['dashboard'] = 'dashboard/index';

// Projects
$route['projects'] = 'projects/index';
$route['projects/create'] = 'projects/create';
$route['projects/store'] = 'projects/store';
$route['projects/view/(:num)'] = 'projects/view/$1';
$route['projects/edit/(:num)'] = 'projects/edit/$1';
$route['projects/update/(:num)'] = 'projects/update/$1';
$route['projects/delete/(:num)'] = 'projects/delete/$1';
$route['projects/members/(:num)'] = 'projects/members/$1';
$route['projects/add_member/(:num)'] = 'projects/add_member/$1';
$route['projects/remove_member/(:num)/(:num)'] = 'projects/remove_member/$1/$2';

// Tasks / Kanban
$route['projects/kanban/(:num)'] = 'tasks/kanban/$1';
$route['tasks/create/(:num)'] = 'tasks/create/$1';
$route['tasks/store'] = 'tasks/store';
$route['tasks/view/(:num)'] = 'tasks/view/$1';
$route['tasks/edit/(:num)'] = 'tasks/edit/$1';
$route['tasks/update'] = 'tasks/update';
$route['tasks/delete/(:num)'] = 'tasks/delete/$1';
$route['tasks/update_status'] = 'tasks/update_status';
$route['tasks/add_comment'] = 'tasks/add_comment';
$route['tasks/daily_update'] = 'tasks/daily_update';

// Issues
$route['issues'] = 'issues/index';
$route['issues/kanban/(:num)'] = 'issues/kanban/$1';
$route['issues/create'] = 'issues/create';
$route['issues/create/(:num)'] = 'issues/create/$1';
$route['issues/store'] = 'issues/store';
$route['issues/view/(:num)'] = 'issues/view/$1';
$route['issues/edit/(:num)'] = 'issues/edit/$1';
$route['issues/update_status'] = 'issues/update_status';
$route['issues/add_comment'] = 'issues/add_comment';
$route['issues/delete/(:num)'] = 'issues/delete/$1';

// Tickets
$route['tickets'] = 'tickets/index';
$route['tickets/create'] = 'tickets/create';
$route['tickets/store'] = 'tickets/store';
$route['tickets/view/(:num)'] = 'tickets/view/$1';
$route['tickets/edit/(:num)'] = 'tickets/edit/$1';
$route['tickets/update_status'] = 'tickets/update_status';
$route['tickets/add_comment'] = 'tickets/add_comment';
$route['tickets/delete/(:num)'] = 'tickets/delete/$1';

// Clients
$route['clients'] = 'clients/index';
$route['clients/create'] = 'clients/create';
$route['clients/store'] = 'clients/store';
$route['clients/view/(:num)'] = 'clients/view/$1';
$route['clients/edit/(:num)'] = 'clients/edit/$1';
$route['clients/update/(:num)'] = 'clients/update/$1';
$route['clients/delete/(:num)'] = 'clients/delete/$1';

// Users
$route['users'] = 'users/index';
$route['users/create'] = 'users/create';
$route['users/store'] = 'users/store';
$route['users/view/(:num)'] = 'users/view/$1';
$route['users/edit/(:num)'] = 'users/edit/$1';
$route['users/update/(:num)'] = 'users/update/$1';
$route['users/delete/(:num)'] = 'users/delete/$1';
$route['users/permissions/(:num)'] = 'users/permissions/$1';
$route['users/save_permissions'] = 'users/save_permissions';

// Notifications
$route['notifications'] = 'notifications/index';
$route['notifications/mark_read/(:num)'] = 'notifications/mark_read/$1';
$route['notifications/mark_all_read'] = 'notifications/mark_all_read';

// Reports
$route['reports'] = 'reports/index';
$route['reports/projects'] = 'reports/projects';
$route['reports/tasks'] = 'reports/tasks';
$route['reports/issues'] = 'reports/issues';

// Profile
$route['profile'] = 'profile/index';
$route['profile/update'] = 'profile/update';

// Clients - multiple users
$route['clients/add_user/(:num)']       = 'clients/add_user/$1';
$route['clients/remove_user/(:num)']    = 'clients/remove_user/$1';
$route['clients/reset_password/(:num)'] = 'clients/reset_password/$1';
$route['clients/save_user_projects/(:num)'] = 'clients/save_user_projects/$1';
$route['clients/user_projects/(:num)']  = 'clients/user_projects/$1';

// Client portal (sub-user management by client admins)
$route['client-portal/users']                    = 'client_portal/users';
$route['client-portal/add_user']                 = 'client_portal/add_user';
$route['client-portal/remove_user/(:num)']       = 'client_portal/remove_user/$1';
$route['client-portal/reset_password/(:num)']  = 'client_portal/reset_password/$1';
$route['client-portal/save_user_projects/(:num)'] = 'client_portal/save_user_projects/$1';
$route['client-portal/user_projects/(:num)']     = 'client_portal/user_projects/$1';

// Attendance
$route['attendance']                    = 'attendance/index';
$route['attendance/clock_in']           = 'attendance/clock_in';
$route['attendance/clock_out']          = 'attendance/clock_out';
$route['attendance/pre_clock_out']      = 'attendance/pre_clock_out';
$route['attendance/break_start']        = 'attendance/break_start';
$route['attendance/break_end']          = 'attendance/break_end';
$route['attendance/save_worksheet']     = 'attendance/save_worksheet';
$route['attendance/admin']              = 'attendance/admin';
$route['attendance/performance']        = 'attendance/performance';
$route['attendance/user_report/(:num)'] = 'attendance/user_report/$1';

// Leave Requests
$route['attendance/leave_request']              = 'attendance/leave_request';
$route['attendance/leave_approve/(:num)']       = 'attendance/leave_approve/$1';
$route['attendance/leave_reject/(:num)']        = 'attendance/leave_reject/$1';
$route['attendance/leave_cancel/(:num)']        = 'attendance/leave_cancel/$1';
$route['attendance/get_status']                 = 'attendance/get_status';

// HR Module
$route['hr']                          = 'hr/index';
$route['hr/create']                   = 'hr/create';
$route['hr/store']                    = 'hr/store';
$route['hr/view/(:num)']              = 'hr/view/$1';
$route['hr/edit/(:num)']              = 'hr/edit/$1';
$route['hr/update/(:num)']            = 'hr/update/$1';
$route['hr/reset_password/(:num)']    = 'hr/reset_password/$1';
$route['hr/toggle_status/(:num)']     = 'hr/toggle_status/$1';
$route['hr/settings']                 = 'hr/settings';
$route['hr/send_notification']        = 'hr/send_notification';

// Discussions
$route['discussions/(:num)']          = 'discussions/index/$1';
$route['discussions/post']            = 'discussions/post';
$route['discussions/delete/(:num)']   = 'discussions/delete/$1';
$route['discussions/get_new/(:num)']  = 'discussions/get_new/$1';

// Chat
$route['chat/rooms']                       = 'chat/rooms';
$route['chat/user_list']                   = 'chat/user_list';
$route['chat/direct/(:num)']              = 'chat/direct/$1';
$route['chat/session/(:num)']             = 'chat/session/$1';
$route['chat/day_session/(:num)']         = 'chat/day_session/$1';
$route['chat/poll/(:num)']                = 'chat/poll/$1';
$route['chat/send']                       = 'chat/send';
$route['chat/heartbeat']                  = 'chat/heartbeat';
$route['chat/create_group']               = 'chat/create_group';
$route['chat/room_info/(:num)']           = 'chat/room_info/$1';
$route['chat/add_members/(:num)']         = 'chat/add_members/$1';
$route['chat/set_work_hours/(:num)']      = 'chat/set_work_hours/$1';

// Worksheet (side menu)
$route['worksheet']          = 'worksheet/index';
$route['worksheet/save']     = 'worksheet/save';
$route['worksheet/admin']    = 'worksheet/admin';

// OT Report
$route['attendance/ot_report'] = 'attendance/ot_report';
