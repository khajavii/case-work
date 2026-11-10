<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */


    "accepted" => "The :attribute field must be accepted.",
    "accepted_if" => "The :attribute field must be accepted when :other is :value.",
    "active_url" => "The :attribute field must be a valid URL.",
    "after" => "The :attribute field must be a date after :date.",
    "after_or_equal" => "The :attribute field must be a date after or equal to :date.",
    "alpha" => "The :attribute field must only contain letters.",
    "alpha_dash" => "The :attribute field may only contain letters, numbers, dashes, and underscores.",
    "alpha_num" => "The :attribute field must only contain letters and numbers.",
    "array" => "The :attribute field must be an array.",
    "ascii" => "The :attribute field may only contain single-byte alphanumeric characters and symbols.",
    "before" => "The :attribute field must be a date before :date.",
    "before_or_equal" => "The :attribute field must be a date before or equal to :date.",
    "between" => [
        "array" => "The :attribute field must have between :min and :max items.",
        "file" => "The :attribute field must be between :min and :max kilobytes.",
        "numeric" => "The :attribute field must be between :min and :max.",
        "string" => "The :attribute field must be between :min and :max characters."
    ],
    "boolean" => "The :attribute field must be true or false.",
    "confirmed" => "The :attribute confirmation does not match.",
    "current_password" => "The password is incorrect.",
    "date" => "The :attribute field is not a valid date.",
    "date_equals" => "The :attribute field must be a date equal to :date.",
    "date_format" => "The :attribute field does not match the format :format.",
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute field must be declined.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'different' => 'The :attribute and :other fields must be different.',
    'digits' => 'The :attribute field must be :digits digits.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute field must not end with any of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with any of the following: :values.',
    'email' => 'The :attribute field must be a valid email address.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute field must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have at least :value items.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be at least :value characters.',
    ],
    'image' => 'The :attribute field must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute field must be an integer.',
    'ip' => 'The :attribute field must be a valid IP address.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have fewer than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must have fewer than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must not exceed :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute field must not have more than :max items.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute field must not be greater than :max.',
        'string' => 'The :attribute field must not have more than :max characters.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'The :attribute field must be a file of type: :values.',
    'mimetypes' => 'The :attribute field must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute field must have at least :min items.',
        'file' => 'The :attribute field must be at least :min kilobytes.',
        'numeric' => 'The :attribute field must be at least :min.',
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute field must be a number.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data breach. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values is present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute field must match :other.',

    'size' => [
        'array' => 'The :attribute field must contain :size items.',
        'file' => 'The :attribute field must be :size kilobytes.',
        'numeric' => 'The :attribute field must be :size.',
        'string' => 'The :attribute field must be :size characters.',
    ],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string' => 'The :attribute field must be a string.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'The :attribute field must be a valid URL.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',
    'strong_password' => 'The :attribute field must contain at least one uppercase letter, one lowercase letter, and one number.',
    'no_sql_injection' => 'The :attribute field contains a SQL injection keyword.',
    'strip_tags' => 'The :attribute field contains HTML tags.',
    'check_admin_with_user_id' => 'The selected :attribute is invalid.',
    'phone' =>  'The :attribute field must be a valid phone number.',
    'unable_to_delete' => 'Unable to delete :model with ID :ids.',
    'exists_admin_job' => 'The selected :attribute is invalid.',
    'order_admin_match' => 'The selected :attribute is invalid because the order does not belong to the specified administrator.',
    'order_user_match' => 'The selected :attribute is invalid because the order does not belong to the specified user.',
    'double' => 'The :attribute field must be a double with up to 2 decimal places.',
    "decimal_up_to_6" => "The :attribute field must be a number with up to 6 decimal places.",
    'is_user_synced_with_admin' => 'The :attribute field must be synchronized with the specified administrator.',
    'zarinpal_merchant_id' => 'The :attribute field must be a valid Zarinpal merchant ID.',
    'more_than_order_amount' => 'The requested amount exceeds the order amount.',
    'order_amount_min' => 'The order amount cannot be less than :percent%.',
    'be_super_display' => 'You cannot use this :attribute. Your account must be a super user.',
    'has_user' => 'The :attribute field must have the user role.',
    'not_self_user_id' => 'The selected ID for :attribute is invalid.',
    'not_self_admin_id' => 'The selected ID for :attribute is invalid.',
    'cannot_use_attribute' => 'This :attribute cannot be used.',
    'combination_invalid' => "The selected combination of :attribute and :attribute1 is invalid.",
    'not_exists' => "The :attribute does not exist.",
    'network_exists' => "The :attribute already exists.",
    'unsupported_network' => "The selected network is not supported for this address: :attribute.",
    'cannot_use_discount' => 'You have already used a discount code and cannot use it again.',




    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'Custom message',
        ],
        'email' => [
            'current_user' => 'The entered email address does not match your current email address.',
        ],
        'production_id' => [
            'required' => 'The production ID is required.',
            'integer' => 'The production ID must be an integer.',
            'min' => 'The production ID must be greater than zero.',
            'exists' => 'The selected production job does not exist or is not accessible.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'phone' => 'Phone number',
        'mobile' => 'Mobile number',
        'email' => 'Email address',
        'otp_status' => 'OTP delivery status',
        'password' => 'Password',
        'name' => 'Name',
        'address' => 'Address',
        'gender' => 'Gender',
        'national_code' => 'National ID',
        'full_name' => 'Full name',
        'image' => 'Image',
        'login_type' => 'Login type',
        'code_phone_number' => 'Mobile verification code',
        'code_email' => 'Email verification code',
        'orders' => 'Orders',
        'order' => 'Order',
        'jobs' => 'Jobs',
        'admin_id' => 'Administrator ID',
        'title' => 'Title',
        'request_delivery_at' => 'Requested delivery date',
        'context' => 'Content',
        'type' => 'Type',
        'number' => 'Number',
        'quantity' => 'Quantity',
        'orderable_id' => 'Orderable ID',
        'services' => 'Services',
        'admins' => 'Administrators',
        'job_name' => 'Job name',
        'admin_type_id' => 'Job type',
        'user' => 'User',
        'users' => 'Users',
        'job' => 'Job',
        'job_types' => 'Job types',
        'profile' => 'Profile',
        'lang' => 'Language',
        'user_id' => 'User ID',
        'item' => 'Item',
        'invoice' => 'Invoice',
        'invoices' => 'Invoices',
        'order_id' => 'Order ID',
        'code_factor' => 'Invoice code',
        'invoice_details' => 'Invoice details',
        'payment' => 'Payment',
        'invoice_id' => 'Invoice ID',
        'setting' => 'Setting',
        'merchant_id' => 'Merchant ID',
        'transactions' => 'Transactions',
        'service' => 'Service',
        'active' => 'Active',
        'de_active' => 'Inactive',
        'product' => 'Product',
        'products' => 'Products',
        'code' => 'Code',
        'link' => 'Link',
        'regions' => 'Regions',
        'job_ad' => 'Job advertisement',
        'post' => 'Post',
        'posts' => 'Posts',
        'user_follow_id' => 'User follow ID',
        'admin_follow_id' => 'Administrator follow ID',
        'user_follow' => 'User follow',
        'user_follows' => 'User follows',
        'user_followings' => 'User followers',
        'admin_follow' => 'Administrator follow',
        'user_name' => 'Username (mobile number or email)',
        'tickets' => 'Tickets',
        'ticket' => 'Ticket',
        'ticket_details' => 'Ticket details',
        'post_type' => 'Post type',
        'app' => 'Application',
        'images' => 'Images',
        'plan' => 'Plan',
        'transaction' => 'Transaction',
        'currency' => 'Currency',
        'currency_id' => 'Currency ID',
        'from_amount' => 'From amount',
        'to_amount' => 'To amount',
        'report_transaction' => 'Transaction report',
        'locale' => 'Locale',
        'document' => 'Document',
        'message' => 'Message',
        'recipient' => 'Recipient',
        'gateway' => 'Gateway',
        'ticket_id' => 'Ticket ID',
        'customers' => 'Customers',
        'kyc' => 'Identity verification',
        'payment_gateway' => 'Payment gateway',
        'purchase' => 'Purchase',
        'purchase_order' => 'Purchase order',

        // website { coinpay.finance }

        'meta_head' => 'Meta information',
        'contact_us' => 'Contact us',
        'token' => 'Token',
        'licence' => 'License',

        'amount' => 'Amount',
        'converted' => 'Converted amount',

        'discount' => 'Discount',

        'withdraw' => 'Withdrawal',
        'revenue' => 'Revenue',
        'permission' => 'Permission',
        'permissions' => 'Permissions',
        'admin' => 'Administrator',
        'role' => 'Role',
        'roles' => 'Roles',
        'submission' => 'Submission',
        'document_step2' => 'Step 2 documents',
        'document_step3' => 'Step 3 documents',
        'networks' => 'Networks',
        'network' => 'Network',
        'notification' => 'Notification',
        'notifications' => 'Notifications',
        'rejection_reason' => 'Rejection reason',
        'wallet' => 'Wallet',

    ],

];
