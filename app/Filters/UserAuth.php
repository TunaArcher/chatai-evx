<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class UserAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Fix Bug แก้ไขปัญหากรณีถูกลบไอดีแล้ว ให้ออกจากระบบทันที
        $UserModel = new \App\Models\UserModel();
        $User = $UserModel->getUserByID(session()->get('userID'));

        if (!$User) {
            session()->setFlashdata(['session_expired' => 'เซ็นซันหมดอายุ กรุณาล็อคอินอีกครั้ง']);
            return redirect()->to('/login');
        }

        if (!session()->get('isUserLoggedIn')) return redirect()->to('/login');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
