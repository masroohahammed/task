<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_user_project_model extends CI_Model {

    public function table_exists() {
        return $this->db->table_exists('client_user_projects');
    }

    public function get_for_user($user_id) {
        if (!$this->table_exists()) {
            return [];
        }
        return $this->db->select('cup.*, p.name as project_name, p.status as project_status')
            ->from('client_user_projects cup')
            ->join('projects p', 'p.id = cup.project_id')
            ->where('cup.user_id', (int)$user_id)
            ->order_by('p.name', 'ASC')
            ->get()->result();
    }

    public function get_project_ids_for_user($user_id) {
        if (!$this->table_exists()) {
            return [];
        }
        $rows = $this->db->select('project_id')
            ->from('client_user_projects')
            ->where('user_id', (int)$user_id)
            ->get()->result();
        return array_map('intval', array_column($rows, 'project_id'));
    }

    public function user_has_project($user_id, $project_id, $min_permission = 'view') {
        if (!$this->table_exists()) {
            return true;
        }
        $row = $this->db->get_where('client_user_projects', [
            'user_id'    => (int)$user_id,
            'project_id' => (int)$project_id,
        ])->row();
        if (!$row) {
            return false;
        }
        return $this->_permission_level($row->permission) >= $this->_permission_level($min_permission);
    }

    public function sync_for_user($user_id, array $assignments, $assigned_by = null) {
        if (!$this->table_exists()) {
            return;
        }
        $user_id = (int)$user_id;
        $this->db->where('user_id', $user_id)->delete('client_user_projects');

        $allowed = ['view', 'tickets', 'manage'];
        foreach ($assignments as $project_id => $permission) {
            $project_id = (int)$project_id;
            if ($project_id < 1) {
                continue;
            }
            if (!in_array($permission, $allowed, true)) {
                $permission = 'view';
            }
            $this->db->insert('client_user_projects', [
                'user_id'     => $user_id,
                'project_id'  => $project_id,
                'permission'  => $permission,
                'assigned_by' => $assigned_by ? (int)$assigned_by : null,
            ]);
        }
    }

    public function remove_user_assignments($user_id) {
        if (!$this->table_exists()) {
            return;
        }
        $this->db->where('user_id', (int)$user_id)->delete('client_user_projects');
    }

    private function _permission_level($permission) {
        $map = ['view' => 1, 'tickets' => 2, 'manage' => 3];
        return $map[$permission] ?? 0;
    }
}
