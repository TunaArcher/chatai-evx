<?php

namespace App\Controllers;

use App\Models\TeamModel;
use App\Models\TeamMemberModel;
use App\Models\TeamSocialModel;
use App\Models\UserModel;
use App\Models\UserSocialModel;
use CodeIgniter\HTTP\ResponseInterface;

class TeamController extends BaseController
{
    private TeamModel $teamModel;
    private TeamMemberModel $teamMemberModel;
    private TeamSocialModel $teamSocialModel;
    private UserModel $userModel;
    private UserSocialModel $userSocialModel;

    public function __construct()
    {
        $this->teamModel = new TeamModel();
        $this->teamMemberModel = new TeamMemberModel();
        $this->teamSocialModel = new TeamSocialModel();
        $this->userModel = new UserModel();
        $this->userSocialModel = new UserSocialModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $userID = hashidsDecrypt(session()->get('userID'));

        $data['content'] = 'team/index';
        $data['title'] = 'Team';
        $data['css_critical'] = '<link href="assets/libs/mobius1-selectr/selectr.min.css" rel="stylesheet" type="text/css" />';
        $data['js_critical'] = ' 
            <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="https://unpkg.com/mobius1-selectr@latest/dist/selectr.min.js" type="text/javascript"></script>
            <script src="app/team.js"></script>
        ';

        if (session()->get('user_owner_id') == '') {

            $data['userSocials'] = $this->userSocialModel->getUserSocialByUserID($userID);
            $members = $this->userModel->getUserByUserOwnerID($userID);
            foreach ($members as $member) {
                $member->status = '';
                if ($member->accept_invite == 'waiting') $member->status = '(รอการตอบรับ)';
            }
            $data['members'] = $members;

            $teams = $this->teamModel->getTeamByOwnerID($userID);
            foreach ($teams as $team) {
                $team->members = $this->teamMemberModel->getTeamMemberByTeamID($team->id);
                $team->socials =  $this->teamSocialModel->getTeamSocialByTeamID($team->id);
            }

            $data['teams'] = $teams;
        } else {

            $teams = [];

            $teamMembers = $this->teamMemberModel->getTeamMemberByUserID($userID);

            foreach ($teamMembers as $teamMember) {
                // px($teamMember);
                $team = $this->teamModel->getTeamByID($teamMember->team_id);

                $teams[] = $team;
            }

            foreach ($teams as $team) {
                $team->members = $this->teamMemberModel->getTeamMemberByTeamID($team->id);
                $team->socials =  $this->teamSocialModel->getTeamSocialByTeamID($team->id);
            }

            $data['teams'] = $teams;
        }

        echo view('/app', $data);
    }

