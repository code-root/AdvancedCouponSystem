<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('admin')->check() && 
               auth()->guard('admin')->user()->hasPermissionTo('manage-settings', 'admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        // General settings
        if ($this->is('admin/settings/general*')) {
            $rules = array_merge($rules, [
                'site_name' => 'required|string|max:255',
                'site_url' => 'nullable|url|max:255',
                'timezone' => 'nullable|string|max:255|in:' . implode(',', timezone_identifiers_list()),
                'locale' => 'nullable|string|max:10|in:en,ar,fr,es,de,it,pt,ru,ja,ko,zh',
                'maintenance_mode' => 'nullable|boolean',
                'maintenance_message' => 'nullable|string|max:500',
                'registration_enabled' => 'nullable|boolean',
            ]);
        }

        // SMTP settings
        if ($this->is('admin/settings/smtp*')) {
            $rules = array_merge($rules, [
                'mail_mailer' => 'nullable|string|max:255|in:smtp,sendmail,mailgun,ses,postmark',
                'mail_host' => 'nullable|string|max:255',
                'mail_port' => 'nullable|integer|min:1|max:65535',
                'mail_username' => 'nullable|string|max:255',
                'mail_password' => 'nullable|string|max:255',
                'mail_encryption' => 'nullable|in:tls,ssl',
                'mail_from_address' => 'nullable|email|max:255',
                'mail_from_name' => 'nullable|string|max:255',
                'mail_verify_peer' => 'nullable|boolean',
            ]);
        }

        // SEO settings
        if ($this->is('admin/settings/seo*')) {
            $rules = array_merge($rules, [
                'meta_description' => 'nullable|string|max:160',
                'meta_keywords' => 'nullable|string|max:500',
                'meta_author' => 'nullable|string|max:255',
                'robots_meta' => 'nullable|string|max:255',
                'og_title' => 'nullable|string|max:60',
                'og_description' => 'nullable|string|max:160',
                'facebook_url' => 'nullable|url|max:255',
                'twitter_url' => 'nullable|url|max:255',
                'linkedin_url' => 'nullable|url|max:255',
                'instagram_url' => 'nullable|url|max:255',
                'google_analytics_id' => 'nullable|string|max:50|regex:/^G-[A-Z0-9]+$/',
                'google_tag_manager_id' => 'nullable|string|max:50|regex:/^GTM-[A-Z0-9]+$/',
                'facebook_pixel_id' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            ]);
        }

        // Payment settings
        if ($this->is('admin/settings/payment*')) {
            $rules = array_merge($rules, [
                'stripe_public_key' => 'nullable|string|max:255|regex:/^pk_test_|^pk_live_/',
                'stripe_secret_key' => 'nullable|string|max:255|regex:/^sk_test_|^rk_live_/',
                'stripe_webhook_secret' => 'nullable|string|max:255|regex:/^whsec_/',
                'paypal_client_id' => 'nullable|string|max:255',
                'paypal_client_secret' => 'nullable|string|max:255',
                'paypal_mode' => 'nullable|in:sandbox,live',
                'currency' => 'nullable|string|max:3|in:USD,EUR,GBP,CAD,AUD,JPY,CNY,INR,BRL,MXN',
                'currency_symbol' => 'nullable|string|max:10',
                'payment_methods' => 'nullable|array',
                'payment_methods.*' => 'in:stripe,paypal,bank_transfer,crypto',
            ]);
        }

        // Branding settings
        if ($this->is('admin/settings/branding*')) {
            $rules = array_merge($rules, [
                'site_logo' => 'nullable|string|max:500',
                'site_favicon' => 'nullable|string|max:500',
                'primary_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'secondary_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'accent_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'font_family' => 'nullable|string|max:255',
                'custom_css' => 'nullable|string|max:10000',
                'custom_js' => 'nullable|string|max:10000',
            ]);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'site_name.required' => 'Site name is required.',
            'site_url.url' => 'Please provide a valid URL.',
            'timezone.in' => 'Invalid timezone selected.',
            'locale.in' => 'Invalid locale selected.',
            'mail_mailer.in' => 'Invalid mail driver selected.',
            'mail_port.min' => 'Port number must be at least 1.',
            'mail_port.max' => 'Port number cannot exceed 65535.',
            'mail_encryption.in' => 'Invalid encryption method selected.',
            'mail_from_address.email' => 'Please provide a valid email address.',
            'meta_description.max' => 'Meta description cannot exceed 160 characters.',
            'meta_keywords.max' => 'Meta keywords cannot exceed 500 characters.',
            'og_title.max' => 'Open Graph title cannot exceed 60 characters.',
            'og_description.max' => 'Open Graph description cannot exceed 160 characters.',
            'facebook_url.url' => 'Please provide a valid Facebook URL.',
            'twitter_url.url' => 'Please provide a valid Twitter URL.',
            'linkedin_url.url' => 'Please provide a valid LinkedIn URL.',
            'instagram_url.url' => 'Please provide a valid Instagram URL.',
            'google_analytics_id.regex' => 'Invalid Google Analytics ID format.',
            'google_tag_manager_id.regex' => 'Invalid Google Tag Manager ID format.',
            'facebook_pixel_id.regex' => 'Invalid Facebook Pixel ID format.',
            'stripe_public_key.regex' => 'Invalid Stripe public key format.',
            'stripe_secret_key.regex' => 'Invalid Stripe secret key format.',
            'stripe_webhook_secret.regex' => 'Invalid Stripe webhook secret format.',
            'paypal_mode.in' => 'Invalid PayPal mode selected.',
            'currency.in' => 'Invalid currency selected.',
            'primary_color.regex' => 'Invalid color format. Use hex format like #FF0000.',
            'secondary_color.regex' => 'Invalid color format. Use hex format like #FF0000.',
            'accent_color.regex' => 'Invalid color format. Use hex format like #FF0000.',
            'custom_css.max' => 'Custom CSS cannot exceed 10000 characters.',
            'custom_js.max' => 'Custom JavaScript cannot exceed 10000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'site_name' => 'site name',
            'site_url' => 'site URL',
            'timezone' => 'timezone',
            'locale' => 'locale',
            'maintenance_mode' => 'maintenance mode',
            'maintenance_message' => 'maintenance message',
            'registration_enabled' => 'registration enabled',
            'mail_mailer' => 'mail driver',
            'mail_host' => 'mail host',
            'mail_port' => 'mail port',
            'mail_username' => 'mail username',
            'mail_password' => 'mail password',
            'mail_encryption' => 'mail encryption',
            'mail_from_address' => 'from address',
            'mail_from_name' => 'from name',
            'mail_verify_peer' => 'verify peer',
            'meta_description' => 'meta description',
            'meta_keywords' => 'meta keywords',
            'meta_author' => 'meta author',
            'robots_meta' => 'robots meta',
            'og_title' => 'Open Graph title',
            'og_description' => 'Open Graph description',
            'facebook_url' => 'Facebook URL',
            'twitter_url' => 'Twitter URL',
            'linkedin_url' => 'LinkedIn URL',
            'instagram_url' => 'Instagram URL',
            'google_analytics_id' => 'Google Analytics ID',
            'google_tag_manager_id' => 'Google Tag Manager ID',
            'facebook_pixel_id' => 'Facebook Pixel ID',
            'stripe_public_key' => 'Stripe public key',
            'stripe_secret_key' => 'Stripe secret key',
            'stripe_webhook_secret' => 'Stripe webhook secret',
            'paypal_client_id' => 'PayPal client ID',
            'paypal_client_secret' => 'PayPal client secret',
            'paypal_mode' => 'PayPal mode',
            'currency' => 'currency',
            'currency_symbol' => 'currency symbol',
            'payment_methods' => 'payment methods',
            'site_logo' => 'site logo',
            'site_favicon' => 'site favicon',
            'primary_color' => 'primary color',
            'secondary_color' => 'secondary color',
            'accent_color' => 'accent color',
            'font_family' => 'font family',
            'custom_css' => 'custom CSS',
            'custom_js' => 'custom JavaScript',
        ];
    }
}

