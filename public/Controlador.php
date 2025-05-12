<?php

class Controlador
{
    protected $db;

    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
    }

    public function registerUser(Request $request)
    {
        try {
            $email = $request->input('email');
            if (empty($email)) {
                return response()->json(['error' => 'The email is mandatory'], 400);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'The email must be a valid email address'], 400);
            }

            $apiKey = bin2hex(random_bytes(16));
            $stmt = $this->db->prepare(
                "INSERT INTO users_tokens (email, api_key) VALUES (?, ?)
                 ON CONFLICT (email) DO UPDATE SET api_key = EXCLUDED.api_key"
            );
            $stmt->execute([$email, $apiKey]);

            return response()->json(['api_key' => $apiKey], 200);
        } catch (\PDOException $e) {
            error_log("Database error in registerUser: " . $e->getMessage());
            return response()->json(['error' => 'Internal server error.'], 500);
        }
    }

    public function generateToken(Request $request)
    {
        $email  = $request->input('email');
        $apiKey = $request->input('api_key');

        if (empty($email)) {
            return response()->json(['error' => 'The email is mandatory'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'The email must be a valid email address'], 400);
        }
        if (empty($apiKey)) {
            return response()->json(['error' => 'The api_key is mandatory'], 400);
        }

        $stmt = $this->db->prepare("SELECT * FROM users_tokens WHERE email = ? AND api_key = ?");
        $stmt->execute([$email, $apiKey]);
        $user = $stmt->fetch();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized. API access token is invalid.'], 401);
        }

        $token = bin2hex(random_bytes(32));
        $stmt  = $this->db->prepare(
            "UPDATE users_tokens SET token = ?, token_expires_at = NOW() + INTERVAL '3 days' WHERE email = ?"
        );
        $stmt->execute([$token, $email]);

        return response()->json(['token' => $token], 200);
    }
}
