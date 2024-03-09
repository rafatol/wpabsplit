<?php

namespace WpAbSplit;

class License
{

    const MOTHERSHIP = 'https://wpabsplit.com';
    const SECRET_KEY = '65e7dd75a536b2.72168865';

    const LICENSE_KEY = 'wpabsplit_license_key';
    const LICENSE_DATA = 'wpabsplit_license_data';

    /**
     * @return boolean
     */
    public static function isActivated()
    {
        $license_key = self::getLicenseKey();

        if($license_key){
            $localCheck = get_option(md5($license_key));

            if($localCheck && $localCheck == date('Y-m-d')){
                return true;
            }

            try {
                $remoteCheck = self::checkWithMotherShip($license_key);

                if($remoteCheck){
                    update_option(md5($license_key), date('Y-m-d'));
                    return true;
                }
            } catch(\LicenseException $e) {

            }
        }

        return false;
    }

    public static function activateLicense()
    {
        $licenseKey = self::getLicenseKey();

        if($licenseKey){
            $requestParams = [
                'slm_action' => 'slm_activate',
                'secret_key' => self::SECRET_KEY,
                'registered_domain' => $_SERVER['HTTP_HOST'],
                'license_key' => $licenseKey,
            ];

            $response = wp_remote_get(add_query_arg($requestParams, self::MOTHERSHIP), array('timeout' => 20, 'sslverify' => false));

            if(is_wp_error($response)){
                throw new \Exception('Error contacting license server');
            }

            $response = json_decode(wp_remote_retrieve_body($response));

            if($response->result == 'success'){
                update_option(md5($licenseKey), date('Y-m-d'));
                return true;
            }

            throw new \LicenseException($response->message);
        }

        return false;
    }

    public static function deactivateLicense()
    {
        $licenseKey = self::getLicenseKey();

        if($licenseKey){
            $requestParams = [
                'slm_action' => 'slm_deactivate',
                'secret_key' => self::SECRET_KEY,
                'registered_domain' => $_SERVER['HTTP_HOST'],
                'license_key' => $licenseKey,
            ];

            $response = wp_remote_get(add_query_arg($requestParams, self::MOTHERSHIP), array('timeout' => 20, 'sslverify' => false));

            if(is_wp_error($response)){
                throw new \Exception('Error contacting license server');
            }

            $response = json_decode(wp_remote_retrieve_body($response));

            if($response->result == 'success'){
                delete_option(md5($licenseKey));
                delete_option(self::LICENSE_KEY);

                return true;
            }

            throw new \LicenseException($response->message);
        }

        return false;
    }

    public static function getLicenseKey()
    {
        return get_option(self::LICENSE_KEY);
    }

    public static function setLicenseKey($licenseKey)
    {
        if(empty($licenseKey)){
            delete_option(self::LICENSE_KEY);
            return;
        }

        update_option(self::LICENSE_KEY, $licenseKey);
    }

    private static function checkWithMotherShip($licenseKey)
    {
        $requestParams = [
            'slm_action' => 'slm_check',
            'secret_key' => self::SECRET_KEY,
            'registered_domain' => $_SERVER['HTTP_HOST'],
            'license_key' => $licenseKey,
        ];

        $response = wp_remote_get(add_query_arg($requestParams, self::MOTHERSHIP), array('timeout' => 20, 'sslverify' => false));

        if(is_wp_error($response)){
            throw new \Exception('Error contacting license server');
        }

        $licenseData = json_decode(wp_remote_retrieve_body($response));

        if($response->result == 'success'){
            update_option(md5($licenseKey), date('Y-m-d'));
            update_option(self::LICENSE_DATA, $licenseData);

            return true;
        }

        delete_option(md5($licenseKey));
        delete_option(self::LICENSE_DATA);

        throw new \LicenseException($response->message);

        return false;
    }

    public static function updateLicenseKey($licenseKey)
    {
        $currentLicenseKey = self::getLicenseKey();

        /** Em caso de licença diferente, desativa a anterior e ativa a nova */
        if($currentLicenseKey != $licenseKey){
            if($currentLicenseKey){
                try {
                    self::deactivateLicense();
                } catch(\LicenseException $e) {

                }
            }

            self::setLicenseKey($licenseKey);

            if($licenseKey){
                return self::activateLicense();
            }
        }

        return false;
    }

}