<?php
// Sanitizer class to validate and sanitizr form inputs
class Sanitizer {
    
    // Store the data to validate
    private array $data = [];
    // Store any errors found
    private array $errors = [];
    
    
    // Constructor - receives form data
    public function __construct(array $data = []) {
        $this->data = $data;
        $this->errors = [];
    }
    

    // Main validation function
    // Example: ['name' => 'required|min:3', 'email' => 'required|email']
    // rules are separated by | 
    public function validate(array $rules): bool {
        // Check each field
        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? '';
            $this->checkField($field, $value, $fieldRules);
        }
        
        return empty($this->errors);
    }
    
    // Check one field with all its rules
    private function checkField(string $field, mixed $value, string $rules): void {
        // Split rules: 'required|min:3|max:20' 
        $ruleList = explode('|', trim($rules));
        
        // Check each rule
        foreach ($ruleList as $rule) {
            $rule = trim($rule);
            
            // Some rules have parameters like 'min:3'
            if (strpos($rule, ':') !== false) {
                [$ruleName, $param] = explode(':', $rule, 2);
                $this->checkRule($field, $value, trim($ruleName), trim($param));
            } else {
                $this->checkRule($field, $value, $rule);
            }
        }
    }
    
    
    // Check one specific rule for a field
    private function checkRule(
        string $field,
        mixed $value,
        string $rule,
        ?string $param = null
    ): void {
        // Clean up the value (remove extra spaces)
        $value = is_string($value) ? trim($value) : $value;

        // Check different rule types
        match ($rule) {
            // 'required' - field cannot be empty
            'required' => ($value === '' || $value === null) && $this->addError($field, "$field is required"),
            
            // 'email' - must be valid email format
            'email' => ($value !== '' && $value !== null) && !filter_var($value, FILTER_VALIDATE_EMAIL) && 
                       $this->addError($field, "$field must be a valid email"),
            
            // 'min:5' - must have at least 5 characters
            'min' => ($value !== '' && $value !== null) && strlen((string)$value) < (int)$param && 
                    $this->addError($field, "$field must be at least $param characters"),
            
            // 'max:20' - must not have more than 20 characters
            'max' => ($value !== '' && $value !== null) && strlen((string)$value) > (int)$param && 
                    $this->addError($field, "$field must not exceed $param characters"),
            
            // 'fname' - only letters, numbers, dash, underscore
            'fname' => ($value !== '' && $value !== null) && !preg_match('/^[a-zA-Z0-9_-]{0,50}$/', (string)$value) && 
                         $this->addError($field, "$field must be at most 50 characters"),
            
            // 'lname' - only letters, numbers, dash, underscore
            'lname' => ($value !== '' && $value !== null) && !preg_match('/^[a-zA-Z0-9_-]{0,50}$/', (string)$value) && 
                         $this->addError($field, "$field must be at most 50 characters"),
            
            // 'password' - needs uppercase, lowercase, number, special char
            'password' => ($value !== '' && $value !== null) && !$this->isStrongPassword($value) && 
                         $this->addError($field, "$field too weak (need uppercase, lowercase, number, !@#$%^&*)"),
            
            // two fields must match
            'match' => ($value !== '' && $value !== null) && $value !== ($this->data[$param] ?? '') && 
                      $this->addError($field, "$field does not match $param"),

            // 'integer' - must be a valid integer
            'integer' => ($value !== '' && $value !== null) && filter_var($value, FILTER_VALIDATE_INT) === false &&
                $this->addError($field, "$field must be an integer"),

            // 'float' - must be a valid float/number
            'float' => ($value !== '' && $value !== null) && filter_var($value, FILTER_VALIDATE_FLOAT) === false &&
                $this->addError($field, "$field must be a valid number"),
            
            // 'phone' - must be a valid phone number (7-15 digits, optional +, spaces, dashes)
            'phone' => ($value !== '' && $value !== null) && !preg_match('/^\+?[\d\s\-]{7,15}$/', (string)$value) &&
            $this->addError($field, "$field must be a valid phone number (7–15 digits)"),

            // 'postal_code' - must be a valid postal code (3-10 chars, letters, numbers, spaces, dashes)
            'postal_code' => ($value !== '' && $value !== null) && !preg_match('/^[A-Za-z0-9\s\-]{3,10}$/', (string)$value) &&
            $this->addError($field, "$field must be a valid postal code (3–10 characters)"),
    
            default => null,
        };
    }
    
    // Check password strength
    private function isStrongPassword(string $password): bool {
        if (strlen($password) < 8) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;      // A-Z
        if (!preg_match('/[a-z]/', $password)) return false;      // a-z
        if (!preg_match('/[0-9]/', $password)) return false;      // 0-9
        if (!preg_match('/[!@#$%^&*()]/', $password)) return false; // special
        return true;
    }
    
    
    // Save an error message
    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    // Get all error messages
    public function getErrors(): array {
        return $this->errors;
    }
    
    // Get first error message instead
    public function firstError(): ?string {
        foreach ($this->errors as $messages) {
            return $messages[0] ?? null;
        }
        return null;
    }
    
    // Check if validation passed (no errors)
    public function passes(): bool {
        return empty($this->errors);
    }
    
    
    // ========== SANITIZATION METHODS ==========
    
    // Remove HTML tags
    public static function sanitizeString(string $input): string {
        // Remove HTML tags like <script>, <iframe>
        return strip_tags(trim($input));
    }
    
    // Clean email address
    public static function sanitizeEmail(string $input): string {
        return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    }
    
    // Clean integer 
    public static function sanitizeInt(mixed $input): int {
        if (!is_scalar($input)) return 0;
        $value = filter_var(trim((string)$input), FILTER_SANITIZE_NUMBER_INT);
        return (int)$value;
    }
    
    // Clean decimal number
    public static function sanitizeFloat(mixed $input): float {
        if (!is_scalar($input)) return 0.0;
        $value = filter_var(trim((string)$input), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        return (float)$value;
    }
        
    // Escape text for HTML output
    public static function escape(string $input): string {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    // Escape text for JavaScript
    public static function escapeJS(mixed $input): string {
        return json_encode($input, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }
}
