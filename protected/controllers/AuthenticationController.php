<?php

/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2025 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.org <info@magnusbilling.org>
 *
 */



namespace app\controllers;

use Yii;
use app\components\CController;
use app\components\Mail;
use app\components\Util;
use app\components\MagnusLog;
use app\components\AccessManager;
use app\models\User;
use app\models\LogUsers;
use app\models\GroupModule;
use app\models\GroupUserGroup;
use app\models\GroupUser;
use app\models\Configuration;
use PHPGangsta_GoogleAuthenticator;
use Exception;

class AuthenticationController extends CController
{
    private $menu = [];

    public function actionLogin()
    {
        $user     = $_REQUEST['user'];
        $password = $_REQUEST['password'];

        $this->verifyLogin();

        if (isset($_REQUEST['remote'])) {
            $modelSip = AccessManager::checkAccessLogin($user, $password);
            if (isset($modelSip->id) && is_object($modelSip)) {
                $user     = $modelSip->idUser->username;
                $password = $modelSip->idUser->password;
            }
        }

        $modelUser = User::find()->where(['username' => $user])->one();

        if (isset($modelUser->idGroup->idUserType->id) && $modelUser->idGroup->idUserType->id == 1) {

            $condition = "username COLLATE utf8_bin = :user AND UPPER(password) COLLATE utf8_bin = :pass ";
        } else {
            $condition = "((username COLLATE utf8_bin = :user OR email LIKE :user) AND (password COLLATE utf8_bin = :pass OR UPPER(SHA1(password)) COLLATE utf8_bin = :pass))  ";
            if ($this->config['global']['sipuser_login'] == 1) {
                $condition .= " OR (id = (SELECT id_user FROM pkg_sip WHERE name COLLATE utf8_bin = :user AND UPPER(SHA1(secret)) COLLATE utf8_bin = :pass) )";
            }
        }



        $loginkey = isset($_POST['loginkey']) ? $_POST['loginkey'] : false;

        if (strlen($loginkey) > 5 && $loginkey == $modelUser->loginkey) {
            $modelUser->active   = 1;
            $modelUser->loginkey = '';
            $modelUser->save();

            $mail = new Mail(Mail::$TYPE_SIGNUPCONFIRM, $modelUser->id);
            $mail->send();
        }

        if (! isset($modelUser->id)) {
            Yii::$app->session->set('logged', false);
            echo json_encode([
                'success' => false,
                'msg'     => 'Username and password combination is invalid',
            ]);
            $nameMsg = $this->nameMsg;
            $info    = 'Username and password combination is invalid - User: ' . $user . ' IP: ' . $_SERVER['REMOTE_ADDR'];
            Yii::error($info);

            MagnusLog::insertLOG(1, $info);

            return;
        }

        // $this->checkCaptcha();

        if ($modelUser->active == 0) {
            Yii::$app->session->set('logged', false);
            echo json_encode([
                'success' => false,
                'msg'     => 'Username is disabled',
            ]);

            $info = 'Username ' . $user . ' is disabled';
            MagnusLog::insertLOG(1, $info);

            return;
        }


        $idUserType = $modelUser->idGroup->idUserType->id;

        Yii::$app->session->set('isAdmin', $idUserType == 1 ? true : false);
        Yii::$app->session->set('isAgent', $idUserType == 2 ? true : false);
        Yii::$app->session->set('isClient', $idUserType == 3 ? true : false);
        Yii::$app->session->set('isClientAgent', isset($modelUser->id_user) && $modelUser->id_user > 1 ? true : false);
        Yii::$app->session->set('id_plan', $modelUser->id_plan);
        Yii::$app->session->set('credit', isset($modelUser->credit) ? $modelUser->credit : 0);
        Yii::$app->session->set('username', $modelUser->username);
        Yii::$app->session->set('logged', true);
        Yii::$app->session->set('id_user', $modelUser->id);
        Yii::$app->session->set('id_agent', is_null($modelUser->id_user) ? 1 : $modelUser->id_user);
        Yii::$app->session->set('name_user', $modelUser->firstname . ' ' . $modelUser->lastname);
        Yii::$app->session->set('id_group', $modelUser->id_group);
        Yii::$app->session->set('user_type', $idUserType);
        Yii::$app->session->set('systemName', $_SERVER['SCRIPT_FILENAME']);
        Yii::$app->session->set('session_start', time());
        Yii::$app->session->set('userCount', User::find()->where(['!=', 'credit', 0])->count());
        Yii::$app->session->set('hidden_prices', $modelUser->idGroup->hidden_prices);
        Yii::$app->session->set('hidden_batch_update', $modelUser->idGroup->hidden_batch_update);

        if ($modelUser->googleAuthenticator_enable > 0) {

            require_once 'lib/GoogleAuthenticator/GoogleAuthenticator.php';
            $ga = new PHPGangsta_GoogleAuthenticator();

            if (strlen($modelUser->google_authenticator_key) < 5 || $modelUser->googleAuthenticator_enable == 3) {

                if ($modelUser->googleAuthenticator_enable == 3) {
                    $secret = $modelUser->google_authenticator_key;
                } else {
                    $secret = $ga->createSecret();
                }

                $modelUser->google_authenticator_key   = $secret;
                $modelUser->googleAuthenticator_enable = 3;
                $modelUser->save();
                Yii::$app->session->set('newGoogleAuthenticator', true);
                Yii::$app->session->set('googleAuthenticatorKey', $ga->getQRCodeGoogleUrl('VoIP-' . $modelUser->username . '-' . $modelUser->id, $secret));
                Yii::$app->session->get('checkGoogleAuthenticator', true);
                Yii::$app->session->set('showGoogleCode', true);
            } else {
                $secret                                       = $modelUser->google_authenticator_key;
                Yii::$app->session->set('newGoogleAuthenticator', false);
                if ($modelUser->googleAuthenticator_enable == 2) {
                    Yii::$app->session->set('showGoogleCode', true);
                } else {
                    Yii::$app->session->set('showGoogleCode', false);
                }
                Yii::$app->session->set('googleAuthenticatorKey', $ga->getQRCodeGoogleUrl('VoIP-' . $modelUser->username . '-' . $modelUser->id, $secret));

                $modelLogUsers = LogUsers::find(
                    'id_user = :key AND ip = :key1 AND description = :key2 AND date > :key3',
                    [
                        ':key'  => $modelUser->id,
                        ':key1' => $_SERVER['REMOTE_ADDR'],
                        ':key2' => 'Username Login on the panel - User ' . $modelUser->username,
                        ':key3' => date('Y-m-d'),
                    ]
                )->count();
                if ($modelLogUsers > 0) {
                    Yii::$app->session->set('checkGoogleAuthenticator', false);
                } else {
                    Yii::$app->session->set('checkGoogleAuthenticator', true);
                }
            }
        } else {
            Yii::$app->session->set('showGoogleCode', false);
            Yii::$app->session->set('newGoogleAuthenticator', false);
            Yii::$app->session->set('checkGoogleAuthenticator', false);
            MagnusLog::insertLOG(1, 'Username Login on the panel - User ' . Yii::$app->session->get('username'));
        }

        if (isset($_REQUEST['securityLogin'])) {
            header("Location: ../../../");
        }

        if (isset($_REQUEST['remote'])) {
            header("Location: ../..");
        }
        echo json_encode([
            'success' => Yii::$app->session->get('username'),
            'msg'     => Yii::$app->session->get('name_user'),
        ]);
        exit;
    }

