<?php
/**
 * Pawfect Pet Shop - Settings Model
 * Handles website settings and configuration
 */

class Settings extends Model {
    protected $table = 'settings';
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];
    
    private static $cache = [];
    
    public function getSetting($key, $default = null) {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $setting = $this->findBy('key', $key);
        
        if (!$setting) {
            self::$cache[$key] = $default;
            return $default;
        }
        
        $value = $this->castValue($setting['value'], $setting['type']);
        self::$cache[$key] = $value;
        
        return $value;
    }
    
    public function setSetting($key, $value, $type = 'string', $group = 'general', $description = null) {
        $existingSetting = $this->findBy('key', $key);
        
        $data = [
            'key' => $key,
            'value' => $this->prepareValue($value, $type),
            'type' => $type,
            'group' => $group,
            'description' => $description
        ];
        
        if ($existingSetting) {
            $result = $this->update($existingSetting['id'], $data);
        } else {
            $result = $this->create($data);
        }
        
        // Update cache
        if ($result) {
            self::$cache[$key] = $value;
        }
        
        return $result;
    }
    
    public function getSettingsByGroup($group) {
        $settings = $this->where(['group' => $group], 'key ASC');
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        
        return $result;
    }
    
    public function getAllSettings() {
        $settings = $this->where([], 'group ASC, key ASC');
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['key']] = [
                'value' => $this->castValue($setting['value'], $setting['type']),
                'type' => $setting['type'],
                'group' => $setting['group'],
                'description' => $setting['description']
            ];
        }
        
        return $result;
    }
    
    public function updateSettings($settings) {
        $updated = 0;
        
        foreach ($settings as $key => $value) {
            $existingSetting = $this->findBy('key', $key);
            
            if ($existingSetting) {
                $type = $existingSetting['type'];
                $data = [
                    'value' => $this->prepareValue($value, $type)
                ];
                
                if ($this->update($existingSetting['id'], $data)) {
                    self::$cache[$key] = $this->castValue($data['value'], $type);
                    $updated++;
                }
            }
        }
        
        return $updated;
    }
    
    public function getSettingsForForm($group = null) {
        $conditions = [];
        if ($group) {
            $conditions['group'] = $group;
        }
        
        $settings = $this->where($conditions, 'group ASC, key ASC');
        $grouped = [];
        
        foreach ($settings as $setting) {
            $grouped[$setting['group']][] = [
                'key' => $setting['key'],
                'value' => $this->castValue($setting['value'], $setting['type']),
                'type' => $setting['type'],
                'description' => $setting['description']
            ];
        }
        
        return $grouped;
    }
    
    public function initializeDefaultSettings() {
        $defaults = [
            // Site Settings
            'site_name' => ['value' => 'Pawfect Pet Shop', 'type' => 'string', 'group' => 'site', 'description' => 'Website name'],
            'site_tagline' => ['value' => 'Your Perfect Pet Companion Awaits', 'type' => 'string', 'group' => 'site', 'description' => 'Website tagline'],
            'site_description' => ['value' => 'Premium pet shop offering pet adoption and quality pet products', 'type' => 'text', 'group' => 'site', 'description' => 'Website description'],
            'site_keywords' => ['value' => 'pet shop, pet adoption, pet food, pet accessories', 'type' => 'string', 'group' => 'site', 'description' => 'SEO keywords'],
            'site_logo' => ['value' => '', 'type' => 'file', 'group' => 'site', 'description' => 'Website logo'],
            'site_favicon' => ['value' => '', 'type' => 'file', 'group' => 'site', 'description' => 'Website favicon'],
            
            // Contact Settings
            'contact_email' => ['value' => 'info@pawfectpetshop.com', 'type' => 'email', 'group' => 'contact', 'description' => 'Contact email address'],
            'contact_phone' => ['value' => '(555) 123-4567', 'type' => 'string', 'group' => 'contact', 'description' => 'Contact phone number'],
            'contact_address' => ['value' => '123 Pet Street, Animal City, AC 12345', 'type' => 'text', 'group' => 'contact', 'description' => 'Business address'],
            'business_hours' => ['value' => 'Mon-Fri: 9AM-7PM, Sat-Sun: 10AM-6PM', 'type' => 'string', 'group' => 'contact', 'description' => 'Business hours'],
            
            // Social Media
            'social_facebook' => ['value' => '', 'type' => 'url', 'group' => 'social', 'description' => 'Facebook page URL'],
            'social_instagram' => ['value' => '', 'type' => 'url', 'group' => 'social', 'description' => 'Instagram profile URL'],
            'social_twitter' => ['value' => '', 'type' => 'url', 'group' => 'social', 'description' => 'Twitter profile URL'],
            'social_youtube' => ['value' => '', 'type' => 'url', 'group' => 'social', 'description' => 'YouTube channel URL'],
            
            // E-commerce Settings
            'currency' => ['value' => 'USD', 'type' => 'string', 'group' => 'ecommerce', 'description' => 'Default currency'],
            'currency_symbol' => ['value' => '$', 'type' => 'string', 'group' => 'ecommerce', 'description' => 'Currency symbol'],
            'tax_rate' => ['value' => '0.08', 'type' => 'decimal', 'group' => 'ecommerce', 'description' => 'Tax rate (decimal)'],
            'shipping_cost' => ['value' => '5.99', 'type' => 'decimal', 'group' => 'ecommerce', 'description' => 'Standard shipping cost'],
            'free_shipping_threshold' => ['value' => '50.00', 'type' => 'decimal', 'group' => 'ecommerce', 'description' => 'Free shipping minimum order'],
            
            // Email Settings
            'email_from_name' => ['value' => 'Pawfect Pet Shop', 'type' => 'string', 'group' => 'email', 'description' => 'Email sender name'],
            'email_from_address' => ['value' => 'noreply@pawfectpetshop.com', 'type' => 'email', 'group' => 'email', 'description' => 'Email sender address'],
            'smtp_host' => ['value' => '', 'type' => 'string', 'group' => 'email', 'description' => 'SMTP host'],
            'smtp_port' => ['value' => '587', 'type' => 'number', 'group' => 'email', 'description' => 'SMTP port'],
            'smtp_username' => ['value' => '', 'type' => 'string', 'group' => 'email', 'description' => 'SMTP username'],
            'smtp_password' => ['value' => '', 'type' => 'password', 'group' => 'email', 'description' => 'SMTP password'],
            
            // Theme Settings
            'theme_primary_color' => ['value' => '#FF8C00', 'type' => 'color', 'group' => 'theme', 'description' => 'Primary theme color'],
            'theme_secondary_color' => ['value' => '#FFD700', 'type' => 'color', 'group' => 'theme', 'description' => 'Secondary theme color'],
            'theme_accent_color' => ['value' => '#FF6347', 'type' => 'color', 'group' => 'theme', 'description' => 'Accent theme color'],
            
            // Features
            'enable_registration' => ['value' => '1', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow user registration'],
            'enable_reviews' => ['value' => '1', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable product reviews'],
            'enable_wishlist' => ['value' => '1', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable wishlist feature'],
            'enable_newsletter' => ['value' => '1', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable newsletter signup'],
            
            // Maintenance
            'maintenance_mode' => ['value' => '0', 'type' => 'boolean', 'group' => 'maintenance', 'description' => 'Enable maintenance mode'],
            'maintenance_message' => ['value' => 'We are currently performing maintenance. Please check back soon!', 'type' => 'text', 'group' => 'maintenance', 'description' => 'Maintenance mode message']
        ];
        
        foreach ($defaults as $key => $config) {
            $existing = $this->findBy('key', $key);
            if (!$existing) {
                $this->create([
                    'key' => $key,
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'description' => $config['description']
                ]);
            }
        }
    }
    
    private function castValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
            case 'number':
                return (int)$value;
            case 'decimal':
            case 'float':
                return (float)$value;
            case 'array':
            case 'json':
                return json_decode($value, true) ?: [];
            default:
                return $value;
        }
    }
    
    private function prepareValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'array':
            case 'json':
                return json_encode($value);
            default:
                return (string)$value;
        }
    }
    
    public function clearCache() {
        self::$cache = [];
    }
    
    public function exportSettings() {
        $settings = $this->getAllSettings();
        return json_encode($settings, JSON_PRETTY_PRINT);
    }
    
    public function importSettings($json) {
        $settings = json_decode($json, true);
        
        if (!$settings) {
            return false;
        }
        
        $imported = 0;
        
        foreach ($settings as $key => $config) {
            if ($this->setSetting($key, $config['value'], $config['type'], $config['group'], $config['description'])) {
                $imported++;
            }
        }
        
        return $imported;
    }
}
?>
