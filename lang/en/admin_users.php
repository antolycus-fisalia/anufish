<?php

return [
    'edit_title' => 'Edit user',
    'edit_description' => 'Update the user account name and email.',
    'name' => 'User name',
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