    public function inviteToTeamMember()
    {

        try {
            $response = [
                'success' => 0,
                'message' => '',
            ];
            $status = 500;

            // รับข้อมูล JSON จาก Request
            $data = $this->request->getJSON();
            $user = $this->userModel->getUserByEmail($data->email);

            if ($user) {
                switch ($user->accept_invite) {
                    case 'waiting':
                        throw new \Exception('คุณเชิญผู้ใช้นี้ไปแล้ว');
                        break;
                    case 'done':
                        throw new \Exception('มียูสนี้แล้ว');
                        break;
                    default:
                        throw new \Exception('มียูสนี้แล้ว');
                        break;
                }
            }

            // สร้างลิงก์สมัครสมาชิก
            $registerLink = base_url('inviteToTeamMember' . '/' . session()->get('userID') . '?email=' . urlencode($data->email));

            // ส่งอีเมล
            $email = \Config\Services::email();
            // $email->setTo($data->email);
            $email->setFrom(getenv('MAIL_USER'), 'AutoConX Team'); // อีเมลและชื่อผู้ส่ง
            $email->setTo($data->email);
            $email->setSubject('คำเชิญเข้าทีม | AutoConX');
            $email->setMessage("
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 8px; text-align: center;'>
                    <div style='margin-bottom: 20px;'>
                        <img src='" . base_url('/assets/images/conXx.png') . "' alt='AutoConX Logo' style='max-width: 150px; height: auto;'>
                    </div>
                    <h1 style='color: #2c3e50;'>คุณถูกเชิญเข้าร่วมทีม</h1>
                    <p style='font-size: 16px; margin: 10px 0;'>
                        นี่คือคำเชิญเข้าร่วมทีม! กรุณาคลิกลิงก์ด้านล่างเพื่อดำเนินการลงทะเบียนของคุณให้เสร็จสมบูรณ์:
                    </p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='{$registerLink}' 
                        style='display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #3498db; text-decoration: none; border-radius: 5px;'>
                            ยืนยันร่วมทีม
                        </a>
                    </div>
                    <p style='font-size: 14px; color: #7f8c8d;'>
                        หากลิงก์ไม่ทำงาน คุณสามารถคัดลอกและวางลิงก์นี้ในเบราว์เซอร์ของคุณ: <br>
                        <a href='{$registerLink}' style='color: #3498db;'>{$registerLink}</a>
                    </p>
                </div>
            ");

            if (!$email->send()) {
                // แสดงข้อผิดพลาด
                print_r($email->printDebugger(['headers', 'subject', 'body']));
            } else {
                $this->userModel->insertUser([
                    'accept_invite' => 'waiting',
                    'email' => $data->email,
                    'user_owner_id' => hashidsDecrypt(session()->get('userID')),
                    'name' => $data->email,
                    'picture' => getAvatar()
                ]);
                $response = [
                    'success' => 1,
                    'message' => 'invite success',
                ];
                $status = 200;
            }

            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function create()
    {

        try {

            $response = [
                'success' => 0,
                'message' => '',
            ];
            $status = 500;

            // รับข้อมูล JSON จาก Request
            $data = $this->request->getJSON();
            $teamLogo = $data->team_logo;
            $teamName = $data->team_name;
            $teamNote = $data->team_note;
            $connectIds = $data->connect_ids;
            $memberIds = $data->member_ids;

            // ตรวจสอบข้อมูลที่จำเป็น
            if (empty($teamName) || empty($connectIds) || empty($memberIds)) {
                return $this->response->setJSON(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            }

            $data = [
                'icon' => $teamLogo,
                'name' => $teamName,
                'note' => $teamNote,
                'owner_id' => hashidsDecrypt(session()->get('userID'))
            ];

            $teamID = $this->teamModel->insertTeam($data);

            if ($teamID) {

                foreach ($connectIds as $connectId) {
                    $this->teamSocialModel->insertTeamSocial([
                        'team_id' => $teamID,
                        'social_id' => $connectId
                    ]);
                }

                foreach ($memberIds as $memberId) {
                    $this->teamMemberModel->insertTeamMember([
                        'team_id' => $teamID,
                        'user_id' => $memberId,
                    ]);
                }

                $response = [
                    'success' => 1,
                    'message' => 'สร้างทีมสำเร็จ',
                ];

                $status = 200;
            }

            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getTeam($teamID)
    {

        try {

            $response = [
                'success' => 0,
                'message' => '',
            ];
            $status = 500;

            $team = $this->teamModel->getTeamByID(hashidsDecrypt($teamID));

            if (!$team) throw new \Exception('ไม่พบทีม');

            $team->members = $this->teamMemberModel->getTeamMemberByTeamID($team->id, 'ONLY_ID');
            $team->socials = $this->teamSocialModel->getTeamSocialByTeamID($team->id, 'ONLY_ID');

            $response = [
                'success' => 1,
                'message' => 'success',
                'data' => $team
            ];
            $status = 200;

            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function update()
    {

        try {

            $response = [
                'success' => 0,
                'message' => '',
            ];
            $status = 500;

            // รับข้อมูล JSON จาก Request
            $data = $this->request->getJSON();
            $teamID = $data->team_id;
            // $teamLogo = $data->team_logo;
            // $teamName = $data->team_name;
            $teamNote = $data->team_note;
            $connectIds = $data->connect_ids;
            $memberIds = $data->member_ids;

            // ตรวจสอบข้อมูลที่จำเป็น
            if (empty($connectIds) || empty($memberIds)) {
                return $this->response->setJSON(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            }

            $data = [
                'note' => $teamNote,
                // 'owner_id' => hashidsDecrypt(session()->get('userID'))
            ];

            if ($this->teamModel->updateTeamByID($teamID, $data)) {

                $this->teamModel->updateRelations($teamID, $connectIds, $memberIds);

                $response = [
                    'success' => 1,
                    'message' => 'อัพเดททีมสำเร็จ',
                ];

                $status = 200;
            }

            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroy()
    {

        try {

            $response = [
                'success' => 0,
                'message' => '',
            ];
            $status = 500;

            // รับข้อมูล JSON จาก Request
            $data = $this->request->getJSON();
            $teamID = hashidsDecrypt($data->teamID);

            $team = $this->teamModel->getTeamByID($teamID);

            if (!$team) throw new \Exception('ไม่พบทีม');

            $this->teamModel->deleteTeamByID($teamID);
            $this->teamSocialModel->deleteTeamSocialByTeamID($teamID);
            $this->teamMemberModel->deleteTeamMemberByTeamID($teamID);

            $response = [
                'success' => 1,
                'message' => 'success',
            ];
            $status = 200;

            return $this->response
                ->setStatusCode($status)
                ->setContentType('application/json')
                ->setJSON($response);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function viewInviteToTeamMember($userID)
    {
        return view('/team/invite', [
            'content' => 'team/invite', // ชื่อไฟล์ View
            'title' => 'Team', // ชื่อหน้า
            'js_critical' => '
                <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>    
                <script src="app/team.js"></script>
            ', // ไฟล์ JS
            'userOwnerID' => $userID,
            'email' => $this->request->getGet('email')
        ]);
    }
}
