<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Discussion_model extends CI_Model {

    public function get_by_project($project_id) {
        return $this->db->select('pd.*, u.first_name, u.last_name, u.avatar, u.job_title')
            ->from('project_discussions pd')
            ->join('users u', 'u.id = pd.user_id')
            ->where('pd.project_id', $project_id)
            ->where('pd.parent_id IS NULL')
            ->order_by('pd.is_pinned DESC, pd.created_at ASC')
            ->get()->result();
    }

    public function get_replies($parent_id) {
        return $this->db->select('pd.*, u.first_name, u.last_name, u.avatar')
            ->from('project_discussions pd')
            ->join('users u', 'u.id = pd.user_id')
            ->where('pd.parent_id', $parent_id)
            ->order_by('pd.created_at ASC')
            ->get()->result();
    }

    public function get($id) {
        return $this->db->get_where('project_discussions', ['id' => $id])->row();
    }

    public function get_single($id) {
        return $this->db->select('pd.*, u.first_name, u.last_name, u.avatar, u.job_title')
            ->from('project_discussions pd')
            ->join('users u', 'u.id = pd.user_id')
            ->where('pd.id', $id)
            ->get()->row();
    }

    public function get_after($project_id, $after_id) {
        return $this->db->select('pd.*, u.first_name, u.last_name, u.avatar')
            ->from('project_discussions pd')
            ->join('users u', 'u.id = pd.user_id')
            ->where('pd.project_id', $project_id)
            ->where('pd.id >', (int)$after_id)
            ->order_by('pd.created_at ASC')
            ->get()->result();
    }

    public function create($data) {
        $this->db->insert('project_discussions', $data);
        return $this->db->insert_id();
    }

    public function delete($id) {
        $this->db->delete('project_discussions', ['id' => $id]);
        $this->db->delete('project_discussions', ['parent_id' => $id]);
    }

    public function pin($id) {
        $disc = $this->get($id);
        $this->db->where('id', $id)->update('project_discussions', ['is_pinned' => $disc->is_pinned ? 0 : 1]);
    }
}