    private function mountMenu()
    {
        $groupModule = new GroupModule();
        $modelGroupModule = $groupModule->getGroupModule(Yii::$app->session->get('id_group'), Yii::$app->session->get('isClient'), Yii::$app->session->get('id_user'));

        Yii::$app->session->set('action', $this->getActions($modelGroupModule));
        Yii::$app->session->set('menu', $this->getMenu($modelGroupModule));
    }

    public function actionLogoff()
    {
        Yii::$app->session->set('logged', false);
        Yii::$app->session->set('id_user', false);
        Yii::$app->session->set('id_agent', false);
        Yii::$app->session->set('name_user', false);
        Yii::$app->session->set('menu', false);
        Yii::$app->session->set('action', false);
        Yii::$app->session->set('currency', false);
        Yii::$app->session->set('language', false);
        Yii::$app->session->set('isAdmin', false);
        Yii::$app->session->set('isClient', false);
        Yii::$app->session->set('isAgent', false);
        Yii::$app->session->set('isClientAgent', false);
        Yii::$app->session->set('id_plan', false);
        Yii::$app->session->set('credit', false);
        Yii::$app->session->set('username', false);
        Yii::$app->session->set('id_group', false);
        Yii::$app->session->set('user_type', false);
        Yii::$app->session->set('decimal', false);
        Yii::$app->session->set('licence', false);
        Yii::$app->session->set('email', false);
        Yii::$app->session->set('userCount', false);
        Yii::$app->session->set('systemName', false);
        Yii::$app->session->set('base_country', false);
        Yii::$app->session->set('version', false);
        Yii::$app->session->set('hidden_prices', false);
        Yii::$app->session->set('hidden_batch_update', false);
        Yii::$app->session->removeAll();
        Yii::$app->session->destroy();

        echo json_encode([
            'success' => true,
        ]);
    }

