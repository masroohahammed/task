<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function index() {
        $uid  = $this->current_user->id;
        $role = $this->current_user->role_slug;

        if ($role === 'admin' || $role === 'project_manager') {
            $data['total_projects']   = $this->Project_model->count_all();
            $data['active_projects']  = $this->Project_model->count_by_status('active');
            $data['total_tasks']      = $this->Task_model->count_all();
            $data['open_issues']      = $this->Issue_model->count_by_status('open');
            $data['open_tickets']     = $this->Ticket_model->count_by_status('open');
            $data['total_clients']    = $this->Client_model->count_all();
            $data['total_users']      = $this->User_model->count_all();
            $data['recent_projects']  = $this->Project_model->get_recent(6);
            $data['recent_tasks']     = $this->Task_model->get_recent(5);
            $data['recent_issues']    = $this->Issue_model->get_recent(5);
            $data['recent_tickets']   = $this->Ticket_model->get_recent(5);
            $data['task_stats']       = $this->Task_model->get_status_stats();
            $data['issue_stats']      = $this->Issue_model->get_status_stats();
            $data['recent_activities']= $this->Activity_model->get_recent(10);
            $data['page_title'] = 'Dashboard';
            $this->render('dashboard/admin', $data);

        } elseif ($role === 'client') {
            $client_id = $this->current_user->client_id;
            $is_admin  = $this->User_model->is_portal_admin($uid);
            $data['is_client_admin'] = $is_admin;
            $data['projects']       = $this->Project_model->get_for_client_user($client_id, $uid, $is_admin);
            $data['my_tickets']     = $this->Ticket_model->get_for_client_user($client_id, $uid, $is_admin, 5);
            $data['open_tickets']   = $this->Ticket_model->count_by_client($client_id, 'open');
            $data['total_projects'] = count($data['projects']);
            $data['team_users']     = [];
            $data['user_projects']  = [];
            if ($is_admin) {
                $data['team_users']    = $this->Client_model->get_users($client_id);
                $data['all_projects']  = $this->Project_model->get_by_client($client_id);
                $this->load->model('Client_user_project_model');
                $data['user_projects'] = $this->Client_user_project_model->get_grouped_for_client($client_id);
            }
            $data['page_title']     = 'Client Dashboard';
            $this->render('dashboard/client', $data);

        } else {
            // Employee
            $data['my_tasks']          = $this->Task_model->get_assigned_to($uid, 10);
            $data['my_issues']         = $this->Issue_model->get_assigned_to($uid, 5);
            $data['my_tickets']        = $this->Ticket_model->get_assigned_to($uid, 5);
            $data['total_my_tasks']    = $this->Task_model->count_assigned_to($uid);
            $data['pending_tasks']     = $this->Task_model->count_assigned_by_status($uid, 'todo');
            $data['in_progress_tasks'] = $this->Task_model->count_assigned_by_status($uid, 'in_progress');
            $data['done_tasks']        = $this->Task_model->count_assigned_by_status($uid, 'done');
            $data['recent_activities'] = $this->Activity_model->get_by_user($uid, 8);
            $data['page_title'] = 'My Dashboard';
            $this->render('dashboard/employee', $data);
        }
    }
}
