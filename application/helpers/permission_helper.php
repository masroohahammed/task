<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Check if current user is a client portal admin for their company.
 */
function is_client_admin() {
    $CI =& get_instance();
    if (!has_role('client')) {
        return false;
    }
    $session_flag = $CI->session->userdata('is_client_admin');
    if ($session_flag !== null && $session_flag !== '') {
        return (bool)$session_flag;
    }
    if (!$CI->db->field_exists('is_client_admin', 'users')) {
        return true;
    }
    $uid = (int)$CI->session->userdata('user_id');
    if ($uid < 1) {
        return false;
    }
    $row = $CI->db->select('is_client_admin')->get_where('users', ['id' => $uid])->row();
    return $row && (int)$row->is_client_admin === 1;
}

/**
 * Check if current user has a permission
 */
function has_permission($permission_slug) {
    $CI =& get_instance();
    if (!$CI->session->userdata('user_id')) return FALSE;
    
    $role_slug = $CI->session->userdata('role_slug');
    if ($role_slug === 'admin') return TRUE;
    
    $permissions = $CI->session->userdata('permissions');
    if (empty($permissions)) return FALSE;
    
    return in_array($permission_slug, $permissions);
}

/**
 * Check if current user has a role
 */
function has_role($role_slug) {
    $CI =& get_instance();
    $user_role = $CI->session->userdata('role_slug');
    if (is_array($role_slug)) {
        return in_array($user_role, $role_slug);
    }
    return $user_role === $role_slug;
}

/**
 * Get current user data
 */
function current_user($key = null) {
    $CI =& get_instance();
    if ($key) return $CI->session->userdata($key);
    return array(
        'id'         => $CI->session->userdata('user_id'),
        'name'       => $CI->session->userdata('user_name'),
        'email'      => $CI->session->userdata('user_email'),
        'role_slug'  => $CI->session->userdata('role_slug'),
        'avatar'     => $CI->session->userdata('user_avatar'),
    );
}

/**
 * Require permission or redirect
 */
function require_permission($permission_slug) {
    if (!has_permission($permission_slug)) {
        $CI =& get_instance();
        $CI->session->set_flashdata('error', 'You do not have permission to perform this action.');
        redirect('dashboard');
    }
}

/**
 * Priority badge HTML
 */
function priority_badge($priority) {
    $map = [
        'low'      => 'badge-success',
        'medium'   => 'badge-info',
        'high'     => 'badge-warning',
        'critical' => 'badge-danger',
    ];
    $class = isset($map[$priority]) ? $map[$priority] : 'badge-secondary';
    return '<span class="badge ' . $class . '">' . ucfirst($priority) . '</span>';
}

/**
 * Status badge HTML
 */
function status_badge($status) {
    $map = [
        'todo'        => ['bg-secondary', 'To Do'],
        'in_progress' => ['bg-primary',   'In Progress'],
        'testing'     => ['bg-info',      'Testing'],
        'done'        => ['bg-success',   'Done'],
        'open'        => ['bg-danger',    'Open'],
        'fixed'       => ['bg-success',   'Fixed'],
        'retesting'   => ['bg-warning',   'Retesting'],
        'closed'      => ['bg-dark',      'Closed'],
        'reopened'    => ['bg-danger',    'Reopened'],
        'resolved'    => ['bg-success',   'Resolved'],
        'planning'    => ['bg-secondary', 'Planning'],
        'active'      => ['bg-primary',   'Active'],
        'on_hold'     => ['bg-warning',   'On Hold'],
        'completed'   => ['bg-success',   'Completed'],
        'cancelled'   => ['bg-danger',    'Cancelled'],
    ];
    if (isset($map[$status])) {
        return '<span class="badge ' . $map[$status][0] . '">' . $map[$status][1] . '</span>';
    }
    return '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Time ago
 */
function time_ago($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

/**
 * Generate avatar initials
 */
function get_initials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}

/**
 * Avatar HTML
 */
function user_avatar($name, $avatar = null, $size = 36) {
    $colors = ['#194999','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899'];
    $color = $colors[crc32($name) % count($colors)];
    if ($avatar) {
        return '<img src="' . base_url('uploads/' . $avatar) . '" class="rounded-circle" width="' . $size . '" height="' . $size . '" alt="' . $name . '">';
    }
    $initials = get_initials($name);
    $font = round($size * 0.38);
    return '<div class="user-avatar" style="width:' . $size . 'px;height:' . $size . 'px;background:' . $color . ';font-size:' . $font . 'px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;flex-shrink:0;">' . $initials . '</div>';
}
