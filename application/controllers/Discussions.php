<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Discussions extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Discussion_model');
    }

    public function index($project_id) {
        $project = $this->Project_model->get($project_id);
        if (!$project) show_404();
        $this->_require_client_project($project_id, 'view');

        $data['project']     = $project;
        $data['discussions'] = $this->Discussion_model->get_by_project($project_id);
        $data['members']     = $this->Project_model->get_members($project_id);
        $data['all_users']   = $this->User_model->get_employees();
        $data['page_title']  = 'Discussion — ' . html_escape($project->name);
        $this->render('discussions/index', $data);
    }

    public function post() {
        $project_id = $this->input->post('project_id');
        $message    = $this->input->post('message', TRUE);
        $parent_id  = $this->input->post('parent_id') ?: NULL;
        $tagged     = $this->input->post('tagged_users');

        if (!$message) {
            $this->json_response(['success'=>false,'message'=>'Message required.'],422); return;
        }
        $this->_require_client_project($project_id, 'view');

        // Handle attachment
        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            $this->load->library('upload');
            $cfg = [
                'upload_path'   => FCPATH . 'uploads/discussions/',
                'allowed_types' => 'jpg|jpeg|png|gif|pdf|doc|docx|zip|txt',
                'max_size'      => 10240,
                'file_name'     => 'disc_' . time(),
            ];
            @mkdir(FCPATH . 'uploads/discussions/', 0755, TRUE);
            $this->upload->initialize($cfg);
            if ($this->upload->do_upload('attachment')) {
                $attachment = 'discussions/' . $this->upload->data('file_name');
            }
        }

        $id = $this->Discussion_model->create([
            'project_id'   => $project_id,
            'parent_id'    => $parent_id,
            'user_id'      => $this->current_user->id,
            'message'      => $message,
            'tagged_users' => $tagged ? json_encode(array_map('intval', explode(',', $tagged))) : NULL,
            'attachments'  => $attachment,
        ]);

        // Notify tagged users
        if ($tagged) {
            foreach (explode(',', $tagged) as $uid) {
                $uid = (int)trim($uid);
                if ($uid && $uid !== $this->current_user->id) {
                    $this->Notification_model->create([
                        'user_id'        => $uid,
                        'type'           => 'discussion_tag',
                        'title'          => $this->current_user->first_name . ' tagged you in a discussion',
                        'message'        => substr(strip_tags($message), 0, 100),
                        'reference_type' => 'discussion',
                        'reference_id'   => $project_id,
                    ]);
                }
            }
        }

        // Notify project members (exclude poster)
        $project = $this->Project_model->get($project_id);
        $members = $this->Project_model->get_members($project_id);
        foreach ($members as $m) {
            if ($m->user_id != $this->current_user->id) {
                $this->Notification_model->create([
                    'user_id'        => $m->user_id,
                    'type'           => 'discussion_new',
                    'title'          => 'New discussion in ' . html_escape($project->name),
                    'message'        => $this->current_user->first_name . ': ' . substr(strip_tags($message), 0, 80),
                    'reference_type' => 'discussion',
                    'reference_id'   => $project_id,
                ]);
            }
        }

        $this->log_activity('Posted discussion', 'project', $project_id);

        $disc = $this->Discussion_model->get_single($id);
        $this->json_response(['success' => true, 'id' => $id, 'discussion' => $disc]);
    }

    public function delete($id) {
        $disc = $this->Discussion_model->get($id);
        if (!$disc) { $this->json_response(['success'=>false],404); return; }
        if ($disc->user_id != $this->current_user->id && !has_role(['admin','project_manager','hr'])) {
            $this->json_response(['success'=>false,'message'=>'Unauthorized'],403); return;
        }
        $this->Discussion_model->delete($id);
        $this->json_response(['success' => true]);
    }

    public function get_new($project_id) {
        $after = $this->input->get('after');
        $rows  = $this->Discussion_model->get_after($project_id, $after);
        $this->json_response(['success'=>true,'discussions'=>$rows]);
    }
}
