<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tasks extends MY_Controller {

    public function kanban($project_id) {
        $project = $this->Project_model->get($project_id);
        if (!$project) show_404();

        $data['project']      = $project;
        $data['members']      = $this->Project_model->get_members($project_id);
        $data['tasks_todo']   = $this->Task_model->get_by_status($project_id, 'todo');
        $data['tasks_prog']   = $this->Task_model->get_by_status($project_id, 'in_progress');
        $data['tasks_test']   = $this->Task_model->get_by_status($project_id, 'testing');
        $data['tasks_done']   = $this->Task_model->get_by_status($project_id, 'done');
        $data['page_title']   = 'Kanban — ' . $project->name;
        $this->render('tasks/kanban', $data);
    }

    public function store() {
        require_permission('can_create_task');
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('project_id', 'Project', 'required');

        if ($this->form_validation->run()) {
            $task_id = $this->Task_model->create([
                'project_id'  => $this->input->post('project_id'),
                'title'       => $this->input->post('title', TRUE),
                'description' => $this->input->post('description', TRUE),
                'assigned_to' => $this->input->post('assigned_to') ?: NULL,
                'priority'    => $this->input->post('priority'),
                'status'      => 'todo',
                'deadline'    => $this->input->post('deadline') ?: NULL,
                'created_by'  => $this->current_user->id,
            ]);

            // Notify assigned user
            $assigned = $this->input->post('assigned_to');
            if ($assigned) {
                $this->Notification_model->create([
                    'user_id'        => $assigned,
                    'type'           => 'task_assigned',
                    'title'          => 'New Task Assigned',
                    'message'        => 'You have been assigned task: ' . $this->input->post('title', TRUE),
                    'reference_type' => 'task',
                    'reference_id'   => $task_id,
                ]);
            }

            $this->log_activity('Created task', 'task', $task_id);
            $this->json_response(['success' => true, 'task_id' => $task_id,
                'task' => $this->Task_model->get_with_assignee($task_id)]);
        } else {
            $this->json_response(['success' => false, 'errors' => validation_errors()], 422);
        }
    }

    public function view($id) {
        $task = $this->Task_model->get_with_details($id);
        if (!$task) show_404();

        $data['task']       = $task;
        $data['project']    = $this->Project_model->get($task->project_id);
        $data['comments']   = $this->Task_model->get_comments($id);
        $data['updates']    = $this->Task_model->get_updates($id);
        $data['files']      = $this->Task_model->get_files($id);
        $data['members']    = $this->Project_model->get_members($task->project_id);
        $data['page_title'] = $task->title;
        $this->render('tasks/view', $data);
    }

    public function update_status() {
        $task_id   = $this->input->post('task_id');
        $status    = $this->input->post('status');
        $position  = $this->input->post('position');

        $valid_statuses = ['todo', 'in_progress', 'testing', 'done'];
        if (!in_array($status, $valid_statuses)) {
            $this->json_response(['success' => false, 'message' => 'Invalid status'], 400);
            return;
        }

        $this->Task_model->update($task_id, ['status' => $status, 'position' => $position]);
        $this->log_activity('Moved task to ' . $status, 'task', $task_id);
        $this->json_response(['success' => true]);
    }

    public function add_comment() {
        $task_id = $this->input->post('task_id');
        $comment = $this->input->post('comment', TRUE);

        if (empty($comment)) {
            $this->json_response(['success' => false, 'message' => 'Comment required'], 422);
            return;
        }

        $comment_id = $this->Task_model->add_comment([
            'task_id'   => $task_id,
            'user_id'   => $this->current_user->id,
            'comment'   => $comment,
        ]);

        $this->log_activity('Commented on task', 'task', $task_id);
        $this->json_response([
            'success' => true,
            'comment' => [
                'id'         => $comment_id,
                'comment'    => $comment,
                'user_name'  => $this->current_user->first_name . ' ' . $this->current_user->last_name,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }

    public function daily_update() {
        require_permission('can_update_task');
        $task_id  = $this->input->post('task_id');
        $progress = $this->input->post('progress');
        $work     = $this->input->post('work_done', TRUE);

        $this->Task_model->add_update([
            'task_id'      => $task_id,
            'user_id'      => $this->current_user->id,
            'work_done'    => $work,
            'progress'     => $progress,
            'blockers'     => $this->input->post('blockers', TRUE),
            'hours_spent'  => $this->input->post('hours_spent'),
            'update_date'  => date('Y-m-d'),
        ]);

        $this->Task_model->update($task_id, ['progress' => $progress]);
        $this->log_activity('Daily update on task', 'task', $task_id);
        $this->json_response(['success' => true]);
    }

    public function delete($id) {
        require_permission('can_manage_project');
        $task = $this->Task_model->get($id);
        $this->Task_model->delete($id);
        $this->log_activity('Deleted task', 'task', $id);
        $this->json_response(['success' => true]);
    }
}
