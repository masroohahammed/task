<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model {

    public function get($id) {
        return $this->db->get_where('projects', ['id' => $id])->row();
    }

    public function get_with_details($id) {
        return $this->db->select('p.*, c.company_name, c.contact_person, c.email as client_email,
            u.first_name as pm_first, u.last_name as pm_last')
            ->from('projects p')
            ->join('clients c', 'c.id = p.client_id', 'left')
            ->join('users u', 'u.id = p.created_by', 'left')
            ->where('p.id', $id)
            ->get()->row();
    }

    public function get_all() {
        return $this->db->order_by('name')->get('projects')->result();
    }

    public function get_all_with_stats() {
        $projects = $this->db->select('p.*, c.company_name')
            ->from('projects p')
            ->join('clients c', 'c.id = p.client_id', 'left')
            ->order_by('p.created_at', 'DESC')
            ->get()->result();

        foreach ($projects as &$proj) {
            $proj->task_count   = $this->db->where('project_id', $proj->id)->count_all_results('tasks');
            $proj->issue_count  = $this->db->where('project_id', $proj->id)->count_all_results('issues');
            $proj->ticket_count = $this->db->where('project_id', $proj->id)->count_all_results('tickets');
            $proj->members      = $this->get_members($proj->id);
        }
        return $projects;
    }

    public function get_by_client($client_id) {
        return $this->db->where('client_id', $client_id)->order_by('created_at','DESC')->get('projects')->result();
    }

    /**
     * Projects visible to a client portal user (all if admin, else assigned only).
     */
    public function get_for_client_user($client_id, $user_id, $is_client_admin = false) {
        $client_id = (int)$client_id;
        $user_id   = (int)$user_id;

        if ($is_client_admin || !$this->db->table_exists('client_user_projects')) {
            return $this->get_by_client($client_id);
        }

        return $this->db->select('p.*')
            ->from('projects p')
            ->join('client_user_projects cup', 'cup.project_id = p.id')
            ->where('p.client_id', $client_id)
            ->where('cup.user_id', $user_id)
            ->order_by('p.created_at', 'DESC')
            ->get()->result();
    }

    public function client_user_can_access($project_id, $client_id, $user_id, $is_client_admin = false, $min_permission = 'view') {
        $project = $this->get((int)$project_id);
        if (!$project || (int)$project->client_id !== (int)$client_id) {
            return false;
        }
        if ($is_client_admin || !$this->db->table_exists('client_user_projects')) {
            return true;
        }
        $this->load->model('Client_user_project_model');
        return $this->Client_user_project_model->user_has_project($user_id, $project_id, $min_permission);
    }

    public function get_by_member($user_id) {
        return $this->db->select('p.*, c.company_name')
            ->from('projects p')
            ->join('project_members pm', 'pm.project_id = p.id')
            ->join('clients c', 'c.id = p.client_id', 'left')
            ->where('pm.user_id', $user_id)
            ->order_by('p.created_at', 'DESC')
            ->get()->result();
    }

    public function get_recent($limit = 5) {
        return $this->db->select('p.*, c.company_name')
            ->from('projects p')
            ->join('clients c', 'c.id = p.client_id', 'left')
            ->order_by('p.created_at', 'DESC')
            ->limit($limit)
            ->get()->result();
    }

    public function get_members($project_id) {
        return $this->db->select('u.id as user_id, u.first_name, u.last_name, u.avatar, u.job_title, pm.role')
            ->from('project_members pm')
            ->join('users u', 'u.id = pm.user_id')
            ->where('pm.project_id', $project_id)
            ->get()->result();
    }

    public function create($data) {
        $data = $this->_normalize_client_id($data);
        $this->db->insert('projects', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $data = $this->_normalize_client_id($data);
        $this->db->where('id', $id)->update('projects', $data);
    }

    /** Empty string client_id breaks FK; must be NULL for internal projects. */
    private function _normalize_client_id(array $data) {
        if (!array_key_exists('client_id', $data)) {
            return $data;
        }
        $cid = $data['client_id'];
        if ($cid === '' || $cid === false || $cid === null) {
            $data['client_id'] = null;
            return $data;
        }
        $cid = (int)$cid;
        if ($cid < 1) {
            $data['client_id'] = null;
            return $data;
        }
        $exists = $this->db->where('id', $cid)->count_all_results('clients') > 0;
        $data['client_id'] = $exists ? $cid : null;
        return $data;
    }

    public function delete($id) {
        $this->db->delete('projects', ['id' => $id]);
    }

    public function add_member($project_id, $user_id, $role = 'member') {
        $exists = $this->db->where(['project_id' => $project_id, 'user_id' => $user_id])->count_all_results('project_members');
        if (!$exists) {
            if (!$this->_can_be_project_member((int)$user_id)) {
                return;
            }
            $this->db->insert('project_members', ['project_id' => $project_id, 'user_id' => $user_id, 'role' => $role]);
        }
    }

    public function remove_member($project_id, $user_id) {
        $this->db->delete('project_members', ['project_id' => $project_id, 'user_id' => $user_id]);
    }

    /**
     * Replace project members with the given user IDs. Ensures $manager_user_id stays as manager.
     */
    public function sync_members($project_id, array $user_ids, $manager_user_id) {
        $project_id = (int)$project_id;
        $manager_user_id = (int)$manager_user_id;
        $ids = array_values(array_unique(array_filter(array_map('intval', $user_ids))));
        if ($manager_user_id && !in_array($manager_user_id, $ids, true)) {
            $ids[] = $manager_user_id;
        }
        $this->db->where('project_id', $project_id)->delete('project_members');
        foreach ($ids as $uid) {
            if (!$this->_can_be_project_member($uid)) {
                continue;
            }
            $role = ($uid === $manager_user_id) ? 'manager' : 'member';
            $this->db->insert('project_members', [
                'project_id' => $project_id,
                'user_id'    => $uid,
                'role'       => $role,
            ]);
        }
    }

    /** Active user, not a client portal account. */
    private function _can_be_project_member($user_id) {
        $uid = (int)$user_id;
        if ($uid < 1) {
            return false;
        }
        $row = $this->db->select('u.id')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.id', $uid)
            ->where('u.status', 'active')
            ->where('r.slug !=', 'client')
            ->get()->row();
        return (bool)$row;
    }

    public function count_all() {
        return $this->db->count_all('projects');
    }

    public function count_by_status($status) {
        return $this->db->where('status', $status)->count_all_results('projects');
    }
}
