<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

use Hashids\Hashids;

class UserCheckPackagePermission implements FilterInterface
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
        $permissionType = $arguments[0] ?? null;

        // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบหรือไม่
        $userID = $this->decryptUserID(session()->get('userID'));
        $user = $this->userModel->getUserByID($userID);

        $userSubscription = $this->subscriptionModel->getUserSubscription($userID);

        // ยูสทั่วไป
        if (!$userSubscription) {

            $userSocials = $this->userSocialModel->getUserSocialByUserID($userID);

            switch ($permissionType) {
                case 'connect':
                    if (count($userSocials) > $user->free_socials_limit) {
                        return service('response')
                            ->setStatusCode(401)
                            ->setJSON([
                                'status' => 'error',
                                'message' => 'Connect คุณถึงลิมิตแล้ว หากต้องการใช้สิทธิเพิ่มเติม กรุณาอัพเกรดแพ็คเกจ',
                            ]);
                    }
                    break;
                default:
                    return null;
                    break;
            }
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
}