    public function actionCheck()
    {
        if (Yii::$app->session->get('logged')) {

            $this->mountMenu();
            $modelGroupUserGroup = GroupUserGroup::find()
                ->where(['id_group_user' => Yii::$app->session->get('id_group')])
                ->count();

            $modelGroupUser = GroupUser::findOne(Yii::$app->session->get('id_group'));

            Yii::$app->session->set('adminLimitUsers', $modelGroupUserGroup);
            Yii::$app->session->set('licence', $this->config['global']['licence']);
            Yii::$app->session->set('email', $this->config['global']['admin_email']);
            Yii::$app->session->set('currency', $this->config['global']['base_currency']);
            Yii::$app->session->set('language', $this->config['global']['base_language']);
            Yii::$app->session->set('decimal', $this->config['global']['decimal_precision']);
            Yii::$app->session->set('base_country', $this->config['global']['base_country']);
            Yii::$app->session->set('version', $this->config['global']['version']);
            Yii::$app->session->set('asterisk_version', $this->config['global']['asterisk_version']);
            Yii::$app->session->set('social_media_network', $this->config['global']['social_media_network']);
            Yii::$app->session->set('show_playicon_cdr', $this->config['global']['show_playicon_cdr']);

            $id_user                  = Yii::$app->session->get('id_user');
            $id_agent                 = Yii::$app->session->get('id_agent');
            $nameUser                 = Yii::$app->session->get('name_user');
            $logged                   = Yii::$app->session->get('logged');
            $menu                     = Yii::$app->session->get('menu');
            $currency                 = Yii::$app->session->get('currency');
            $language                 = Yii::$app->session->get('language');
            $isAdmin                  = Yii::$app->session->get('isAdmin');
            $isClient                 = Yii::$app->session->get('isClient');
            $isAgent                  = Yii::$app->session->get('isAgent');
            $isClientAgent            = Yii::$app->session->get('isClientAgent');
            $id_plan                  = Yii::$app->session->get('id_plan');
            $credit                   = Yii::$app->session->get('credit');
            $username                 = Yii::$app->session->get('username');
            $id_group                 = Yii::$app->session->get('id_group');
            $user_type                = Yii::$app->session->get('user_type');
            $decimal                  = Yii::$app->session->get('decimal');
            $licence                  = Yii::$app->session->get('licence');
            $email                    = Yii::$app->session->get('email');
            $userCount                = Yii::$app->session->get('userCount');
            $base_country             = Yii::$app->session->get('base_country');
            $version                  = Yii::$app->session->get('version');
            $show_playicon_cdr        = Yii::$app->session->get('show_playicon_cdr');
            $social_media_network     = Yii::$app->session->get('social_media_network');
            $checkGoogleAuthenticator = Yii::$app->session->get('checkGoogleAuthenticator');
            $googleAuthenticatorKey   = Yii::$app->session->get('googleAuthenticatorKey');
            $newGoogleAuthenticator   = Yii::$app->session->get('newGoogleAuthenticator');
            $showGoogleCode           = Yii::$app->session->get('showGoogleCode');
            $hidden_prices            = Yii::$app->session->set('hidden_prices', $modelGroupUser->hidden_prices);
            $hidden_batch_update      = Yii::$app->session->get('hidden_batch_update', $modelGroupUser->hidden_batch_update);
        } else {
            $id_user                  = false;
            $id_agent                 = false;
            $nameUser                 = false;
            $logged                   = false;
            $menu                     = [];
            $currency                 = false;
            $language                 = false;
            $isAdmin                  = false;
            $isClient                 = false;
            $isAgent                  = false;
            $isClientAgent            = false;
            $id_plan                  = false;
            $credit                   = false;
            $username                 = false;
            $id_group                 = false;
            $user_type                = false;
            $decimal                  = false;
            $licence                  = false;
            $email                    = false;
            $userCount                = false;
            $base_country             = false;
            $version                  = false;
            $checkGoogleAuthenticator = false;
            $googleAuthenticatorKey   = false;
            $newGoogleAuthenticator   = false;
            $showGoogleCode           = false;
            $social_media_network     = false;
            $show_playicon_cdr        = false;
            $hidden_prices            = false;
            $hidden_batch_update      = false;
        }
        $language = !is_null(Yii::$app->session->get('language')) ? Yii::$app->session->get('language') : 'en';
        $theme    = !is_null(Yii::$app->session->get('theme')) ? Yii::$app->session->get('theme') : 'blue-neptune';

        if (file_exists('resources/images/logo_custom.png')) {
            Yii::info('file existe', 'info');
        }

        if (Yii::$app->session->get('isClientAgent')) {

            if (file_exists('resources/images/logo_custom_' . Yii::$app->session->get('id_agent') . '.png')) {
                $logo = 'resources/images/logo_custom_' . Yii::$app->session->get('id_agent') . '.png';
            } else {
                if (file_exists('resources/images/logo_custom.png')) {
                    $logo = 'resources/images/logo_custom.png';
                } else {
                    $logo = 'resources/images/logo.png';
                }
            }
        } else if (Yii::$app->session->get('isAgent')) {

            if (file_exists('resources/images/logo_custom_' . Yii::$app->session->get('id_user') . '.png')) {
                $logo = 'resources/images/logo_custom_' . Yii::$app->session->get('id_user') . '.png';
            } else {
                if (file_exists('resources/images/logo_custom.png')) {
                    $logo = 'resources/images/logo_custom.png';
                } else {
                    $logo = 'resources/images/logo.png';
                }
            }
        } else {
            $logo = file_exists('resources/images/logo_custom.png') ? 'resources/images/logo_custom.png' : 'resources/images/logo.png';
        }

        echo json_encode([
            'id'                       => $id_user,
            'id_agent'                 => $id_agent,
            'name'                     => $nameUser,
            'success'                  => $logged,
            'menu'                     => $menu,
            'language'                 => $language,
            'theme'                    => $theme,
            'currency'                 => $currency,
            'language'                 => $language,
            'isAdmin'                  => $isAdmin,
            'isClient'                 => $isClient,
            'isAgent'                  => $isAgent,
            'isClientAgent'            => $isClientAgent,
            'id_plan'                  => $id_plan,
            'credit'                   => $credit,
            'username'                 => $username,
            'id_group'                 => $id_group,
            'user_type'                => $user_type,
            'decimal'                  => $decimal,
            'licence'                  => $licence,
            'email'                    => $email,
            'userCount'                => $userCount,
            'base_country'             => $base_country,
            'version'                  => $version,
            'show_playicon_cdr'        => $show_playicon_cdr,
            'social_media_network'     => $social_media_network,
            'asterisk_version'         => Yii::$app->session->get('asterisk_version'),
            'checkGoogleAuthenticator' => $checkGoogleAuthenticator,
            'googleAuthenticatorKey'   => $googleAuthenticatorKey,
            'newGoogleAuthenticator'   => $newGoogleAuthenticator,
            'showGoogleCode'           => $showGoogleCode,
            'logo'                     => $logo,
            'show_filed_help'          => $this->config['global']['show_filed_help'],
            'campaign_user_limit'      => $this->config['global']['campaign_user_limit'],
            'showMCDashBoard'          => $this->config['global']['showMCDashBoard'],
            'hidden_prices'            => $hidden_prices,
            'hidden_batch_update'      => $hidden_batch_update,
        ]);
        exit;
    }

