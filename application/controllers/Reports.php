<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {

    public function index() {
        if (!has_role(['admin', 'project_manager'])) {
            $this->session->set_flashdata('error', 'Access denied.');
            redirect('dashboard');
        }
        $data['total_projects']   = $this->Project_model->count_all();
        $data['active_projects']  = $this->Project_model->count_by_status('active');
        $data['completed_projects'] = $this->Project_model->count_by_status('completed');
        $data['total_tasks']      = $this->Task_model->count_all();
        $data['task_stats']       = $this->Task_model->get_status_stats();
        $data['issue_stats']      = $this->Issue_model->get_status_stats();
        $data['open_issues']      = $this->Issue_model->count_by_status('open');
        $data['closed_issues']    = $this->Issue_model->count_by_status('closed');
        $data['open_tickets']     = $this->Ticket_model->count_by_status('open');
        $data['resolved_tickets'] = $this->Ticket_model->count_by_status('resolved');
        $data['total_clients']    = $this->Client_model->count_all();
        $data['total_users']      = $this->User_model->count_all();
        $data['recent_projects']  = $this->Project_model->get_recent(10);
        $data['recent_activities']= $this->Activity_model->get_recent(15);
        $data['page_title']       = 'Reports & Analytics';
        $this->render('reports/index', $data);
    }
}
