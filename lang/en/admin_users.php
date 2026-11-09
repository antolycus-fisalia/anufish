<?php

return [
    // FSH-25: User list and detail
    'list_title' => 'User Management',
    'list_description' => 'View and manage registered user accounts.',
    'detail_title' => 'User Details',
    'search' => 'Search by name or email',
    'search_button' => 'Search',
    'view_detail' => 'View details',
    'back_to_list' => 'Back to user list',
    'empty' => 'No users found.',
    'pagination' => 'User list pagination',
    'name' => 'User name',
    'email' => 'Email',
    'role' => 'Role',
    'status' => 'Status',
    'joined_at' => 'Joined on',

    // Existing: User edit
    'edit_title' => 'Edit user',
    'edit_description' => 'Update the user account name and email.',
    'edit_limit' => 'This form only changes name and email. Role, status, and password stay unchanged.',
    'save' => 'Save changes',
    'saving' => 'Saving...',
    'updated' => 'User data updated successfully.',
    'update_failed' => 'Failed to update user data.',

    'validation' => [
        'name_required' => 'User name is required.',
        'name_string' => 'User name must be text.',
        'name_max' => 'User name must not exceed 255 characters.',
        'email_required' => 'User email is required.',
        'email_invalid' => 'Invalid email format.',
        'email_max' => 'Email must not exceed 255 characters.',
        'email_unique' => 'Email is already used by another user.',
    ],
];
