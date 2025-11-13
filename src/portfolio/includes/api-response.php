<?php
/**
 * API Response Helper
 *
 * Provides standardized JSON responses for API endpoints
 */

class ApiResponse {
    /**
     * Send a successful JSON response and exit
     *
     * @param array $data Additional data to include in the response
     * @param string|null $message Optional success message
     * @return void (exits script)
     */
    public static function success($data = [], $message = null) {
        self::setJsonHeader();

        $response = ['success' => true];

        if ($message !== null) {
            $response['message'] = $message;
        }

        // Merge additional data
        $response = array_merge($response, $data);

        echo json_encode($response);
        exit;
    }

    /**
     * Send an error JSON response and exit
     *
     * @param string $message Error message
     * @param int $httpCode HTTP status code (default: 400)
     * @return void (exits script)
     */
    public static function error($message, $httpCode = 400) {
        self::setJsonHeader();

        if ($httpCode !== 200) {
            http_response_code($httpCode);
        }

        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Send an unauthorized error response (401)
     *
     * @param string $message Error message (default: 'Unauthorized')
     * @return void (exits script)
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }

    /**
     * Send a forbidden error response (403)
     *
     * @param string $message Error message (default: 'Forbidden')
     * @return void (exits script)
     */
    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403);
    }

    /**
     * Send a not found error response (404)
     *
     * @param string $message Error message (default: 'Not found')
     * @return void (exits script)
     */
    public static function notFound($message = 'Not found') {
        self::error($message, 404);
    }

    /**
     * Send a method not allowed error response (405)
     *
     * @param string $message Error message (default: 'Method not allowed')
     * @return void (exits script)
     */
    public static function methodNotAllowed($message = 'Method not allowed') {
        self::error($message, 405);
    }

    /**
     * Send a server error response (500)
     *
     * @param string $message Error message (default: 'Internal server error')
     * @return void (exits script)
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }

    /**
     * Validate that the request method matches expected method(s)
     *
     * @param string|array $methods Expected method(s) (e.g., 'POST' or ['GET', 'POST'])
     * @return void (exits on failure)
     */
    public static function requireMethod($methods) {
        $methods = (array) $methods;
        $currentMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($currentMethod, $methods)) {
            self::methodNotAllowed('Invalid request method. Expected: ' . implode(' or ', $methods));
        }
    }

    /**
     * Validate required POST/GET parameters
     *
     * @param array $requiredParams Array of required parameter names
     * @param string $source 'POST' or 'GET' (default: 'POST')
     * @return void (exits on failure)
     */
    public static function requireParams($requiredParams, $source = 'POST') {
        $data = $source === 'POST' ? $_POST : $_GET;
        $missing = [];

        foreach ($requiredParams as $param) {
            if (!isset($data[$param]) || trim($data[$param]) === '') {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            self::error('Missing required parameters: ' . implode(', ', $missing), 400);
        }
    }

    /**
     * Set JSON content-type header
     *
     * @return void
     */
    private static function setJsonHeader() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
    }
}
