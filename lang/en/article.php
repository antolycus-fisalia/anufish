
<?php

return [
    'messages' => [
        'forbidden' => 'You are not allowed to perform this article action.',
        'created' => 'Article created successfully.',
        'updated' => 'Article updated successfully.',
        'deleted' => 'Article deleted successfully.',
        'create_failed' => 'Failed to create article. Please retry.',
        'update_failed' => 'Failed to update article. Please retry.',
        'delete_failed' => 'Failed to delete article. Please retry.',
    ],

    'moderation' => [
        'approved' => 'Article approved successfully.',
        'rejected' => 'Article rejected successfully.',
        'deleted' => 'Article deleted successfully.',
        'conflict' => 'The article changed or this action is no longer available. Reopen the article details.',
        'failed' => 'Failed to update the moderation status. Please retry.',
    ],

    'validation' => [
        'title_required' => 'The article title is required.',
        'title_string' => 'The article title must be text.',
        'title_max' => 'The article title must not exceed 255 characters.',
        'category_required' => 'The article category is required.',
        'category_string' => 'The article category must be text.',
        'category_max' => 'The article category must not exceed 100 characters.',
        'content_required' => 'The article content is required.',
        'content_string' => 'The article content must be text.',
        'content_max' => 'The article content must not exceed 50000 characters.',
        'image_invalid' => 'The article image must be a valid image file.',
        'image_dimensions' => 'The article image must not exceed 8 megapixels.',
        'image_resources' => 'The image is too large to process. Please reduce its resolution.',
        'image_max' => 'The article image must not exceed 5 MB.',
        'image_format' => 'The image must be JPG, JPEG, or PNG.',
    ],

    'public' => [
        'title' => 'Articles',
        'description' => 'Explore published articles.',
        'empty_title' => 'No articles yet',
        'empty_description' => 'Approved articles will appear here.',
        'no_image' => 'Image unavailable',
        'author' => 'Author',
        'unknown_author' => 'Unknown author',
        'pagination' => 'Article pagination',
    ],

    'admin_moderation' => [
        'list_title' => 'Article Moderation',
        'list_description' => 'Review and manage articles submitted by users.',
        'detail_title' => 'Article Moderation Detail',

        'filter_status' => 'Filter by Status',
        'all_statuses' => 'All Statuses',
        'apply_filter' => 'Apply Filter',

        'author' => 'Author',
        'unknown_author' => 'Unknown author',
        'category' => 'Category',
        'created_at' => 'Created At',
        'content' => 'Article Content',

        'view_detail' => 'View Details',
        'back_to_list' => 'Back to List',

        'empty' => 'No articles found for this status.',
        'pagination' => 'Article page navigation',

        'actions_title' => 'Moderation Actions',
        'actions_pending' => 'Moderation controls will be connected to the backend during integration.',

        // Moderation action buttons and messages
        'approve' => 'Approve',
        'reject' => 'Reject',
        'delete' => 'Delete',
        'confirm_delete' => 'Are you sure you want to delete this article?',
        'refresh_detail' => 'Reload article details',
        'request_failed' => 'Something went wrong. Please try again.',

        'statuses' => [
            'menunggu' => 'Pending',
            'disetujui' => 'Approved',
            'ditolak' => 'Rejected',
        ],
    ],
];