    public function actionGoogleAuthenticator()
    {
        require_once 'lib/GoogleAuthenticator/GoogleAuthenticator.php';

        $ga = new PHPGangsta_GoogleAuthenticator();

        $modelUser = User::findOne((int) Yii::$app->session->get('id_user'));

        //Yii::info(print_r($sql,true),'info');
        $secret      = $modelUser->google_authenticator_key;
        $oneCodePost = $_POST['oneCode'];

        $checkResult = $ga->verifyCode($secret, $oneCodePost, 2);

        if ($checkResult) {
            $sussess                                        = true;
            Yii::$app->session->set('checkGoogleAuthenticator', false);
            $modelUser->googleAuthenticator_enable          = 1;
            $modelUser->save();
            MagnusLog::insertLOG(1, 'Username Login on the panel - User ' . Yii::$app->session->get('username'));
        } else {
            $sussess = false;
        }
        //$sussess = true;
        echo json_encode([
            'success' => $sussess,
            'msg'     => Yii::$app->session->get('name_user'),
        ]);
    }

    public function actionChangePassword()
    {
        $passwordChanged = false;
        $id_user         = Yii::$app->session->get('id_user');
        $currentPassword = $_POST['current_password'];
        $newPassword     = $_POST['password'];
        $isClient        = Yii::$app->session->get('isClient');
        $errors          = '';

        $modelUser = User::find()
            ->where(['id' => $id_user, 'password' => $currentPassword])
            ->one();

        if (isset($modelUser->id)) {
            try {
                $modelUser->password = $newPassword;
                $passwordChanged     = $modelUser->save();
            } catch (Exception $e) {
                $errors = $this->getErrorMySql($e);
            }

            $msg = $passwordChanged ? yii::t('yii', 'Password change success!') : $errors;
        } else {
            $msg = yii::t('yii', 'Current Password incorrect.');
        }

        echo json_encode([
            'success' => $passwordChanged,
            'msg'     => $msg,
        ]);
    }

