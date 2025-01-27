<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

use Hashids\Hashids;

class CheckPermissions implements FilterInterface
{

    protected $userModel;
    protected $userSocialModel;
    protected $subscriptionModel;

    public function __construct()
    {
        $this->userModel = new \App\Models\UserModel();
        $this->userSocialModel = new \App\Models\UserSocialModel();
        $this->subscriptionModel = new \App\Models\SubscriptionModel();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // ตรวจสอบค่าที่ส่งเข้ามาใน $arguments
        $menu = $arguments[0] ?? null;

        // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบหรือไม่
        $userID = $this->decryptUserID(session()->get('userID'));
        $user = $this->userModel->getUserByID($userID);

        // ดึงสิทธิ์ (permission_ids) ของผู้ใช้งาน
        $userPermissions = explode(',', session()->get('permissions')); // สมมติว่า permission_ids เก็บเป็น "1,2,3"

        // ดึง id ของเมนูที่ต้องการตรวจสอบ
        $menuId = $this->getMenuId($menu);

        // ตรวจสอบว่าผู้ใช้งานมีสิทธิ์ในเมนูนี้หรือไม่
        if (!in_array($menuId, $userPermissions)) {
            // ถ้าไม่มีสิทธิ์ ให้ Redirect หรือส่งข้อความแจ้งเตือน
            return redirect()->to('/');
        }

        return null; // อนุญาตให้ผ่าน
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }

    /**
     * แกะรหัส User ID จาก Hashids
     */
    protected function decryptUserID($input)
    {
        $hashids = new Hashids(getenv('CLIENT_SECRET_KEY'));
        return $hashids->decode("$input")[0] ?? null;
    }

    private function getMenuId($menu)
    {
        $permissionModel = new \App\Models\PermissionModel;
        $permission = $permissionModel->getPermissionByMenu($menu);

        return $permission ? $permission->id : null;
    }
}
