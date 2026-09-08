<?php

namespace App\Controllers;

class Auth extends BaseController
{
    /**
     * Renders the login page.
     */
    public function login()
    {
        // If already logged in, redirect to admin dashboard
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        return view('auth/login');
    }

    /**
     * Handles the login form submission.
     */
    public function attemptLogin()
    {
        $db = \Config\Database::connect();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return redirect()->back()->with('error', 'Username dan password wajib diisi.')->withInput();
        }

        // Query user in sys_user
        $user = $db->table('sys_user')
            ->where('username', $username)
            ->get()
            ->getRowArray();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            session()->set([
                'user_id'   => $user['user_id'],
                'username'  => $user['username'],
                'role'      => $user['role'],
                'logged_in' => true,
            ]);

            return redirect()->to(base_url('admin/dashboard'))->with('success', 'Selamat datang kembali, ' . $user['username'] . '!');
        }

        return redirect()->back()->with('error', 'Username atau password salah.')->withInput();
    }

    /**
     * Log the user out and destroy the session.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Anda telah berhasil logout.');
    }
}