    public function actionImportLogo()
    {
        if (isset($_FILES['logo']['tmp_name']) && strlen($_FILES['logo']['tmp_name']) > 3) {

            $uploaddir = "resources/images/";

            if (Yii::$app->session->get('isAgent')) {
                $uploadfile = $uploaddir . 'logo_custom_' . Yii::$app->session->get('id_user') . '.png';
            } else {
                $uploadfile = $uploaddir . 'logo_custom.png';
            }
            $typefile = Util::valid_extension($_FILES["logo"]["name"], ['png']);

            move_uploaded_file($_FILES["logo"]["tmp_name"], $uploadfile);
        }

        echo json_encode([
            'success' => true,
            'msg'     => 'Refresh the system to see the new logo',
        ]);
    }

    public function actionImportWallpapers()
    {
        if (isset($_FILES['wallpaper']['tmp_name']) && strlen($_FILES['wallpaper']['tmp_name']) > 3) {

            $uploaddir = "resources/images/wallpapers/";
            $typefile  = Util::valid_extension($_FILES["wallpaper"]["name"], ['jpg']);

            $uploadfile = $uploaddir . 'Customization.jpg';
            move_uploaded_file($_FILES["wallpaper"]["tmp_name"], $uploadfile);
        }

        $modelConfiguration = Configuration::find()->where(['like', 'config_key', 'wallpaper'])->one();
        $modelConfiguration->config_value = 'Customization';
        try {
            $success = $modelConfiguration->save();
            $msg     = Yii::t('yii', 'Refresh the system to see the new logo');
        } catch (Exception $e) {
            $success = false;
            $msg     = $this->getErrorMySql($e);
        }
        echo json_encode([
            'success' => $success,
            'msg'     => $msg,
        ]);
    }

    public function actionImportLoginBackground()
    {
        $success = false;
        $msg     = 'error';

        if (isset($_FILES['loginbackground']['tmp_name']) && strlen($_FILES['loginbackground']['tmp_name']) > 3) {

            $typefile = Util::valid_extension($_FILES["loginbackground"]["name"], ['jpg']);

            $uploadfile = 'resources/images/lock-screen-background.jpg';
            try {
                move_uploaded_file($_FILES["loginbackground"]["tmp_name"], $uploadfile);
                $success = true;
                $msg     = 'Refresh the system to see the new wallpaper';
            } catch (Exception $e) {
            }
        }

        $colors = ['black', 'blue', 'gray', 'orange', 'purple', 'red', 'yellow', 'green'];
        foreach ($colors as $key => $color) {
            $types = ['crisp', 'neptune', 'triton'];
            foreach ($types as $key => $type) {
                copy("/var/www/html/mbilling/resources/images/lock-screen-background.jpg", "/var/www/html/mbilling/$color-$type/resources/images/lock-screen-background.jpg");
            }
        }

        echo json_encode([
            'success' => $success,
            'msg'     => $msg,
        ]);
    }

