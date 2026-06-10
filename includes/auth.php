<?php
/**
 * Admin authentication functions.
 * Supports Bearer token, X-Admin-Token header, and session-based auth.
 */

function getAdminToken(): ?string {
    // Check Authorization header (Bearer token)
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)$/i', $auth, $matches)) {
        return $matches[1];
    }
    // Check X-Admin-Token header
    $xToken = $_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '';
    if ($xToken) {
        return $xToken;
    }
    return null;
}

function authenticateAdmin(): bool {
    $token = getAdminToken();
    if ($token && hash_equals(ADMIN_SECRET_KEY, $token)) {
        return true;
    }
    return false;
}

function requireAdmin(): void {
    if (!authenticateAdmin()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized access. Invalid or missing admin token.']);
        exit;
    }
}

function sanitizeHtml(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function escapeJs(string $str): string {
    return json_encode($str, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT);
}

function getJsonInput(): array {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}