    public function actionForgetPassword()
    {
        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

            $modelUser = User::find()->where(['email' => $_POST['email']])->all();
            if (is_array($modelUser) && count($modelUser) > 1) {
                $success = false;
                $msg     = "Email in use more than 1 account, contact administrator";
            } else if (is_array($modelUser) && count($modelUser)) {

                if ($modelUser[0]->idGroup->idUserType->id == 1) {

                    $success = false;
                    $msg     = "You can't request admin password";
                } else {

                    $mail = new Mail(Mail::$TYPE_FORGETPASSWORD, $modelUser[0]->id);
                    try {
                        $mail->send();
                    } catch (Exception $e) {
                        //
                    }
                    $success = true;
                    $msg     = "Your password was sent to your email";
                }
            } else {
                $success = false;
                $msg     = "Email not found";
            }
        } else {
            $success = false;
            $msg     = "Email not found";
        }

        echo json_encode([
            'success' => $success,
            'msg'     => $msg,
        ]);
    }

    public function actionCancelCreditNotification()
    {
        if (isset($_GET['key']) && isset($_GET['id'])) {


            $modelUser = User::findOne((int) $_GET['id']);

            if (isset($modelUser->id)) {

                $key = sha1($modelUser->id . $modelUser->username . $modelUser->password);
                if ($key == $_GET['key']) {
                    $modelUser->credit_notification = '-1';
                    $modelUser->save();
                    echo '<br><center><font color=green>' . Yii::t('app', "Success") . '</font></center>';
                }
            }
        }
    }

    public function verifyLogin()
    {
        $modelLogUsers = LogUsers::find()
            ->where(['ip' => $_SERVER['REMOTE_ADDR']])
            ->andWhere(['>', 'date', new \yii\db\Expression('DATE_SUB(NOW(), INTERVAL 5 MINUTE)')])
            ->orderBy(['id' => SORT_DESC])
            ->limit(3)
            ->all();


        if (is_array($modelLogUsers) && count($modelLogUsers) < 3) {
            return;
        }

        if (preg_match('/IP blocked after 3 failing attempts. IP: /', $modelLogUsers[0]->description)) {
            Yii::$app->session->get('logged', false);
            echo json_encode([
                'success' => false,
                'msg'     => "IP blocked after 3 failing attempts. Wait 5 minutes and try again.",
                'ip'      => $_SERVER['REMOTE_ADDR'],
            ]);
            exit;
        }

        $invalid = 0;
        for ($i = 0; $i < 3; $i++) {
            if (preg_match('/Username and password combination is invalid/', $modelLogUsers[$i]->description)) {
                $invalid++;
            }
        }

        if ($invalid >= 3) {
            Yii::$app->session->get('logged', false);
            echo json_encode([
                'success' => false,
                'msg'     => Yii::t('app', "IP blocked after 3 failing attempts.") . "<br><b>" . Yii::t('app', "Wait 5 minutes and try again.") . "</b>" . "<br> IP: " . $_SERVER['REMOTE_ADDR'],
            ]);
            $nameMsg = $this->nameMsg;

            $info = 'IP blocked after 3 failing attempts. IP: ' . $_SERVER['REMOTE_ADDR'];
            Yii::info($info, 'error');
            MagnusLog::insertLOG(1, $info);

            exit;
        }
    }

    public function checkCaptcha()
    {
        if (strlen($this->config['global']['reCaptchaSecret']) > 10 && strlen($this->config['global']['reCaptchaKey']) > 10) {
            $post_data = http_build_query(
                [
                    'secret'   => $this->config['global']['reCaptchaSecret'],
                    'response' => $_POST['key'],
                ]
            );

            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $post_data,
                ],
            ];

            $context  = stream_context_create($opts);
            $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            try {
                $response = json_decode($response);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'msg'     => 'Invalid captcha json' . print_r($response, true),
                ]);
                exit;
            }
            if ($response->success != true || $response->hostname != $_SERVER['HTTP_HOST']) {
                echo json_encode([
                    'success' => false,
                    'msg'     => 'Invalid captcha. Refresh the page to generate new code',
                ]);
                exit;
            }
        }
    }
}
